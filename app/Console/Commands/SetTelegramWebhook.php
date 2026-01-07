<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook:set';
    protected $description = 'Set Telegram webhook manually';

    public function handle()
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = env('TELEGRAM_WEBHOOK_URL');

        if (!$botToken || !$webhookUrl) {
            $this->error('TELEGRAM_BOT_TOKEN or TELEGRAM_WEBHOOK_URL not set in .env');
            return 1;
        }

        $telegram = new Api($botToken);
        $response = $telegram->setWebhook(['url' => $webhookUrl]);

        $this->info('Webhook set response: ' . json_encode($response));
        return 0;
    }
}
