<?php

namespace App\Http\Controllers;

use App\Models\Client;
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

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => 'required|email|unique:companies,company_email',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_description' => 'nullable|string',
            'is_active' => 'boolean',
            'plan_status' => 'nullable|string|max:50',
            'plan_start_date' => 'nullable|date',
            'plan_end_date' => 'nullable|date|after_or_equal:plan_start_date',
            'company_currency' => 'nullable|string|max:10',
            'company_timezone' => 'nullable|string|max:50',
        ], [
            'company_name.required' => 'Le nom de l\'entreprise est requis',
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères',
            'company_name.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères',
            'company_name.unique' => 'Ce nom d\'entreprise existe déjà',

            'company_email.required' => 'L\'email de l\'entreprise est requis',
            'company_email.email' => 'L\'email doit être une adresse email valide',
            'company_email.unique' => 'Cet email est déjà utilisé',

            'company_phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'company_phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',

            'company_description.string' => 'La description doit être une chaîne de caractères',
        ]);

        if ($validator->fails()) {
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
                'telegram_id' => $id,
            ]);

            DB::commit();

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

    public function createClient($id, Request $request, Nutgram $bot)
    {
        $userId = null;
        $user = User::where('telegram_id', $id)->first();
        if ($user) {
            $userId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|unique:users,email',
            'client_phone' => 'nullable|string|max:20',
            'client_cin' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $client = Client::createClient($validated, $userId, $user->company_id);

            DB::commit();

            $bot->sendMessage(
                text: "✅ <b>Client créé avec succès !</b>\n\n" .
                "📌 <b>Nom:</b> " . e($client->client_name) . "\n" .
                "📧 <b>Email:</b> " . e($client->client_email) . "\n" .
                "📱 <b>Téléphone:</b> " . e($client->client_phone ?? 'Non renseigné') . "\n" .
                "🆔 <b>CIN:</b> " . e($client->client_cin ?? 'Non renseigné') . "\n" .
                "📍 <b>Adresse:</b> " . e($client->client_address ?? 'Non renseignée') . "\n",
                chat_id: $id,
                parse_mode: 'HTML'
            );

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'data' => [
                    'client_id' => $client->client_id,
                    'client_name' => $client->client_name,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error creating client", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'telegram_user_id' => $id
            ]);

            try {
                $bot->sendMessage(
                    text: "❌ <b>Erreur lors de la création du client</b>\n\n" .
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
                'message' => 'Error creating client',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
