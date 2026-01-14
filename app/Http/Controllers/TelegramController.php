<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Client;
use App\Models\Company;
use App\Models\MvtArticle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
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
                "Utiliser la commande /subscribe pour souscrire au plan '⭐ Premiun' ou '🏢 Entreprise'. Plan actuel: " . e($company->plan_status) . "\n\n" .
                "👤 <b>Compte administrateur créé</b>\n" .
                "Email: " . e($adminUser->email) . "\n" .
                "Mot de passe temporaire: " . e($currentPassword) . "\n" .
                "Rôle: " . e($adminUser->user_role) . '\n\n' .
                "👉 <b>Connectez-vous et rendez-vous sur votre compte pour modifier vos informations personnelles et votre mot de passe.</b> \n Lien web : " . url(env('APP_URL')),
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
            'client_name' => 'required|string|max:255|unique:clients,client_name',
            'client_email' => 'required|email|unique:clients,client_email',
            'client_phone' => 'nullable|string|max:20',
            'client_cin' => 'nullable|string|max:20',
            'client_adress' => 'nullable|string|max:255',
        ], [
            'client_name.required' => 'Le nom du client est requis',
            'client_name.string' => 'Le nom du client doit être une chaîne de caractères',
            'client_name.max' => 'Le nom du client ne peut pas dépasser 255 caractères',
            'client_name.unique' => 'Ce nom de client existe déjà',

            'client_email.required' => 'L\'email du client est requis',
            'client_email.email' => 'L\'email doit être une adresse email valide',
            'client_email.unique' => 'Cet email est déjà utilisé',

            'client_phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'client_phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',

            'client_cin.string' => 'Le CIN doit être une chaîne de caractères',
            'client_cin.max' => 'Le CIN ne peut pas dépasser 20 caractères',

            'client_adress.string' => 'L\'adresse doit être une chaîne de caractères',
            'client_adress.max' => 'L\'adresse ne peut pas dépasser 255 caractères',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('telegram_id', $id)->first();

        $clientCount = Client::where('company_id', $user->company_id)->count();
        $maxClients = Client::getMaxClients($user->company->plan_status);

        if ($clientCount >= $maxClients) {
            $message = "⚠️ <b>Limite atteinte</b>\n\n"
                . "Votre plan {$user->company->plan_status} permet {$maxClients} clients maximum.\n"
                . "Vous avez déjà {$clientCount} clients.\n\n"
                . "💎 Passez à un plan supérieur pour ajouter plus de clients.";

            $bot->sendMessage(
                $message,
                parse_mode: 'HTML'
            );

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $client = Client::createClient($validated, $userId, $user->company_id);

            DB::commit();

            $message = "✅ <b>Client créé avec succès !</b>\n\n"
                . $client->formatForDisplay();

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📋 Créer un devis', callback_data: "quote_create_{$client->client_id}"),
                    InlineKeyboardButton::make('👥 Voir tous les clients', callback_data: 'client_list')
                )
                ->addRow(
                    InlineKeyboardButton::make('🏢 Menu Principale', callback_data: 'menu_back')
                );

            $bot->sendMessage($message, chat_id: $id, parse_mode: 'HTML', reply_markup: $keyboard);

            $clientCount = Client::where('company_id', $user->company_id)->count();

            Company::where('company_id', $user->company_id)
                ->update(['client_count' => $clientCount]);

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

    public function createArticle($id, Request $request, Nutgram $bot)
    {
        $userId = null;
        $user = User::where('telegram_id', $id)->first();
        if ($user) {
            $userId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'article_name' => 'required|string|max:255|unique:articles,article_name',
            'selling_price' => 'required|numeric|min:0',
            'article_reference' => 'nullable|string|max:20',
            'article_unité' => 'nullable|string|max:20',
            'article_tva' => 'nullable|numeric|min:0|max:100',
            'quantity_stock' => 'nullable|numeric|min:0',
        ], [
            'article_name.required' => 'Le nom de l’article est obligatoire.',
            'selling_price.required' => 'Le prix de vente est obligatoire.',
            'selling_price.numeric' => 'Le prix doit être un nombre.',
            'article_tva.max' => 'La TVA doit être comprise entre 0 et 100%.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('telegram_id', $id)->first();

        $reference = 'ART-' . strtoupper(substr(md5(uniqid()), 0, 8));

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            $tva = isset($validated['article_tva']) && is_numeric($validated['article_tva'])
                ? (float) $validated['article_tva']
                : 0;

            if ($tva < 0 || $tva > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'La TVA doit être entre 0 et 100%',
                    'errors' => [
                        'article_tva' => ['La TVA doit être entre 0 et 100%']
                    ]
                ], 422);
            }

            $validated['user_id'] = $userId;
            $validated['company_id'] = $user->company_id;
            $validated['article_reference'] = $reference;
            $validated['article_tva'] = $tva;
            $validated['article_source'] = $validated['article_source'] ?? 'Catalogue';


            $article = Article::create($validated);

            $entree = [
                'mvtType' => 'entree',
                'mvt_quantity' => $validated['quantity_stock'],
                'mvt_date' => Carbon::now(),
                'article_id' => $article->article_id,
                'user_id' => $userId,
            ];

            MvtArticle::create($entree);

            DB::commit();

            $message = "✅ <b>Client créé avec succès !</b>\n\n";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}"),
                    InlineKeyboardButton::make('📋 Tous les articles', callback_data: 'article_list')
                )
                ->addRow(
                    InlineKeyboardButton::make('🏢 Menu Principal', callback_data: 'menu_back')
                );

            $bot->sendMessage($message, chat_id: $id, parse_mode: 'HTML', reply_markup: $keyboard);

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error creating article", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'telegram_user_id' => $id
            ]);

            try {
                $bot->sendMessage(
                    text: "❌ <b>Erreur lors de la création du article</b>\n\n" .
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
                'message' => 'Error creating article',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
