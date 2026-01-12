<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetTelegramCommands extends Command
{
    protected $signature = 'telegram:commands:set';
    protected $description = 'Définit les commandes du bot Telegram (menu)';

    public function handle()
    {
        $botToken = config('telegram.bots.mybot.token') ?? env('TELEGRAM_BOT_TOKEN');

        if (!$botToken) {
            $this->error('❌ TELEGRAM_BOT_TOKEN non défini');
            return 1;
        }

        $telegram = new Api($botToken);

        $commands = [
            [
                'command' => 'menu',
                'description' => 'Accueil du bot 🚀',
            ],
            [
                'command' => 'clients',
                'description' => 'Gestion des clients 👥',
            ],
            [
                'command' => 'articles',
                'description' => 'Gestion des articles 📦',
            ],
            [
                'command' => 'subscription',
                'description' => 'Mon abonnement 💳',
            ],
            [
                'command' => 'createcompany',
                'description' => 'Créer une entreprise 🏢',
            ],
            [
                'command' => 'profile',
                'description' => 'Mon profil 👤',
            ],
            [
                'command' => 'cancel',
                'description' => 'Annuler une action en cours ❌',
            ],
            [
                'command' => 'help',
                'description' => 'Aide & guide d’utilisation 🆘',
            ],
        ];

        $telegram->setMyCommands([
            'commands' => $commands,
        ]);

        $this->info('✅ Menu des commandes Telegram mis à jour avec succès');
        return 0;
    }
}
