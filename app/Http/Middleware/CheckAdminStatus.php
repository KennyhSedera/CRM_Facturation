<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;

class CheckAdminStatus
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));
        $adminIds = array_map('trim', $adminIds);

        if (!in_array($bot->userId(), $adminIds)) {
            // Répondre au callback si c'est un callback query
            if ($bot->callbackQuery()) {
                try {
                    $bot->answerCallbackQuery(
                        text: "❌ Réservé aux administrateurs",
                        show_alert: true
                    );
                } catch (\Exception $e) {
                    \Log::debug('Callback already answered');
                }
            }

            $bot->sendMessage("❌ Commande réservée aux administrateurs.");
            return;
        }

        $next($bot);
    }
}
