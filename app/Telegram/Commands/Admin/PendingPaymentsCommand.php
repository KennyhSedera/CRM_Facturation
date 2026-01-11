<?php

namespace App\Telegram\Commands\Admin;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use App\Models\Payment;

class PendingPaymentsCommand extends Command
{
    protected string $command = 'pending_payments';
    protected ?string $description = '[Admin] Voir les paiements en attente';

    public function handle(Nutgram $bot): void
    {
        // Vérifier si l'utilisateur est admin
        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));

        if (!in_array($bot->user()->id, array_map('trim', $adminIds))) {
            $bot->sendMessage("❌ Commande réservée aux administrateurs.");
            return;
        }

        // Récupérer les paiements en attente
        $pendingPayments = Payment::with(['user', 'company'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $bot->sendMessage(
                "✅ <b>Aucun paiement en attente</b>\n\n"
                . "Tous les paiements ont été traités.",
                parse_mode: 'HTML',
                reply_markup: InlineKeyboardMarkup::make()->addRow(
                    InlineKeyboardButton::make('🔙 Menu Admin', callback_data: 'admin_menu')
                )
            );
            return;
        }

        // Construire le message
        $message = "💳 <b>Paiements en attente</b>\n\n"
            . "📊 Total : <b>{$pendingPayments->count()}</b> paiement(s)\n\n";

        foreach ($pendingPayments as $index => $payment) {
            $planEmoji = $payment->plan_type === 'premium' ? '⭐' : '🏢';
            $amount = number_format((float) $payment->amount, 0, ',', ' ');

            $message .= ($index + 1) . ". {$planEmoji} <b>{$payment->company->company_name}</b>\n";
            $message .= "   💰 {$amount} FCFA\n";
            $message .= "   📋 Type: " . $payment->getActionTypeLabel() . "\n";
            $message .= "   📅 " . $payment->created_at->format('d/m/Y H:i') . "\n\n";
        }

        $message .= "👇 Sélectionnez un paiement pour voir les détails :";

        // Créer le clavier
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($pendingPayments as $index => $payment) {
            $planEmoji = $payment->plan_type === 'premium' ? '⭐' : '🏢';

            // Limiter la longueur du nom
            $companyName = mb_strlen($payment->company->company_name) > 25
                ? mb_substr($payment->company->company_name, 0, 25) . '...'
                : $payment->company->company_name;

            $buttonText = ($index + 1) . ". {$planEmoji} {$companyName}";

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    $buttonText,
                    callback_data: "admin_payment_view_{$payment->payment_id}"
                )
            );
        }

        // Ajouter un bouton retour
        $keyboard->addRow(
            InlineKeyboardButton::make('🔙 Menu Admin', callback_data: 'admin_menu')
        );

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }
}
