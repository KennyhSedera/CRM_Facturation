<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use App\Models\Ticket;

class TicketKeyboard
{
    public static function list(array $tickets): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        foreach ($tickets as $ticket) {
            $emoji = self::getStatusEmoji($ticket->status);
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$emoji} Ticket #{$ticket->id} - {$ticket->subject}",
                    callback_data: "ticket_show_{$ticket->id}"
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('⬅️ Retour', callback_data: 'menu_back')
        );

        return $keyboard;
    }

    public static function details(Ticket $ticket): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        if ($ticket->status === 'open') {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '✅ Clôturer',
                    callback_data: "ticket_close_{$ticket->id}"
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('⬅️ Mes tickets', callback_data: 'menu_mytickets')
        );

        return $keyboard;
    }

    private static function getStatusEmoji(string $status): string
    {
        return match ($status) {
            'open' => '🆕',
            'in_progress' => '⚙️',
            'closed' => '✅',
            default => '❓'
        };
    }
}
