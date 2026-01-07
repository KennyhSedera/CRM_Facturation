<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // <-- Ajouter ceci
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // <-- Ajouter Dispatchable ici

    public $chatId;
    public $message;
    public $keyboard;

    public function __construct($chatId, $message, $keyboard = null)
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->keyboard = $keyboard;
    }

    public function handle()
    {
        app(TelegramService::class)->sendMessage(
            $this->chatId,
            $this->message,
            $this->keyboard
        );
    }
}
