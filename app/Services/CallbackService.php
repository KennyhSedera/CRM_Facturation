<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;

class CallbackService
{
    public function handleCallback(array $callback)
    {
        $chatId = $callback['message']['chat']['id'] ?? null;
        $callbackData = $callback['data'] ?? '';
        $callbackQueryId = $callback['id'] ?? null;

        \Log::info('Callback reçu', [
            'chat_id' => $chatId,
            'callback_data' => $callbackData
        ]);

        if (!$chatId || !$callbackData || !$callbackQueryId) {
            return;
        }

        // Répondre immédiatement au callback pour enlever le "loading"
        Telegram::answerCallbackQuery([
            'callback_query_id' => $callbackQueryId,
        ]);

        // Gérer les callbacks spéciaux
        if (str_starts_with($callbackData, 'getmyid_')) {
            $this->handleGetMyIdCallback($callbackData, $chatId);
            return;
        }

        if (str_starts_with($callbackData, 'search_')) {
            $this->handleSearchCallback($callbackData, $chatId);
            return;
        }

        // Gérer les callbacks de commandes principales
        $commandName = ltrim($callbackData, '/');

        switch ($commandName) {
            case 'facture':
                $this->handleFacture($chatId);
                break;

            case 'facture_all':
                $this->handleFactureAll($chatId);
                break;

            case 'facture_pending':
                $this->handleFacturePending($chatId);
                break;

            case 'facture_paid':
                $this->handleFacturePaid($chatId);
                break;

            case 'facture_unpaid':
                $this->handleFactureUnpaid($chatId);
                break;

            case 'stats':
                $this->handleStats($chatId);
                break;

            case 'stock':
                $this->handleStock($chatId);
                break;

            case 'recherche':
                $this->handleRecherche($chatId);
                break;

            case 'getmyid':
                $this->handleGetMyId($chatId);
                break;

            case 'start':
                $this->handleStart($chatId);
                break;

            case 'stats_monthly':
                $this->handleStatsMonthly($chatId);
                break;

            case 'stats_charts':
                $this->handleStatsCharts($chatId);
                break;


            default:
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "❌ Commande inconnue: " . $callbackData,
                ]);
                break;
        }
    }

    private function handleFacture($chatId)
    {
        // Ici vous pouvez ajouter la logique complexe
        // Comme récupérer les données de la base de données, etc.

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📋 Toutes les factures', 'callback_data' => 'facture_all'],
                    ['text' => '⏳ En attente', 'callback_data' => 'facture_pending']
                ],
                [
                    ['text' => '✅ Payées', 'callback_data' => 'facture_paid'],
                    ['text' => '❌ Impayées', 'callback_data' => 'facture_unpaid']
                ],
                [
                    ['text' => '🔙 Retour au menu', 'callback_data' => '/start']
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📄 **Gestion des Factures**\n\nChoisissez une option :",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function handleStats($chatId)
    {
        // Exemple de statistiques fictives
        $statsText = "📊 **Tableau de Bord - Statistiques**\n\n" .
                    "💰 **Chiffre d'affaires :**\n" .
                    "• Total facturé : 15,250€\n" .
                    "• Factures payées : 12,840€\n" .
                    "• En attente : 2,410€\n\n" .
                    "📋 **Factures :**\n" .
                    "• Total : 45\n" .
                    "• Payées : 38\n" .
                    "• En attente : 7\n\n" .
                    "📅 **Ce mois-ci :**\n" .
                    "• Nouvelles factures : 12\n" .
                    "• Montant : 4,320€";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📈 Détails mensuels', 'callback_data' => 'stats_monthly'],
                    ['text' => '📊 Graphiques', 'callback_data' => 'stats_charts']
                ],
                [
                    ['text' => '🔙 Retour au menu', 'callback_data' => '/start']
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $statsText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function handleStock($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📦 Tout le stock', 'callback_data' => 'stock_all'],
                    ['text' => '⚠️ Stock faible', 'callback_data' => 'stock_low']
                ],
                [
                    ['text' => '➕ Ajouter produit', 'callback_data' => 'stock_add'],
                    ['text' => '✏️ Modifier stock', 'callback_data' => 'stock_edit']
                ],
                [
                    ['text' => '🔙 Retour au menu', 'callback_data' => '/start']
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📦 **Gestion du Stock**\n\nChoisissez une option :",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function handleRecherche($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '👥 Rechercher un client', 'callback_data' => 'search_client'],
                    ['text' => '📦 Rechercher un produit', 'callback_data' => 'search_product']
                ],
                [
                    ['text' => '🔍 Recherche globale', 'callback_data' => 'search_global']
                ],
                [
                    ['text' => '🔙 Retour au menu', 'callback_data' => '/start']
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🔍 **Fonction de Recherche**\n\nQue souhaitez-vous rechercher ?",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function handleGetMyId($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🆔 **Votre ID Telegram**\n\nVotre Chat ID est : `{$chatId}`\n\n💡 Vous pouvez utiliser cet ID pour configurer des notifications personnalisées.",
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleStart($chatId)
    {
        $startCommand = new \App\Telegram\Commands\StartCommand();
        $startCommand->handleCallback($chatId);
    }

    private function handleGetMyIdCallback($callbackData, $chatId)
    {
        // Logique spécifique pour GetMyID avec callbacks
        $getMyIdCommand = new \App\Telegram\Commands\GetMyIdCommand();
        $update = Telegram::getWebhookUpdate();
        $getMyIdCommand->setUpdate($update);
        $getMyIdCommand->handleCallback($chatId, $callbackData);
    }

    private function handleSearchCallback($callbackData, $chatId)
    {
        switch ($callbackData) {
            case 'search_client':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '👥 **Recherche de client**' . "\n\n" .
                             'Tapez le nom, email ou téléphone du client à rechercher :',
                    'parse_mode' => 'Markdown'
                ]);
                break;

            case 'search_product':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📦 **Recherche de produit**' . "\n\n" .
                             'Tapez le nom ou la référence du produit à rechercher :',
                    'parse_mode' => 'Markdown'
                ]);
                break;

            case 'search_global':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '🔍 **Recherche globale**' . "\n\n" .
                             'Tapez votre terme de recherche (clients et produits) :',
                    'parse_mode' => 'Markdown'
                ]);
                break;
        }
    }

    private function handleFactureAll($chatId)
    {
        $command = new \App\Telegram\Commands\FactureAllCommand();
        $command->handleCallback($chatId);
    }

    private function handleFacturePending($chatId)
    {
        $command = new \App\Telegram\Commands\FacturePendingCommand();
        $command->handleCallback($chatId);
    }

    private function handleFacturePaid($chatId)
    {
        $command = new \App\Telegram\Commands\FacturePaidCommand();
        $command->handleCallback($chatId);
    }

    private function handleFactureUnpaid($chatId)
    {
        $command = new \App\Telegram\Commands\FactureUnpaidCommand();
        $command->handleCallback($chatId);
    }

    private function handleStatsMonthly($chatId)
    {
        $command = new \App\Telegram\Commands\StatsMonthlyCommand();
        $command->handleCallback($chatId);
    }

    private function handleStatsCharts($chatId)
    {
        $command = new \App\Telegram\Commands\StatsChartsCommand();
        $command->handleCallback($chatId);
    }

}
