<?php

// namespace App\Services;
// use Telegram\Bot\Api;

// class TelegramService
// {
//     protected $telegram;

//     public function __construct()
//     {
//         $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
//     }

//     public function sendMessage($chatId, $message)
//     {
//         $this->telegram->sendMessage([
//             'chat_id' => $chatId,
//             'text' => $message,
//         ]);
//     }
// }

// namespace App\Services;

// class TelegramService
// {
//     private string $token;
//     private string $apiUrl;

//     public function __construct()
//     {
//         $this->token = env('TELEGRAM_BOT_TOKEN');
//         $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
//     }

//     public function sendMessage($chatId, $message)
//     {
//         $url = $this->apiUrl . 'sendMessage';
//         $data = [
//             'chat_id' => $chatId,
//             'text' => $message,
//         ];

//         file_get_contents($url . '?' . http_build_query($data));
//     }
// }


namespace App\Services;

class TelegramService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token = config('telegram.bots.mybot.token') ?? env('TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function sendMessage(int|string $chatId, string $message, ?array $keyboard = null): void
    {
        $url = $this->apiUrl . 'sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode([
                'inline_keyboard' => $keyboard
            ]);
        }

        file_get_contents($url . '?' . http_build_query($data));
    }

    public function sendStartMessage(array $data): void
    {
        $chatId = $data['message']['chat']['id'] ?? null;
        if (!$chatId) return;

        $keyboard = [
            [
                ['text' => '📄 Voir les devis', 'callback_data' => '/facture'],
                ['text' => '📊 Dashboard', 'callback_data' => '/stats'],
            ],
            [
                ['text' => '📦 Stock', 'callback_data' => '/stock'],
                ['text' => '🔍 Recherche', 'callback_data' => '/recherche'],
            ]
        ];

        $this->sendMessage(
            $chatId,
            "👋 Bienvenue dans le CRM Dargatech ! Que souhaites-tu faire ?",
            $keyboard
        );
    }
}

