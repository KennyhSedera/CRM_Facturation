<?php

use App\Telegram\Callbacks\AlertCallback;
use App\Telegram\Handlers\PaymentProofHandler;
use App\Telegram\Handlers\CreateCompanyPaymentHandler;
use SergiX44\Nutgram\Nutgram;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\TicketCommand;
use App\Telegram\Commands\ProfileCommand;
use App\Telegram\Commands\CreateCompanyCommand;
use App\Telegram\Commands\ClientsCommand;
use App\Telegram\Commands\ClientCallbackHandler;
use App\Telegram\Commands\SubscriptionCommand;
use App\Telegram\Commands\SubscriptionCallbackHandler;
use App\Telegram\Callbacks\MenuCallback;
use App\Telegram\Callbacks\TicketCallback;
use App\Telegram\Commands\Admin\AdminPaymentCallbackHandler;
use App\Telegram\Commands\Admin\PendingPaymentsCommand;
use App\Telegram\Commands\ArticleCallbackHandler;
use App\Telegram\Commands\ArticlesCommand;
use App\Telegram\Commands\MenuCommande;
use App\Telegram\Conversations\CreateTicketConversation;
use App\Telegram\Handlers\TextHandler;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

/*
|--------------------------------------------------------------------------
| Commandes
|--------------------------------------------------------------------------
*/

$bot->registerCommand(StartCommand::class);
$bot->registerCommand(HelpCommand::class);
$bot->registerCommand(TicketCommand::class);
$bot->registerCommand(ProfileCommand::class);
$bot->registerCommand(ClientsCommand::class);
$bot->registerCommand(SubscriptionCommand::class);
$bot->registerCommand(PendingPaymentsCommand::class);
$bot->registerCommand(ArticlesCommand::class);
$bot->registerCommand(MenuCommande::class);

// Commande pour créer une entreprise
$bot->onCommand('createcompany', CreateCompanyCommand::class);
$bot->onCommand('quotes', [AlertCallback::class, 'handle']);
$bot->onCommand('calculate', [AlertCallback::class, 'handle']);
$bot->onCommand('settings', [AlertCallback::class, 'handle']);

// Commande pour annuler le processus
$bot->onCommand('cancel', function (Nutgram $bot) {
    $awaitingCompanyData = $bot->getUserData('awaiting_company_data');
    $awaitingClient = $bot->getGlobalData('awaiting_client_data');
    $awaitingSearchQuery = $bot->getGlobalData('awaiting_search_query');
    $editingClientId = $bot->getGlobalData('editing_client_id');
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
    $awaitingReject = $bot->getGlobalData('awaiting_reject_reason');
    $awaitingArticleData = $bot->getGlobalData('awaiting_article_data');
    $editingArticleId = $bot->getGlobalData('editing_article_id');
    $awaitingStockAdd = $bot->getGlobalData('awaiting_stock_add');
    $awaitingStockRemove = $bot->getGlobalData('awaiting_stock_remove');
    $awaitingStockReplace = $bot->getGlobalData('awaiting_stock_replace');
    $awaitingArticleSearch = $bot->getGlobalData('awaiting_article_search');

    if ($awaitingCompanyData) {
        CreateCompanyCommand::cancelProcess($bot);
        $bot->sendMessage(
            text: "❌ <b>Processus de création d'entreprise annulé</b>\n\n" .
            "Toutes vos données ont été supprimées.\n" .
            "Vous pouvez recommencer avec /createcompany",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingCreationProof) {
        $bot->deleteGlobalData('awaiting_creation_payment_proof');
        $bot->deleteGlobalData('creation_payment_plan');
        $bot->deleteGlobalData('creation_payment_method');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Envoi de preuve de création annulé</b>\n\n" .
            "Vous pouvez recommencer avec /createcompany",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingClient) {
        $bot->deleteGlobalData('awaiting_client_data');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Ajout de client annulé</b>\n\n" .
            "Utilisez /clients pour gérer vos clients.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingSearchQuery) {
        $bot->deleteGlobalData('awaiting_search_query');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Recherche annulée</b>\n\n" .
            "Utilisez /clients pour gérer vos clients.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($editingClientId) {
        $bot->deleteGlobalData('editing_client_id');
        $bot->deleteGlobalData('editing_field');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Modification de client annulée</b>\n\n" .
            "Utilisez /clients pour gérer vos clients.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($editingArticleId) {
        $bot->deleteGlobalData('editing_article_id');
        $bot->deleteGlobalData('editing_article_field');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Modification d'article annulée</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingArticleData) {
        $bot->deleteGlobalData('awaiting_article_data');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Ajout d'article annulé</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingStockAdd) {
        $bot->deleteGlobalData('awaiting_stock_add');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Ajout de stock annulé</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingStockRemove) {
        $bot->deleteGlobalData('awaiting_stock_remove');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Retrait de stock annulé</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingStockReplace) {
        $bot->deleteGlobalData('awaiting_stock_replace');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Remplacement de stock annulé</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingProof) {
        $bot->deleteGlobalData('awaiting_payment_proof');
        $bot->deleteGlobalData('payment_plan');
        $bot->deleteGlobalData('payment_action');
        $bot->deleteGlobalData('payment_method');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Envoi de preuve annulé</b>\n\n" .
            "Vous pouvez recommencer le processus de paiement avec /subscription",
            parse_mode: ParseMode::HTML
        );
    } elseif ($awaitingReject) {
        $bot->deleteGlobalData('awaiting_reject_reason');
        $bot->sendMessage("ℹ️ Rejet annulé.");
    } elseif ($awaitingArticleSearch) { // ✅ Ajouté
        $bot->deleteGlobalData('awaiting_article_search');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Recherche d'article annulée</b>\n\n" .
            "Utilisez /articles pour gérer vos articles.",
            parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
        );
    } else {
        $bot->sendMessage("ℹ️ Aucun processus en cours à annuler.");
    }
});

