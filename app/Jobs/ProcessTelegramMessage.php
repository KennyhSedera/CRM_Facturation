<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $message;
    public string $chatId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, string $chatId)
    {
        $this->message = $message;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        if (str_contains($this->message, '/devis')) {
            $telegramService->sendMessage($this->chatId, "Voici la liste des devis...");
        } elseif (str_contains($this->message, '/stock')) {
            $telegramService->sendMessage($this->chatId, "Stock actuel en cours de traitement...");
        } else {
            $telegramService->sendMessage($this->chatId, "Commande non reconnue.");
        }
    }

}
