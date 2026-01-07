<?php

namespace App\Telegram\Handlers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Gérer les paiements lors de la création d'entreprise
 */
class CreateCompanyPaymentHandler
{
    /**
     * Traiter le paiement Mobile Money
     */
    public static function processMobilePayment(Nutgram $bot, string $plan): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $price = self::getPlanPrice($plan);
        $currency = config('subscription.currency');
        $planName = strtoupper($plan);

        $message = "💳 <b>Paiement Mobile Money</b>\n\n"
            . "📱 <b>Instructions :</b>\n\n"
            . "1️⃣ Ouvrez votre application Mobile Money\n"
            . "2️⃣ Envoyez <b>" . number_format($price, 0, ',', ' ') . " {$currency}</b> au numéro :\n"
            . "   📞 <code>034 00 000 00</code>\n\n"
            . "3️⃣ Motif : <code>Création Entreprise - Plan {$planName}</code>\n\n"
            . "4️⃣ Une fois le paiement effectué, cliquez sur le bouton ci-dessous\n\n"
            . "⚠️ Le traitement prend environ 5-10 minutes.\n"
            . "✅ Votre entreprise sera activée après validation.\n\n"
            . "💡 Besoin d'aide ? /help";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ J\'ai effectué le paiement', callback_data: "create_confirm_{$plan}_mobile")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Annuler', callback_data: 'plan:cancel')
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
    public static function processBankPayment(Nutgram $bot, string $plan): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        $price = self::getPlanPrice($plan);
        $currency = 'Ar';
        $planName = strtoupper($plan);

        $message = "🏦 <b>Paiement par Virement Bancaire</b>\n\n"
            . "📋 <b>Coordonnées bancaires :</b>\n\n"
            . "🏦 Banque : <b>BNI Madagascar</b>\n"
            . "👤 Titulaire : <b>FacturePro SARL</b>\n"
            . "💳 RIB : <code>00001 00000 12345678901 23</code>\n"
            . "💰 Montant : <b>" . number_format($price, 0, ',', ' ') . " {$currency}</b>\n"
            . "📝 Motif : <code>Création Entreprise - Plan {$planName}</code>\n\n"
            . "⚠️ Le traitement prend 1-2 jours ouvrés.\n"
            . "✅ Votre entreprise sera activée après validation.\n\n"
            . "4️⃣ Une fois le virement effectué, cliquez sur le bouton ci-dessous\n\n"
            . "💡 Besoin d'aide ? /help";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ J\'ai effectué le virement', callback_data: "create_confirm_{$plan}_bank")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Annuler', callback_data: 'plan:cancel')
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
     * Confirmer le paiement et demander la preuve
     */
    public static function confirmPayment(Nutgram $bot, string $plan, string $method): void
    {
        try {
            $bot->answerCallbackQuery("✅ En attente de la preuve");
        } catch (\Exception $e) {
            \Log::debug('Callback already answered: ' . $e->getMessage());
        }

        // Déterminer la méthode de paiement
        $paymentMethod = $method === 'mobile' ? 'mobile_money' : 'bank_transfer';

        // Sauvegarder les données pour le traitement ultérieur
        $bot->setGlobalData('awaiting_creation_payment_proof', true);
        $bot->setGlobalData('creation_payment_plan', $plan);
        $bot->setGlobalData('creation_payment_method', $paymentMethod);
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
            . "✅ <b>Une fois validé, votre entreprise sera activée automatiquement</b>\n\n"
            . "💡 Pour annuler, tapez /cancel";

        try {
            $bot->editMessageText($message, parse_mode: 'HTML');
        } catch (\Exception $e) {
            \Log::warning('Failed to edit message: ' . $e->getMessage());
            $bot->sendMessage($message, parse_mode: 'HTML');
        }
    }

    /**
     * Obtenir le prix d'un plan
     */
    private static function getPlanPrice(string $plan): int
    {
        // Utiliser les prix de votre modèle Payment
        $prices = \App\Models\Payment::getPlanPrices();

        return match ($plan) {
            'premium' => $prices['premium'] ?? 9900,
            'entreprise', 'enterprise' => $prices['enterprise'] ?? 14900,
            default => 0
        };
    }
}