/*
|--------------------------------------------------------------------------
| Callbacks Menu Principal
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('menu_faq', [MenuCallback::class, 'showFaq']);
$bot->onCallbackQueryData('menu_ticket', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    CreateTicketConversation::begin($bot);
});
$bot->onCallbackQueryData('menu_mytickets', [TicketCallback::class, 'listUserTickets']);
$bot->onCallbackQueryData('menu_profile', [ProfileCommand::class, 'handle']);
$bot->onCallbackQueryData('menu_back', [MenuCallback::class, 'backToMenu']);

/*
|--------------------------------------------------------------------------
| Callbacks Création d'Entreprise
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('plan:{plan}', [CreateCompanyCommand::class, 'handlePlanSelection']);

$bot->onCallbackQueryData('create_payment_mobile_{plan}', function (Nutgram $bot, string $plan) {
    CreateCompanyPaymentHandler::processMobilePayment($bot, $plan);
});

$bot->onCallbackQueryData('create_payment_bank_{plan}', function (Nutgram $bot, string $plan) {
    CreateCompanyPaymentHandler::processBankPayment($bot, $plan);
});

$bot->onCallbackQueryData('create_confirm_{plan}_{method}', function (Nutgram $bot, string $plan, string $method) {
    CreateCompanyPaymentHandler::confirmPayment($bot, $plan, $method);
});

/*
|--------------------------------------------------------------------------
| Callbacks Gestion des Abonnements
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('subscription_renew_{plan}', function (Nutgram $bot, string $plan) {
    SubscriptionCallbackHandler::renewSubscription($bot, $plan);
});

$bot->onCallbackQueryData('subscription_upgrade_{plan}', function (Nutgram $bot, string $plan) {
    SubscriptionCallbackHandler::upgradePlan($bot, $plan);
});

$bot->onCallbackQueryData('payment_mobile_{plan}_{action}', function (Nutgram $bot, string $plan, string $action) {
    SubscriptionCallbackHandler::processMobilePayment($bot, $plan, $action);
});

$bot->onCallbackQueryData('payment_bank_{plan}_{action}', function (Nutgram $bot, string $plan, string $action) {
    SubscriptionCallbackHandler::processBankPayment($bot, $plan, $action);
});

$bot->onCallbackQueryData('payment_confirm_{plan}_{action}_{method}', function (Nutgram $bot, string $plan, string $action, string $method) {
    SubscriptionCallbackHandler::confirmPayment($bot, $plan, $action, $method);
});

$bot->onCallbackQueryData('subscription_history', function (Nutgram $bot) {
    SubscriptionCallbackHandler::showPaymentHistory($bot);
});

$bot->onCallbackQueryData('subscription_back', function (Nutgram $bot) {
    SubscriptionCallbackHandler::backToSubscription($bot);
});

/*
|--------------------------------------------------------------------------
| Callbacks Admin - Gestion des Paiements
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('admin_payment_view_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::viewPayment($bot, $id);
});

$bot->onCallbackQueryData('admin_payment_proof_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::showProof($bot, $id);
});

$bot->onCallbackQueryData('admin_payment_approve_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::approvePayment($bot, $id);
});

$bot->onCallbackQueryData('admin_payment_reject_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::rejectPayment($bot, $id);
});

/*
|--------------------------------------------------------------------------
| Callbacks Gestion des Clients
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('client_menu', function (Nutgram $bot) {
    ClientCallbackHandler::showMenu($bot);
});

$bot->onCallbackQueryData('client_list', function (Nutgram $bot) {
    ClientCallbackHandler::listClients($bot);
});

$bot->onCallbackQueryData('client_add', function (Nutgram $bot) {
    ClientCallbackHandler::addClient($bot);
});

$bot->onCallbackQueryData('client_search', function (Nutgram $bot) {
    ClientCallbackHandler::searchClient($bot);
});

$bot->onCallbackQueryData('client_edit_field_{clientId}_{field}', function (Nutgram $bot, int $clientId, string $field) {
    ClientCallbackHandler::editClientField($bot, $clientId, $field);
});

$bot->onCallbackQueryData('client_delete_confirm_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::confirmDelete($bot, $id);
});

$bot->onCallbackQueryData('client_toggle_status_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::toggleClientStatus($bot, $id);
});

$bot->onCallbackQueryData('quote_create_{id}', function (Nutgram $bot, int $id) {
    (new AlertCallback())->handle($bot);
});

$bot->onCallbackQueryData('client_view_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::viewClient($bot, $id);
});

$bot->onCallbackQueryData('client_modify_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::editClient($bot, $id);
});

$bot->onCallbackQueryData('client_delete_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::deleteClient($bot, $id);
});

/*
|--------------------------------------------------------------------------
| Callbacks Tickets
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('ticket_show_{id}', [TicketCallback::class, 'show']);
$bot->onCallbackQueryData('ticket_close_{id}', [TicketCallback::class, 'close']);

/*
|--------------------------------------------------------------------------
| Callbacks Aide
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('help_clients', [HelpCommand::class, 'showClientsHelp']);
$bot->onCallbackQueryData('help_subscription', [HelpCommand::class, 'showSubscriptionHelp']);
$bot->onCallbackQueryData('help_company', [HelpCommand::class, 'showCompanyHelp']);
$bot->onCallbackQueryData('help_faq', [HelpCommand::class, 'showFaq']);
$bot->onCallbackQueryData('help_contact', [HelpCommand::class, 'showContact']);
$bot->onCallbackQueryData('help_guide', [HelpCommand::class, 'showGuide']);
$bot->onCallbackQueryData('help_back', [HelpCommand::class, 'showBack']);
$bot->onCallbackQueryData('help_create_ticket', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    CreateTicketConversation::begin($bot);
});

/*
|--------------------------------------------------------------------------
| Messages texte
|--------------------------------------------------------------------------
*/

