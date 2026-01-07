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
                parse_mode: 'HTML'
            );
            return;
        }

        $message = "💳 <b>Paiements en attente</b>\n\n"
            . "📊 Total : <b>{$pendingPayments->count()}</b> paiement(s)\n\n";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($pendingPayments as $payment) {
            $planEmoji = $payment->plan_type === 'premium' ? '⭐' : '🏢';
            $amount = number_format((float) $payment->amount, 0, ',', ' ');

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$planEmoji} {$payment->user->name} - {$amount} FCFA",
                    callback_data: "admin_payment_view_{$payment->payment_id}"
                )
            );
        }

        $bot->sendMessage(
            text: $message . "Sélectionnez un paiement pour le valider :",
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }
}
