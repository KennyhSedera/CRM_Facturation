<?php

namespace App\Telegram\Commands;

use App\Models\Company;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class CreateCompanyCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $this->handle($bot);
    }

    public function handle($bot): void
    {
        $telegramUser = $bot->user();

        $startCommand = new StartCommand();
        $message = $startCommand->getWelcomeMessageNewUser($telegramUser->first_name);

        $bot->sendMessage(
            text: $message,
            parse_mode: ParseMode::HTML,
            reply_markup: $this->getPlanKeyboard($bot)
        );
    }

    protected function getPlanKeyboard(Nutgram $bot): InlineKeyboardMarkup
    {
        $telegramUser = $bot->user();
        $webAppUrl = route('webapp.form.company', ['user_id' => $telegramUser->id]);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📝 Créer mon entreprise',
                    web_app: new WebAppInfo($webAppUrl)
                )
            );

        return $keyboard;
    }

    public static function handlePlanSelection(Nutgram $bot): void
    {
        $callbackData = $bot->callbackQuery()->data;
        $userId = $bot->userId();

        $plan = str_replace('plan:', '', $callbackData);

        if ($plan === 'cancel') {
            $bot->answerCallbackQuery();
            $bot->editMessageText(
                text: "❌ Création d'entreprise annulée.\n\n" .
                "Vous pouvez recommencer à tout moment avec /createcompany",
                parse_mode: ParseMode::HTML
            );

            self::cancelProcess($bot);
            return;
        }

        $bot->setUserData('selected_plan', $plan);
        $bot->answerCallbackQuery(text: "✅ Plan sélectionné : " . ucfirst($plan));

        // Message demandant toutes les infos en une fois
        $message = "✏️ <b>Informations de l'entreprise</b>\n\n"
            . "Envoyez-moi les informations de votre entreprise dans ce format :\n\n"
            . "<code>Nom de l'entreprise\n"
            . "Email\n"
            . "Description\n"
            . "Téléphone\n"
            . "Site web (optionnel)\n"
            . "Adresse</code>\n\n"
            . "<b>Exemple :</b>\n"
            . "<code>TechSolutions SARL\n"
            . "contact@techsolutions.mg\n"
            . "Développement de solutions web et mobile pour entreprises\n"
            . "+261 34 12 345 67\n"
            . "www.techsolutions.mg\n"
            . "Lot II A 45 Antananarivo</code>\n\n"
            . "💡 Le site web est optionnel. Les autres champs sont obligatoires.\n\n"
            . "💡 Tapez /cancel pour annuler";

        $bot->editMessageText($message, parse_mode: ParseMode::HTML);

        $bot->setUserData('awaiting_company_data', true);
    }

    public static function handleCompanyData(Nutgram $bot): void
    {
        $text = trim($bot->message()->text);

        if (strtolower($text) === '/cancel') {
            self::cancelProcess($bot);
            $bot->sendMessage(
                text: "❌ <b>Création d'entreprise annulée</b>\n\n" .
                "Toutes vos données ont été supprimées.\n" .
                "Vous pouvez recommencer avec /createcompany",
                parse_mode: ParseMode::HTML
            );
            return;
        }

        $lines = array_map('trim', explode("\n", $text));

        if (count($lines) < 5) {
            $bot->sendMessage(
                "❌ <b>Format incorrect</b>\n\n"
                . "Vous devez fournir au minimum :\n"
                . "1. Nom de l'entreprise\n"
                . "2. Email\n"
                . "3. Description\n"
                . "4. Téléphone\n"
                . "5. Adresse\n\n"
                . "💡 Le site web est optionnel.\n\n"
                . "Réessayez ou tapez /cancel pour annuler.",
                parse_mode: ParseMode::HTML
            );
            return;
        }

        // Extraire les données
        $companyName = $lines[0] ?? '';
        $companyEmail = $lines[1] ?? '';
        $companyDescription = $lines[2] ?? '';
        $companyPhone = $lines[3] ?? '';

        // Site web (optionnel) et adresse
        if (count($lines) >= 6) {
            $companyWebsite = $lines[4];
            $companyAddress = $lines[5];
        } else {
            $companyWebsite = null;
            $companyAddress = $lines[4];
        }

        // Validations
        $errors = [];

        if (strlen($companyName) < 2) {
            $errors[] = "• Le nom doit contenir au moins 2 caractères";
        }

        if (!filter_var($companyEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "• L'email est invalide";
        }

        if (strlen($companyDescription) < 10) {
            $errors[] = "• La description doit contenir au moins 10 caractères";
        }

        if (strlen($companyPhone) < 8) {
            $errors[] = "• Le téléphone doit contenir au moins 8 caractères";
        }

        if (strlen($companyAddress) < 5) {
            $errors[] = "• L'adresse doit contenir au moins 5 caractères";
        }

        $existingCompanyByName = Company::where('company_name', $companyName)->first();
        if ($existingCompanyByName) {
            $errors[] = "• Ce nom d'entreprise est déjà utilisé";
        }

        $existingCompanyByEmail = Company::where('company_email', $companyEmail)->first();
        if ($existingCompanyByEmail) {
            $errors[] = "• Cet email est déjà utilisé par une autre entreprise";
        }

        // Si erreurs, afficher et demander de réessayer
        if (!empty($errors)) {
            $bot->sendMessage(
                "❌ <b>Erreurs de validation</b>\n\n"
                . implode("\n", $errors) . "\n\n"
                . "Veuillez corriger et réessayer.\n\n"
                . "💡 Tapez /cancel pour annuler",
                parse_mode: ParseMode::HTML
            );
            return;
        }

        // Stocker les données
        $bot->setUserData('company_name', $companyName);
        $bot->setUserData('company_email', $companyEmail);
        $bot->setUserData('company_description', $companyDescription);
        $bot->setUserData('company_phone', $companyPhone);
        $bot->setUserData('company_website', $companyWebsite);
        $bot->setUserData('company_address', $companyAddress);

        // Vérifier si le plan nécessite un paiement
        $plan = $bot->getUserData('selected_plan');
        if (in_array($plan, ['premium', 'entreprise'])) {
            self::requestPayment($bot);
        } else {
            self::createCompany($bot);
        }
    }

    /**
     * Demander le paiement pour les plans Premium/Entreprise
     */
    protected static function requestPayment(Nutgram $bot): void
    {
        $plan = $bot->getUserData('selected_plan');
        $price = Payment::getPlanPrice($plan);

        $currency = config('subscription.currency') ?? 'FCFA';
        $planName = strtoupper($plan);
        $planEmoji = $plan === 'premium' ? '⭐' : '🏢';

        $benefits = [
            'premium' => "• 50 clients\n• Devis illimités\n• Support prioritaire\n• Personnalisation avancée",
            'entreprise' => "• Clients illimités\n• Devis illimités\n• Support dédié 24/7\n• API personnalisée\n• Formation incluse",
        ];

        $message = "💳 <b>Paiement requis</b>\n\n"
            . "{$planEmoji} <b>Plan {$planName}</b>\n\n"
            . "📋 <b>Avantages :</b>\n{$benefits[$plan]}\n\n"
            . "💰 Prix : <b>" . $price . " {$currency}/mois</b>\n"
            . "📅 Durée : <b>1 mois</b>\n\n"
            . "⚠️ <b>Important :</b> Votre entreprise sera créée après validation du paiement par notre équipe.\n\n"
            . "Choisissez votre mode de paiement :";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Mobile Money', callback_data: "create_payment_mobile_{$plan}"),
                InlineKeyboardButton::make('🏦 Virement bancaire', callback_data: "create_payment_bank_{$plan}")
            )
            ->addRow(
                InlineKeyboardButton::make('❌ Annuler', callback_data: 'plan:cancel')
            );

        $bot->sendMessage(
            text: $message,
            parse_mode: ParseMode::HTML,
            reply_markup: $keyboard
        );
    }

    /**
     * Annuler le processus de création d'entreprise
     */
    public static function cancelProcess(Nutgram $bot): void
    {
        $bot->deleteUserData('selected_plan');
        $bot->deleteUserData('awaiting_company_data');
        $bot->deleteUserData('company_name');
        $bot->deleteUserData('company_email');
        $bot->deleteUserData('company_description');
        $bot->deleteUserData('company_phone');
        $bot->deleteUserData('company_website');
        $bot->deleteUserData('company_address');

        \Log::info('Company creation process cancelled', [
            'user_id' => $bot->userId()
        ]);
    }

    /**
     * Créer l'entreprise (PUBLIC pour être appelée depuis PaymentProofHandler)
     */
    public static function createCompany(Nutgram $bot, $isActive = null): void
    {
        try {
            $plan = $bot->getUserData('selected_plan');

            $planStatus = match ($plan) {
                'free' => 'free',
                'premium' => 'premium',
                'entreprise' => 'enterprise',
                default => 'free'
            };

            $planPrice = Payment::getPlanPrice($plan);

            if ($isActive === null) {
                $isActive = ($plan === 'free');
            }

            $company = Company::create([
                'plan_status' => $planStatus,
                'company_name' => $bot->getUserData('company_name'),
                'company_email' => $bot->getUserData('company_email'),
                'company_description' => $bot->getUserData('company_description'),
                'company_phone' => $bot->getUserData('company_phone'),
                'company_website' => $bot->getUserData('company_website'),
                'company_address' => $bot->getUserData('company_address'),
                'is_active' => $isActive,
                'plan_start_date' => $isActive ? now() : null,
                'plan_end_date' => $isActive ? now()->addMonth() : null,
                'company_currency' => 'FCFA',
                'company_timezone' => 'Indian/Antananarivo',
                'client_count' => 0,
            ]);

            $adminUser = User::create([
                'name' => 'Admin ' . $company->company_name,
                'email' => $company->company_email,
                'password' => Hash::make($company->company_name),
                'company_id' => $company->company_id,
                'telegram_id' => $bot->user()->id,
                'user_role' => 'admin_company',
            ]);

            $planName = match ($plan) {
                'free' => 'Gratuit',
                'premium' => 'Premium',
                'entreprise' => 'Entreprise',
                default => 'Gratuit'
            };

            // Message différent selon si l'entreprise est active ou non
            if ($isActive) {
                $statusMessage = "✅ <b>Votre entreprise est maintenant active !</b>";
            } else {
                $statusMessage = "⏳ <b>Votre entreprise est en attente de validation du paiement</b>\n\n"
                    . "Vous recevrez une notification dès que votre paiement sera validé.";
            }

            $bot->sendMessage(
                text: "✅ <b>Entreprise créée avec succès !</b>\n\n"
                . "🏢 <b>" . htmlspecialchars($company->company_name) . "</b>\n"
                . "📧 " . htmlspecialchars($company->company_email) . "\n"
                . "📦 Plan : " . $planName . "\n"
                . "💰 Prix : " . number_format($planPrice, 0, ',', ' ') . " Ar\n\n"
                . $statusMessage . "\n\n"
                . "ID Entreprise : <code>" . $company->company_id . "</code>\n\n"
                . "Votre utilisateur principal :\n"
                . "Email : " . $company->company_email . "\n"
                . "Mot de passe : " . $company->company_name,
                parse_mode: ParseMode::HTML
            );

            self::cancelProcess($bot);

            \Log::info('Company created successfully', [
                'user_id' => $bot->userId(),
                'company_id' => $company->company_id,
                'is_active' => $isActive
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating company: ' . $e->getMessage(), [
                'user_id' => $bot->userId(),
                'trace' => $e->getTraceAsString()
            ]);

            $bot->sendMessage(
                text: "❌ <b>Erreur lors de la création</b>\n\n"
                . "Une erreur est survenue. Veuillez réessayer avec /createcompany",
                parse_mode: ParseMode::HTML
            );

            self::cancelProcess($bot);
        }
    }
}