$bot->onText('.*', function (Nutgram $bot) {
    $awaitingCompanyData = $bot->getUserData('awaiting_company_data');
    $awaitingClient = $bot->getGlobalData('awaiting_client_data');
    $awaitingSearchQuery = $bot->getGlobalData('awaiting_search_query');
    $editingClientId = $bot->getGlobalData('editing_client_id');
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
    $awaitingReject = $bot->getGlobalData('awaiting_reject_reason');
    $awaitingArticleData = $bot->getGlobalData('awaiting_article_data');
    $editingArticleId = $bot->getGlobalData('editing_article_id');
    $awaitingStockAdd = $bot->getGlobalData('awaiting_stock_add');
    $awaitingStockRemove = $bot->getGlobalData('awaiting_stock_remove');
    $awaitingStockReplace = $bot->getGlobalData('awaiting_stock_replace');
    $awaitingArticleSearch = $bot->getGlobalData('awaiting_article_search');

    if ($awaitingCompanyData) {
        CreateCompanyCommand::handleCompanyData($bot);
        return;
    }

    if ($awaitingClient && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ClientCallbackHandler::processClientData($bot);
        return;
    }

    if ($awaitingSearchQuery && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ClientCallbackHandler::processSearchQuery($bot);
        return;
    }

    if ($editingClientId && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ClientCallbackHandler::processFieldEdit($bot);
        return;
    }

    if ($editingArticleId && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processArticleFieldEdit($bot);
        return;
    }

    if ($awaitingCreationProof && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleTransactionNumber($bot);
        return;
    }

    if ($awaitingProof && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleTransactionNumber($bot);
        return;
    }

    if ($awaitingReject && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        AdminPaymentCallbackHandler::processRejectReason($bot);
        return;
    }

    if ($awaitingArticleData && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processArticleData($bot);
        return;
    }

    if ($awaitingStockAdd && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processStockAdd($bot, $awaitingStockAdd);
        return;
    }

    if ($awaitingStockRemove && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processStockRemove($bot, $awaitingStockRemove);
        return;
    }

    if ($awaitingStockReplace && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processStockReplace($bot, $awaitingStockReplace);
        return;
    }

    if ($awaitingArticleSearch && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ArticleCallbackHandler::processArticleSearch($bot);
        return;
    }
    $textHandler = new TextHandler();
    $textHandler->handle($bot);
});

