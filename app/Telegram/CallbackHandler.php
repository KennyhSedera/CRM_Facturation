<?php

namespace App\Telegram;

use Telegram\Bot\Laravel\Facades\Telegram;

class CallbackHandler
{
    public static function handle($callbackQuery)
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $callbackData = $callbackQuery->getData();

        // Répondre au callback pour enlever le "loading" sur le bouton
        Telegram::answerCallbackQuery([
            'callback_query_id' => $callbackQuery->getId(),
            'text' => 'Chargement...'
        ]);

        switch ($callbackData) {
            case '/facture':
                $factureCommand = new \App\Telegram\Commands\FactureCommand();
                $factureCommand->handleCallback($chatId);
                break;

            case '/clients':
                $clientsCommand = new \App\Telegram\Commands\ClientsCommand();
                $clientsCommand->handleCallback($chatId);
                break;

            case '/client':
                $clientCommand = new \App\Telegram\Commands\ClientCommand();
                $clientCommand->handleCallback($chatId);
                break;

            case '/stats':
                $statsCommand = new \App\Telegram\Commands\StatsCommand();
                $statsCommand->handleCallback($chatId);
                break;

            case '/stats_monthly':
                $statsCommand = new \App\Telegram\Commands\StatsMonthlyCommand();
                $statsCommand->handleCallback($chatId);
                break;

            case '/stats_charts':
                $statsChartsCommand = new \App\Telegram\Commands\StatsChartsCommand();
                $statsChartsCommand->handleCallback($chatId);
                break;

            case '/stock':
                $stockCommand = new \App\Telegram\Commands\StockCommand();
                $stockCommand->handleCallback($chatId);
                break;

            case '/recherche':
                $rechercheCommand = new \App\Telegram\Commands\RechercheCommand();
                $rechercheCommand->handleCallback($chatId);
                break;

            case '/getmyid':
                $getMyIdCommand = new \App\Telegram\Commands\GetMyIdCommand();
                $getMyIdCommand->handleCallback($chatId);
                break;

            default:
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Commande inconnue: " . $callbackData,
                ]);
        }
    }
}
