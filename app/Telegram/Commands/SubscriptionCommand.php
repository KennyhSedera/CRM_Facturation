<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

/**
 * Commande pour gérer l'abonnement
 */
class SubscriptionCommand extends Command
{
    protected string $command = 'subscription';
    protected ?string $description = 'Gérer mon abonnement';

    public function handle(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Vous devez d'abord créer votre entreprise. Utilisez /start");
            return;
        }

        $company = $user->company;
        $planEndDate = Carbon::parse($company->plan_end_date);
        $daysRemaining = (int) round(now()->diffInDays($planEndDate, false));

        $planEmoji = $this->getPlanEmoji($company->plan_status);
        $planName = strtoupper($company->plan_status);

        $message = "💳 <b>Votre Abonnement</b>\n\n"
            . "🏢 Entreprise : <b>{$company->company_name}</b>\n"
            . "{$planEmoji} Plan actuel : <b>{$planName}</b>\n"
            . "📅 Date de fin : " . $planEndDate->format('d/m/Y') . "\n";

        if ($daysRemaining > 0) {
            $message .= "⏰ Jours restants : <b>{$daysRemaining} jours</b>\n\n";

            if ($daysRemaining <= 7) {
                $message .= "⚠️ <b>Votre abonnement expire bientôt !</b>\n";
            }
        } elseif ($daysRemaining == 0) {
            $message .= "⚠️ <b>Votre abonnement expire aujourd'hui !</b>\n\n";
        } else {
            $message .= "❌ <b>Votre abonnement a expiré</b>\n\n";
        }

        // Afficher les limites du plan
        $message .= $this->getPlanLimits($company->plan_status);

        $keyboard = InlineKeyboardMarkup::make();

        // Options selon le plan et l'état
        if ($company->plan_status === 'free') {
            $keyboard->addRow(
                InlineKeyboardButton::make('⭐ Passer à PREMIUM', callback_data: 'subscription_upgrade_premium'),
                InlineKeyboardButton::make('🏢 Passer à ENTREPRISE', callback_data: 'subscription_upgrade_enterprise')
            );
        } else {
            if ($company->plan_status === 'premium') {
                $keyboard->addRow(
                    InlineKeyboardButton::make(
                        '🔄 Renouveler mon abonnement',
                        callback_data: "subscription_renew_{$company->plan_status}"
                    ),
                    InlineKeyboardButton::make(
                        '⬆️ Passer à ENTREPRISE',
                        callback_data: 'subscription_upgrade_enterprise'
                    )
                );
            } else {
                $keyboard->addRow(
                    InlineKeyboardButton::make(
                        '🔄 Renouveler mon abonnement',
                        callback_data: "subscription_renew_{$company->plan_status}"
                    )
                );
            }

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📜 Historique des paiements',
                    callback_data: 'subscription_history'
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('🔙 Retour', callback_data: 'menu_back')
        );

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }

    private function getPlanEmoji(string $plan): string
    {
        $emojis = [
            'free' => '🆓',
            'premium' => '⭐',
            'enterprise' => '🏢',
        ];
        return $emojis[$plan] ?? '📦';
    }

    private function getPlanLimits(string $plan): string
    {
        $limits = [
            'free' => "📊 <b>Limites du plan GRATUIT :</b>\n• 3 clients maximum\n• 5 devis par mois\n• Calculatrice illimitée\n\n",
            'premium' => "📊 <b>Avantages du plan PREMIUM :</b>\n• 50 clients\n• Devis illimités\n• Support prioritaire\n• Personnalisation avancée\n\n",
            'enterprise' => "📊 <b>Avantages du plan ENTREPRISE :</b>\n• Clients illimités\n• Devis illimités\n• Support dédié 24/7\n• API personnalisée\n• Formation incluse\n\n",
        ];
        return $limits[$plan] ?? '';
    }
}

/**
 * Gestion des callbacks d'abonnement
 */
class SubscriptionCallbackHandler
{
    /**
     * Obtenir le prix depuis la config
     */
    private static function getPlanPrice(string $plan): int
    {
        $planKey = strtoupper($plan);
        $price = config("subscription.plans.$planKey.price", 0);
        return (int) ($price * 1000); // 9.900 -> 9900
    }

