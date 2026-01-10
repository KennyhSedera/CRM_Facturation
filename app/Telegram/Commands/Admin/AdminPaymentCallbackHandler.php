<?php

namespace App\Telegram\Commands\Admin;

use App\Models\Company;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AdminPaymentCallbackHandler
{
    /**
     * Voir les détails d'un paiement
     */
    public static function viewPayment(Nutgram $bot, int $paymentId): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered');
        }

        $payment = Payment::with(['user', 'company'])->find($paymentId);

        if (!$payment) {
            $bot->sendMessage("❌ Paiement non trouvé.");
            return;
        }

        $message = $payment->formatForDisplay();

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Valider', callback_data: "admin_payment_approve_{$paymentId}"),
                InlineKeyboardButton::make('❌ Rejeter', callback_data: "admin_payment_reject_{$paymentId}")
            )
            ->addRow(
                InlineKeyboardButton::make('📸 Voir la preuve', callback_data: "admin_payment_proof_{$paymentId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'admin_payments_list')
            );

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }

    /**
     * Afficher la preuve de paiement
     */
    public static function showProof(Nutgram $bot, int $paymentId): void
    {
        try {
            $bot->answerCallbackQuery();
        } catch (\Exception $e) {
            \Log::debug('Callback already answered');
        }

        $payment = Payment::with(['user', 'company'])->find($paymentId);

        if (!$payment) {
            $bot->sendMessage("❌ Paiement non trouvé.");
            return;
        }

        if (!$payment->transaction_proof) {
            $bot->sendMessage("❌ Aucune preuve de paiement disponible.");
            return;
        }

        try {
            // Récupérer le chemin du fichier
            $filePath = storage_path('app/public/' . $payment->transaction_proof);

            if (!file_exists($filePath)) {
                $bot->sendMessage("❌ Fichier de preuve introuvable.");
                return;
            }

            $caption = "📸 <b>Preuve de paiement</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n"
                . "👤 Client : {$payment->user->name}\n"
                . "💰 Montant : " . number_format((float) $payment->amount, 0, ',', ' ') . " FCFA";

            // Envoyer la photo/document
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $bot->sendPhoto(
                    fopen($filePath, 'r'),
                    caption: $caption,
                    parse_mode: 'HTML'
                );
            } else {
                $bot->sendDocument(
                    fopen($filePath, 'r'),
                    caption: $caption,
                    parse_mode: 'HTML'
                );
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send proof: ' . $e->getMessage());

            // Alternative : envoyer l'URL
            $proofUrl = asset('storage/' . $payment->transaction_proof);
            $bot->sendMessage(
                "📸 <b>Preuve de paiement</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
                . "🔗 Lien : {$proofUrl}",
                parse_mode: 'HTML'
            );
        }
    }

    /**
     * Valider un paiement
     */
    public static function approvePayment(Nutgram $bot, int $paymentId): void
    {
        try {
            $bot->answerCallbackQuery("✅ Validation en cours...");
        } catch (\Exception $e) {
            \Log::debug('Callback already answered');
        }

        $payment = Payment::with(['user', 'company'])->find($paymentId);

        if (!$payment) {
            $bot->sendMessage("❌ Paiement non trouvé.");
            return;
        }

        if ($payment->status !== 'pending') {
            $bot->sendMessage("⚠️ Ce paiement a déjà été traité.");
            return;
        }

        try {
            $adminId = $bot->user()->id;
            $user = User::where('telegram_id', $adminId)->first();
            Company::where('company_id', $user->company_id)
                ->update([
                    'is_active' => true,
                ]);

            $payment->confirm($user->id, "Approuvé par l'admin via Telegram");

            $bot->sendMessage(
                "✅ <b>Paiement validé !</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n"
                . "👤 Client : {$payment->user->name}\n"
                . "📦 Plan : " . strtoupper($payment->plan_type) . "\n\n"
                . "Le plan a été activé automatiquement.",
                parse_mode: 'HTML'
            );

            // Notifier le client
            self::notifyUserApproval($bot, $payment);

        } catch (\Exception $e) {
            \Log::error('Failed to approve payment: ' . $e->getMessage());
            $bot->sendMessage("❌ Erreur lors de la validation du paiement.");
        }
    }

    /**
     * Rejeter un paiement
     */
    public static function rejectPayment(Nutgram $bot, int $paymentId): void
    {
        try {
            $bot->answerCallbackQuery("Raison du rejet ?");
        } catch (\Exception $e) {
            \Log::debug('Callback already answered');
        }

        $payment = Payment::with(['user', 'company'])->find($paymentId);

        if (!$payment) {
            $bot->sendMessage("❌ Paiement non trouvé.");
            return;
        }

        // Demander la raison
        $bot->setGlobalData('awaiting_reject_reason', true);
        $bot->setGlobalData('reject_payment_id', $paymentId);

        $bot->sendMessage(
            "✍️ <b>Raison du rejet</b>\n\n"
            . "Veuillez indiquer la raison du rejet de ce paiement :\n\n"
            . "Tapez /cancel pour annuler.",
            parse_mode: 'HTML'
        );
    }

    /**
     * Traiter la raison du rejet
     */
    public static function processRejectReason(Nutgram $bot): void
    {
        $paymentId = $bot->getGlobalData('reject_payment_id');
        $reason = trim($bot->message()->text);

        $payment = Payment::with(['user', 'company'])->find($paymentId);
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$payment) {
            $bot->sendMessage("❌ Paiement non trouvé.");
            return;
        }

        try {
            $adminId = $user->id;
            $payment->reject($adminId, $reason);

            $bot->sendMessage(
                "❌ <b>Paiement rejeté</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n"
                . "👤 Client : {$payment->user->name}\n"
                . "📝 Raison : {$reason}",
                parse_mode: 'HTML'
            );

            // Notifier le client
            self::notifyUserRejection($bot, $payment, $reason);

            $bot->deleteGlobalData('awaiting_reject_reason');
            $bot->deleteGlobalData('reject_payment_id');

        } catch (\Exception $e) {
            \Log::error('Failed to reject payment: ' . $e->getMessage());
            $bot->sendMessage("❌ Erreur lors du rejet.");
        }
    }

    /**
     * Notifier l'utilisateur de l'approbation
     */
    private static function notifyUserApproval(Nutgram $bot, Payment $payment): void
    {
        $message = "🎉 <b>Paiement approuvé !</b>\n\n"
            . "✅ Votre paiement a été validé\n"
            . "📱 Référence : <code>{$payment->payment_reference}</code>\n"
            . "📦 Plan : <b>" . strtoupper($payment->plan_type) . "</b>\n\n"
            . "Votre plan est maintenant actif !\n\n"
            . "💡 Utilisez /subscription pour voir vos détails.";

        try {
            $bot->sendMessage(
                $message,
                chat_id: $payment->user->telegram_id,
                parse_mode: 'HTML'
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify user {$payment->user->telegram_id}: " . $e->getMessage());
        }
    }

    /**
     * Notifier l'utilisateur du rejet
     */
    private static function notifyUserRejection(Nutgram $bot, Payment $payment, string $reason): void
    {
        $message = "❌ <b>Paiement rejeté</b>\n\n"
            . "Votre paiement n'a pas pu être validé.\n"
            . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
            . "📝 <b>Raison :</b>\n{$reason}\n\n"
            . "💡 Veuillez réessayer ou contacter le support : /help";

        try {
            $bot->sendMessage(
                $message,
                chat_id: $payment->user->telegram_id,
                parse_mode: 'HTML'
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify user {$payment->user->telegram_id}: " . $e->getMessage());
        }
    }
}
