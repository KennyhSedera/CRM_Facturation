<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = '📖 Liste des commandes utiles pour utiliser le bot';

    public function handle()
    {
        $commands = $this->getTelegram()->getCommandBus()->getCommands();

        $text = "<b>📌 Commandes disponibles :</b>\n\nClique sur un bouton pour exécuter la commande.";

        $customIcons = [
            'start'   => '🚀',
            'facture' => '📄',
            'client'  => '👤',
            'clients'  => '👥',
            'stock'   => '📦',
            'recherche' => '🔍',
            'stats'   => '📊',
            'stats_monthly' => '📅',
            'stats_charts' => '📈',
            'help'    => '🆘',
            'getmyid' => '🆔',
        ];

        // Construction du clavier inline par paires
        $keyboard = [];
        $row = [];
        foreach ($commands as $i => $command) {
            $icon  = $customIcons[$command->getName()] ?? '➡️';
            $name  = $command->getName();
            $desc  = $command->getDescription();
            $row[] = [
                'text' => $icon . ' /' . $name . ' - ' . $desc,
                'callback_data' => '/' . $name
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $keyboard[] = [
            [
                'text' => 'Besoin d\'aide ? Support Dargatech',
                'url' => 'https://t.me/dargatech_support'
            ]
        ];

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    public function handleCallback($chatId)
    {
        $commands = $this->getTelegram()->getCommandBus()->getCommands();
        $text = "<b>📌 Commandes disponibles :</b>\n\nClique sur un bouton pour exécuter la commande.";
        $customIcons = [
            'start'   => '🚀',
            'facture' => '📄',
            'client'  => '👤',
            'clients'  => '👥',
            'stock'   => '📦',
            'recherche' => '🔍',
            'stats'   => '📊',
            'stats_monthly' => '📅',
            'stats_charts' => '📈',
            'help'    => '🆘',
            'getmyid' => '🆔',
        ];
        $keyboard = [];
        $row = [];
        foreach ($commands as $i => $command) {
            $icon  = $customIcons[$command->getName()] ?? '➡️';
            $name  = $command->getName();
            $desc  = $command->getDescription();
            $row[] = [
                'text' => $icon . ' /' . $name . ' - ' . $desc,
                'callback_data' => '/' . $name
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }
        $keyboard[] = [
            [
                'text' => 'Besoin d\'aide ? Support Dargatech',
                'url' => 'https://t.me/dargatech_support'
            ]
        ];
        \Telegram\Bot\Laravel\Facades\Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }
}
