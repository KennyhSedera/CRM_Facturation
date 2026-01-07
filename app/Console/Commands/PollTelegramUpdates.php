<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class PollTelegramUpdates extends Command
{
    protected $signature = 'telegram:polling';
    protected $description = 'Poll updates from Telegram Bot API';

    public function handle()
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $updates = $telegram->getUpdates();

        foreach ($updates as $update) {
            if (isset($update['message']['text'])) {
                $chatId = $update['message']['chat']['id'];
                $text = $update['message']['text'];

                if ($text === '/start') {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Réponse via polling ! ✅',
                    ]);
                }
            }
        }

        return Command::SUCCESS;
    }
}
