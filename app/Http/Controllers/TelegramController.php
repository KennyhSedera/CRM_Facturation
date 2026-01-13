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
        ]);

        if ($validator->fails()) {
            Log::error("Validation failed", ['errors' => $validator->errors()]);

            // Envoyer un message d'erreur à l'utilisateur Telegram
            $bot->sendMessage(
                text: "❌ <b>Erreur de validation</b>\n\n" .
                implode("\n", $validator->errors()->all()),
                chat_id: $id,
                parse_mode: 'HTML'
            );

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $company = Company::create($validated);

            $adminUser = User::create([
                'name' => 'Admin ' . $company->company_name,
                'email' => $company->company_email,
                'password' => Hash::make(Str::random(16)), // Mot de passe sécurisé aléatoire
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
                "Mot de passe temporaire: " . e($adminUser->password) . "\n" .
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