/*
|--------------------------------------------------------------------------
| Photos et Documents
|--------------------------------------------------------------------------
*/

// Réception de photos
$bot->onPhoto(function (Nutgram $bot) {
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');

    if (($awaitingProof || $awaitingCreationProof) && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handlePhoto($bot);
    }
});

// Réception de documents
$bot->onDocument(function (Nutgram $bot) {
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');

    if (($awaitingProof || $awaitingCreationProof) && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleDocument($bot);
    }
});

/*
|--------------------------------------------------------------------------
| Callbacks Gestion des Articles
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('article_menu', [ArticleCallbackHandler::class, 'showMenu']);
$bot->onCallbackQueryData('article_list', [ArticleCallbackHandler::class, 'listArticles']);
$bot->onCallbackQueryData('article_add', [ArticleCallbackHandler::class, 'addArticle']);

$bot->onCallbackQueryData('article_edit_field_{articleId}_{field}', function (Nutgram $bot, int $articleId, string $field) {
    ArticleCallbackHandler::editArticleField($bot, $articleId, $field);
});

$bot->onCallbackQueryData('article_delete_confirm_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::confirmDelete($bot, $articleId);
});

$bot->onCallbackQueryData('stock_add_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::stockAdd($bot, $articleId);
});

$bot->onCallbackQueryData('stock_remove_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::stockRemove($bot, $articleId);
});

$bot->onCallbackQueryData('stock_replace_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::stockReplace($bot, $articleId);
});

$bot->onCallbackQueryData('article_view_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::viewArticle($bot, $articleId);
});

$bot->onCallbackQueryData('article_modify_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::editArticle($bot, $articleId);
});

$bot->onCallbackQueryData('article_stock_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::adjustStock($bot, $articleId);
});

$bot->onCallbackQueryData('article_delete_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::deleteArticle($bot, $articleId);
});

$bot->onCallbackQueryData('article_history_{articleId}', function (Nutgram $bot, int $articleId) {
    ArticleCallbackHandler::showHistory($bot, $articleId);
});

$bot->onCallbackQueryData('article_search', function (Nutgram $bot) {
    ArticleCallbackHandler::searchArticle($bot);
});

/*
|--------------------------------------------------------------------------
| Gestion des factures
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('invoice_menu', function (Nutgram $bot) {
    (new AlertCallback())->handle($bot);
});

$bot->onCallbackQueryData('menu_new_invoice', function (Nutgram $bot) {
    (new AlertCallback())->handle($bot);
});

$bot->onCallbackQueryData('menu_my_invoices', function (Nutgram $bot) {
    (new AlertCallback())->handle($bot);
});

$bot->onCallbackQueryData('quote_create_{id}', function (Nutgram $bot, int $id) {
    (new AlertCallback())->handle($bot);
});

/*
|--------------------------------------------------------------------------
| Gestion des paramètres
|--------------------------------------------------------------------------
*/

$bot->onCallbackQueryData('menu_settings', function (Nutgram $bot) {
    (new AlertCallback())->handle($bot);
});

/*
|--------------------------------------------------------------------------
| Gestion des erreurs
|--------------------------------------------------------------------------
*/

$bot->onException(function (Nutgram $bot, \Throwable $e) {
    \Log::error('Telegram error: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'user_id' => $bot->user()?->id,
        'chat_id' => $bot->chatId(),
    ]);

    $bot->sendMessage(
        text: '❌ Une erreur est survenue. Veuillez réessayer.\n\n' .
        'Si le problème persiste, contactez le support avec /help',
        parse_mode: ParseMode::HTML
    );
});