    /**
     * Renouveler l'abonnement
     */
    public static function renewSubscription(Nutgram $bot, string $plan): void
    {
        // ✅ Répondre immédiatement au callback
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Entreprise non trouvée");
            return;
        }

        $price = self::getPlanPrice($plan);
        $currency = config('subscription.currency', 'FCFA');
        $planName = strtoupper($plan);
        $planEmoji = $plan === 'premium' ? '⭐' : '🏢';

        $message = "{$planEmoji} <b>Renouvellement - Plan {$planName}</b>\n\n"
            . "💰 Prix : <b>" . number_format($price, 0, ',', ' ') . " {$currency}</b>\n"
            . "📅 Durée : <b>1 mois</b>\n\n"
            . "Choisissez votre mode de paiement :";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Mobile Money', callback_data: "payment_mobile_{$plan}_renew"),
                InlineKeyboardButton::make('🏦 Virement bancaire', callback_data: "payment_bank_{$plan}_renew")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'subscription_back')
            );

        try {
            $bot->editMessageText(
                text: $message,
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);
        }
    }

    /**
     * Passer à un plan supérieur
     */
    public static function upgradePlan(Nutgram $bot, string $newPlan): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Entreprise non trouvée");
            return;
        }

        $price = self::getPlanPrice($newPlan);
        $currency = config('subscription.currency', 'FCFA');
        $planName = strtoupper($newPlan);
        $planEmoji = $newPlan === 'premium' ? '⭐' : '🏢';

        $benefits = [
            'premium' => "• 50 clients\n• Devis illimités\n• Support prioritaire\n• Personnalisation avancée",
            'enterprise' => "• Clients illimités\n• Devis illimités\n• Support dédié 24/7\n• API personnalisée\n• Formation incluse",
        ];

        $message = "{$planEmoji} <b>Passer au plan {$planName}</b>\n\n"
            . "📋 <b>Avantages :</b>\n{$benefits[$newPlan]}\n\n"
            . "💰 Prix : <b>" . number_format($price, 0, ',', ' ') . " {$currency}/mois</b>\n"
            . "📅 Durée : <b>1 mois</b>\n\n"
            . "Choisissez votre mode de paiement :";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Mobile Money', callback_data: "payment_mobile_{$newPlan}_upgrade"),
                InlineKeyboardButton::make('🏦 Virement bancaire', callback_data: "payment_bank_{$newPlan}_upgrade")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'subscription_back')
            );

        try {
            $bot->editMessageText(
                text: $message,
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);
        }
    }

    /**
     * Notifier les administrateurs
     */
    private static function notifyAdmins(Nutgram $bot, User $user, string $plan, string $action): void
    {
        // ID Telegram des admins (à configurer dans .env)
        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));

        $actionText = $action === 'renew' ? 'Renouvellement' : 'Upgrade';

        $message = "🔔 <b>Nouvelle demande de paiement</b>\n\n"
            . "👤 Utilisateur : {$user->name}\n"
            . "🏢 Entreprise : {$user->company->company_name}\n"
            . "💳 Type : {$actionText}\n"
            . "📦 Plan : " . strtoupper($plan) . "\n"
            . "📅 Date : " . now()->format('d/m/Y H:i') . "\n\n"
            . "🆔 User ID : {$user->id}\n"
            . "🆔 Telegram ID : {$user->telegram_id}";

        foreach ($adminIds as $adminId) {
            if ($adminId) {
                try {
                    $bot->sendMessage($message, chat_id: trim($adminId), parse_mode: 'HTML');
                } catch (\Exception $e) {
                    \Log::error("Failed to notify admin {$adminId}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Afficher l'historique des paiements
     */
    public static function showPaymentHistory(Nutgram $bot): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $message = "📜 <b>Historique des paiements</b>\n\n"
            . "🚧 Cette fonctionnalité sera bientôt disponible.\n\n"
            . "Elle vous permettra de consulter :\n"
            . "• Tous vos paiements\n"
            . "• Les factures\n"
            . "• Les dates de renouvellement";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'subscription_back')
            );

        try {
            $bot->editMessageText(
                text: $message,
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);
        }
    }

    /**
     * Retour au menu abonnement
     */
    public static function backToSubscription(Nutgram $bot): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $subscriptionCmd = new SubscriptionCommand();
        $subscriptionCmd->handle($bot);
    }

    /**
     * Traiter le paiement Mobile Money
     */
    public static function processMobilePayment(Nutgram $bot, string $plan, string $action): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $price = self::getPlanPrice($plan);
        $currency = config('subscription.currency', 'FCFA');
        $actionText = $action === 'renew' ? 'Renouvellement' : 'Upgrade';

        $message = "💳 <b>Paiement Mobile Money</b>\n\n"
            . "📱 <b>Instructions :</b>\n\n"
            . "1️⃣ Ouvrez votre application Mobile Money\n"
            . "2️⃣ Envoyez <b>" . number_format($price, 0, ',', ' ') . " {$currency}</b> au numéro :\n"
            . "   📞 <code>034 00 000 00</code>\n\n"
            . "3️⃣ Motif : <code>{$actionText} Plan " . strtoupper($plan) . "</code>\n\n"
            . "4️⃣ Une fois le paiement effectué, cliquez sur le bouton ci-dessous\n\n"
            . "⚠️ Le traitement prend environ 5-10 minutes.\n\n"
            . "💡 Besoin d'aide ? /help";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ J\'ai effectué le paiement', callback_data: "payment_confirm_{$plan}_{$action}_mobile")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'subscription_back')
            );

        try {
            $bot->editMessageText(
                text: $message,
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);
        }
    }

    /**
     * Traiter le paiement par virement bancaire
     */
    public static function processBankPayment(Nutgram $bot, string $plan, string $action): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $price = self::getPlanPrice($plan);
        $currency = config('subscription.currency', 'FCFA');
        $actionText = $action === 'renew' ? 'Renouvellement' : 'Upgrade';

        $message = "🏦 <b>Paiement par Virement Bancaire</b>\n\n"
            . "📋 <b>Coordonnées bancaires :</b>\n\n"
            . "🏦 Banque : <b>BNI Madagascar</b>\n"
            . "👤 Titulaire : <b>FacturePro SARL</b>\n"
            . "💳 RIB : <code>00001 00000 12345678901 23</code>\n"
            . "💰 Montant : <b>" . number_format($price, 0, ',', ' ') . " {$currency}</b>\n"
            . "📝 Motif : <code>{$actionText} Plan " . strtoupper($plan) . "</code>\n\n"
            . "⚠️ Le traitement prend 1-2 jours ouvrés.\n\n"
            . "4️⃣ Une fois le virement effectué, cliquez sur le bouton ci-dessous\n\n"
            . "💡 Besoin d'aide ? /help";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ J\'ai effectué le virement', callback_data: "payment_confirm_{$plan}_{$action}_bank")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'subscription_back')
            );

        try {
            $bot->editMessageText(
                text: $message,
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);
        }
    }

    /**
     * Confirmer le paiement (version améliorée)
     */
    public static function confirmPayment(Nutgram $bot, string $plan, string $action, string $method): void
    {
        try {
            $bot->answerCallbackQuery("✅ En attente de la preuve");
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        // Déterminer la méthode de paiement
        $paymentMethod = $method === 'mobile' ? 'mobile_money' : 'bank_transfer';

        // Activer le mode "attente de preuve"
        $bot->setGlobalData('awaiting_payment_proof', true);
        $bot->setGlobalData('payment_plan', $plan);
        $bot->setGlobalData('payment_action', $action);
        $bot->setGlobalData('payment_method', $paymentMethod);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);

        $message = "📸 <b>Envoi de la preuve de paiement</b>\n\n"
            . "Veuillez maintenant envoyer :\n\n"
            . "1️⃣ <b>Une capture d'écran</b> de votre reçu de paiement\n"
            . "   OU\n"
            . "2️⃣ Le <b>numéro de transaction</b> (texte)\n\n"
            . "📎 Formats acceptés : Photo (JPG, PNG) ou PDF\n\n"
            . "⚠️ Assurez-vous que les informations suivantes sont visibles :\n"
            . "• Montant exact du paiement\n"
            . "• Date et heure\n"
            . "• Numéro de référence\n\n"
            . "💡 Pour annuler, tapez /cancel";

        try {
            $bot->editMessageText($message, parse_mode: 'HTML');
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML');
        }
    }
}
