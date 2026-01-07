<?php

namespace App\Telegram\Callbacks;

use SergiX44\Nutgram\Nutgram;
use App\Models\Ticket;
use App\Telegram\Keyboards\MainMenuKeyboard;
use App\Telegram\Keyboards\TicketKeyboard;

class TicketCallback
{
    /**
     * Afficher les détails d'un ticket
     */
    public static function show(Nutgram $bot, int $ticketId): void
    {
        $bot->answerCallbackQuery();

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            $bot->editMessageText("❌ Ticket non trouvé");
            return;
        }

        // Vérifier que le ticket appartient à l'utilisateur
        if ($ticket->user_telegram_id != $bot->userId()) {
            $bot->answerCallbackQuery("❌ Accès refusé");
            return;
        }

        $message = "🎫 <b>Ticket #{$ticket->id}</b>\n\n"
            . "📝 Sujet : {$ticket->subject}\n"
            . "📊 Statut : {$ticket->status}\n"
            . "📅 Créé le : " . $ticket->created_at->format('d/m/Y H:i');

        $bot->editMessageText(
            text: $message,
            reply_markup: TicketKeyboard::details($ticket),
            parse_mode: 'HTML'
        );
    }

    /**
     * Clôturer un ticket
     */
    public static function close(Nutgram $bot, int $ticketId): void
    {
        $ticket = Ticket::find($ticketId);

        if (!$ticket || $ticket->user_telegram_id != $bot->userId()) {
            $bot->answerCallbackQuery("❌ Erreur");
            return;
        }

        $ticket->update(['status' => 'closed']);

        $bot->answerCallbackQuery("✅ Ticket clôturé");

        $bot->editMessageText(
            "✅ Ticket #{$ticketId} clôturé avec succès !",
            reply_markup: MainMenuKeyboard::make()
        );
    }
}
