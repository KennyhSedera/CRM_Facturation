<?php

// app/Telegram/Commands/GetMyIdCommand.php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetMyIdCommand extends Command
{
    protected string $name = 'getmyid';
    protected string $description = 'Obtenir vos informations Telegram';

    public function handle()
    {
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $this->sendMainMenu($chatId);
    }

    public function handleCallback($chatId, $callbackData = null)
    {
        switch ($callbackData) {
            case 'get_my_id_main':
                $this->sendMainMenu($chatId);
                break;
            case 'get_my_id_info':
                $this->sendUserInfo($chatId);
                break;
            case 'get_my_id_help':
                $this->sendHelpInfo($chatId);
                break;
            default:
                $this->sendMainMenu($chatId);
                break;
        }
    }

    private function sendMainMenu($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🆔 Get My ID',
                        'callback_data' => 'getmyid_info'
                    ]
                ],
                [
                    [
                        'text' => '❓ Help',
                        'callback_data' => 'getmyid_help'
                    ]
                ],
                [
                    [
                        'text' => '🔙 Menu Principal',
                        'callback_data' => '/start'
                    ]
                ]
            ]
        ];

        $menuText = "**Get My ID**\n";
        $menuText .= "147,072 utilisateurs mensuel\n\n";
        $menuText .= "Que peut faire ce bot ?\n\n";
        $menuText .= "I will send you your telegram user ID, ";
        $menuText .= "current chat ID and sender ID or chat ID ";
        $menuText .= "of forwarded message.";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $menuText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendUserInfo($chatId)
    {
        // Récupérer les infos de l'utilisateur depuis l'update
        $user = $this->getUpdate()->getCallbackQuery()
            ? $this->getUpdate()->getCallbackQuery()->getFrom()
            : $this->getUpdate()->getMessage()->getFrom();

        $userId = $user->getId();
        $username = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();

        $userInfo = "📋 **Tes informations Telegram :**\n\n";
        $userInfo .= "🆔 **User ID :** `" . $userId . "`\n";
        $userInfo .= "💬 **Chat ID :** `" . $chatId . "`\n";

        if ($username) {
            $userInfo .= "👤 **Username :** @" . $username . "\n";
        }

        if ($firstName) {
            $userInfo .= "📝 **Prénom :** " . $firstName . "\n";
        }

        if ($lastName) {
            $userInfo .= "📝 **Nom :** " . $lastName . "\n";
        }

        $userInfo .= "\n💡 Tu peux copier ces IDs en appuyant dessus !";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🔙 Retour',
                        'callback_data' => 'getmyid_main'
                    ]
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $userInfo,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendHelpInfo($chatId)
    {
        $helpText = "❓ **Aide**\n\n";
        $helpText .= "**Commandes disponibles :**\n";
        $helpText .= "/start - Menu principal\n";
        $helpText .= "/getmyid - Obtenir vos infos\n";
        $helpText .= "/help - Aide générale\n\n";
        $helpText .= "**À propos de ce bot :**\n";
        $helpText .= "Je peux t'envoyer ton ID utilisateur Telegram, ";
        $helpText .= "l'ID du chat actuel et l'ID de l'expéditeur ";
        $helpText .= "ou l'ID du chat des messages transférés.\n\n";
        $helpText .= "📊 **Utilisateurs :** 147,072 mensuel";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🔙 Retour',
                        'callback_data' => 'getmyid_main'
                    ]
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $helpText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    // Méthode pour gérer les messages transférés
    public function handleForwardedMessage($message, $chatId)
    {
        $forwardedFrom = $message['forward_from'] ?? null;
        $forwardedFromChat = $message['forward_from_chat'] ?? null;

        if (!$forwardedFrom && !$forwardedFromChat) {
            return false; // Ce n'est pas un message transféré
        }

        $responseText = "📨 **Message transféré - Informations :**\n\n";

        if ($forwardedFrom) {
            $responseText .= "👤 **De l'utilisateur :**\n";
            $responseText .= "🆔 **User ID :** `" . $forwardedFrom['id'] . "`\n";

            if (isset($forwardedFrom['username'])) {
                $responseText .= "👤 **Username :** @" . $forwardedFrom['username'] . "\n";
            }

            if (isset($forwardedFrom['first_name'])) {
                $responseText .= "📝 **Prénom :** " . $forwardedFrom['first_name'] . "\n";
            }

            if (isset($forwardedFrom['last_name'])) {
                $responseText .= "📝 **Nom :** " . $forwardedFrom['last_name'] . "\n";
            }
        }

        if ($forwardedFromChat) {
            $responseText .= "\n💬 **Du groupe/canal :**\n";
            $responseText .= "🆔 **Chat ID :** `" . $forwardedFromChat['id'] . "`\n";

            if (isset($forwardedFromChat['title'])) {
                $responseText .= "📝 **Nom :** " . $forwardedFromChat['title'] . "\n";
            }

            $responseText .= "📋 **Type :** " . $forwardedFromChat['type'] . "\n";

            if (isset($forwardedFromChat['username'])) {
                $responseText .= "👤 **Username :** @" . $forwardedFromChat['username'] . "\n";
            }
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🔙 Menu GetMyID',
                        'callback_data' => 'getmyid_main'
                    ]
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $responseText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);

        return true;
    }
}
