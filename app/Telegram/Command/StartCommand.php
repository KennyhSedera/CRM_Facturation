<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Démarrer le bot';

    public function handle()
    {
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $this->sendMainMenu($chatId);
    }

    // Peut être appelée depuis un callback handler
    public function handleCallback($chatId)
    {
        $this->sendMainMenu($chatId);
    }

    private function sendMainMenu($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📄 Voir les devis', 'callback_data' => '/facture'],
                    ['text' => '📊 Dashboard', 'callback_data' => '/stats']
                ],
                [
                    ['text' => '📦 Stock', 'callback_data' => '/stock'],
                    ['text' => '🔍 Recherche', 'callback_data' => '/recherche']
                ],
                [
                    ['text' => '🆔 Get My ID', 'callback_data' => '/getmyid']
                ]
            ]
        ];

        $welcomeText = "👋 *Bienvenue dans le CRM Dargatech !*\n\n" .
                       "Choisissez une option dans le menu ci-dessous ou tapez /help pour voir toutes les commandes.";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $welcomeText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }
}
