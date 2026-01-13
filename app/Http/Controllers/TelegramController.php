<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SergiX44\Nutgram\Nutgram;
use Str;

class TelegramController extends Controller
{

    public function handle(Request $request, Nutgram $bot)
    {
        try {
            $bot->run();

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function createCompany($id, Request $request, Nutgram $bot)
    {
        Log::info("Creating company via Telegram WebApp for user ID: $id");

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => 'required|email|unique:companies,company_email',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|url|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_description' => 'nullable|string',
            'is_active' => 'boolean',
            'plan_status' => 'nullable|string|max:50',
            'plan_start_date' => 'nullable|date',
            'plan_end_date' => 'nullable|date|after_or_equal:plan_start_date',
            'company_currency' => 'nullable|string|max:10',
            'company_timezone' => 'nullable|string|max:50',
        ], [
            // Messages pour company_name
            'company_name.required' => 'Le nom de l\'entreprise est requis',
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères',
            'company_name.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères',
            'company_name.unique' => 'Ce nom d\'entreprise existe déjà',

            // Messages pour company_email
            'company_email.required' => 'L\'email de l\'entreprise est requis',
            'company_email.email' => 'L\'email doit être une adresse email valide',
            'company_email.unique' => 'Cet email est déjà utilisé',

            // Messages pour company_phone
            'company_phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'company_phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',

            // Messages pour company_website
            'company_website.url' => 'Le site web doit être une URL valide',
            'company_website.max' => 'Le site web ne peut pas dépasser 255 caractères',

            // Messages pour company_address
            'company_address.string' => 'L\'adresse doit être une chaîne de caractères',
            'company_address.max' => 'L\'adresse ne peut pas dépasser 500 caractères',

            // Messages pour company_description
            'company_description.string' => 'La description doit être une chaîne de caractères',

            // Messages pour is_active
            'is_active.boolean' => 'Le statut actif doit être vrai ou faux',

            // Messages pour plan_status
            'plan_status.string' => 'Le statut du plan doit être une chaîne de caractères',
            'plan_status.max' => 'Le statut du plan ne peut pas dépasser 50 caractères',

            // Messages pour plan_start_date
            'plan_start_date.date' => 'La date de début doit être une date valide',

            // Messages pour plan_end_date
            'plan_end_date.date' => 'La date de fin doit être une date valide',
            'plan_end_date.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début',

            // Messages pour company_currency
            'company_currency.string' => 'La devise doit être une chaîne de caractères',
            'company_currency.max' => 'La devise ne peut pas dépasser 10 caractères',

            // Messages pour company_timezone
            'company_timezone.string' => 'Le fuseau horaire doit être une chaîne de caractères',
            'company_timezone.max' => 'Le fuseau horaire ne peut pas dépasser 50 caractères',
        ]);

        if ($validator->fails()) {
            Log::error("Validation failed", ['errors' => $validator->errors()]);

            // $bot->sendMessage(
            //     text: "❌ <b>Erreur de validation</b>\n\n" .
            //     implode("\n", $validator->errors()->all()),
            //     chat_id: $id,
            //     parse_mode: 'HTML'
            // );

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['plan_status'] = $validated['plan_status'] ?? 'free';

            $validated['company_timezone'] = $validated['company_timezone'] ?? 'UTC';
            $validated['company_currency'] = $validated['company_currency'] ?? 'FCFA';

            $company = Company::create($validated);

            $currentPassword = Str::random(16);

            $adminUser = User::create([
                'name' => 'Admin ' . $company->company_name,
                'email' => $company->company_email,
                'password' => Hash::make($currentPassword),
                'company_id' => $company->company_id,
                'user_role' => 'admin_company',
            ]);

            DB::commit();

            Log::info("Company created successfully", [
                'company_id' => $company->company_id,
                'telegram_user_id' => $id
            ]);

            // ✅ CORRECTION: Envoyer le message au bon utilisateur avec chat_id
            $bot->sendMessage(
                text: "✅ <b>Entreprise créée avec succès !</b>\n\n" .
                "📌 <b>Nom:</b> " . e($company->company_name) . "\n" .
                "📧 <b>Email:</b> " . e($company->company_email) . "\n" .
                "📱 <b>Téléphone:</b> " . e($company->company_phone ?? 'Non renseigné') . "\n" .
                "🌐 <b>Site web:</b> " . e($company->company_website ?? 'Non renseigné') . "\n" .
                "📍 <b>Adresse:</b> " . e($company->company_address ?? 'Non renseignée') . "\n" .
                "📝 <b>Description:</b> " . e($company->company_description ?? 'Aucune') . "\n\n" .
                "👤 <b>Compte administrateur créé</b>\n" .
                "Email: " . e($adminUser->email) . "\n" .
                "Mot de passe temporaire: " . e($currentPassword) . "\n" .
                "Rôle: " . e($adminUser->user_role),
                chat_id: $id,
                parse_mode: 'HTML'
            );

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data' => [
                    'company_id' => $company->company_id,
                    'company_name' => $company->company_name,
                    'admin_user_id' => $adminUser->id,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error creating company", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'telegram_user_id' => $id
            ]);

            try {
                $bot->sendMessage(
                    text: "❌ <b>Erreur lors de la création de l'entreprise</b>\n\n" .
                    "Une erreur s'est produite. Veuillez réessayer plus tard ou contactez le support.",
                    chat_id: $id,
                    parse_mode: 'HTML'
                );
            } catch (\Exception $botError) {
                Log::error("Failed to send error message to user", [
                    'error' => $botError->getMessage()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
