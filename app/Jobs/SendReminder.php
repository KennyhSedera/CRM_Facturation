<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(TelegramService $telegramService): void
    {
        $message = "🔔 Bonjour {$this->client->name}, pensez à finaliser votre commande ou devis.";
        $chatId = $this->client->chatId;
        $telegramService->sendMessage($chatId,$message);
    }
}
