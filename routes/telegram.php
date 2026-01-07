<?php

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
use App\Telegram\Conversations\CreateTicketConversation;
use App\Telegram\Handlers\TextHandler;
// use App\Telegram\Middleware\AdminMiddleware;

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

// Commande pour créer une entreprise
$bot->onCommand('createcompany', CreateCompanyCommand::class);

// Commande pour annuler le processus
$bot->onCommand('cancel', function (Nutgram $bot) {
    $awaitingCompanyData = $bot->getUserData('awaiting_company_data');
    $awaitingClient = $bot->getGlobalData('awaiting_client_data');
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
    $awaitingReject = $bot->getGlobalData('awaiting_reject_reason');

    if ($awaitingCompanyData) {
        CreateCompanyCommand::cancelProcess($bot);
        $bot->sendMessage(
            text: "❌ <b>Processus de création d'entreprise annulé</b>\n\n" .
            "Toutes vos données ont été supprimées.\n" .
            "Vous pouvez recommencer avec /createcompany",
            parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
        );
    } elseif ($awaitingCreationProof) {
        $bot->deleteGlobalData('awaiting_creation_payment_proof');
        $bot->deleteGlobalData('creation_payment_plan');
        $bot->deleteGlobalData('creation_payment_method');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Envoi de preuve de création annulé</b>\n\n" .
            "Vous pouvez recommencer avec /createcompany",
            parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
        );
    } elseif ($awaitingClient) {
        $bot->deleteGlobalData('awaiting_client_data');
        $bot->deleteGlobalData('user_telegram_id');
        $bot->sendMessage(
            text: "❌ <b>Ajout de client annulé</b>\n\n" .
            "Utilisez /clients pour gérer vos clients.",
            parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
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
            parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
        );
    } elseif ($awaitingReject) {
        $bot->deleteGlobalData('awaiting_reject_reason');
        $bot->sendMessage("ℹ️ Rejet annulé.");
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

// Paiement Mobile Money pour création
$bot->onCallbackQueryData('create_payment_mobile_{plan}', function (Nutgram $bot, string $plan) {
    CreateCompanyPaymentHandler::processMobilePayment($bot, $plan);
});

// Paiement Virement bancaire pour création
$bot->onCallbackQueryData('create_payment_bank_{plan}', function (Nutgram $bot, string $plan) {
    CreateCompanyPaymentHandler::processBankPayment($bot, $plan);
});

// Confirmation du paiement pour création
$bot->onCallbackQueryData('create_confirm_{plan}_{method}', function (Nutgram $bot, string $plan, string $method) {
    CreateCompanyPaymentHandler::confirmPayment($bot, $plan, $method);
});

/*
|--------------------------------------------------------------------------
| Callbacks Gestion des Abonnements
|--------------------------------------------------------------------------
*/

// Renouvellement
$bot->onCallbackQueryData('subscription_renew_{plan}', function (Nutgram $bot, string $plan) {
    SubscriptionCallbackHandler::renewSubscription($bot, $plan);
});

// Upgrade de plan
$bot->onCallbackQueryData('subscription_upgrade_{plan}', function (Nutgram $bot, string $plan) {
    SubscriptionCallbackHandler::upgradePlan($bot, $plan);
});

// Paiement Mobile Money
$bot->onCallbackQueryData('payment_mobile_{plan}_{action}', function (Nutgram $bot, string $plan, string $action) {
    SubscriptionCallbackHandler::processMobilePayment($bot, $plan, $action);
});

// Paiement Virement bancaire
$bot->onCallbackQueryData('payment_bank_{plan}_{action}', function (Nutgram $bot, string $plan, string $action) {
    SubscriptionCallbackHandler::processBankPayment($bot, $plan, $action);
});

// Confirmation de paiement avec méthode
$bot->onCallbackQueryData('payment_confirm_{plan}_{action}_{method}', function (Nutgram $bot, string $plan, string $action, string $method) {
    SubscriptionCallbackHandler::confirmPayment($bot, $plan, $action, $method);
});

// Historique des paiements
$bot->onCallbackQueryData('subscription_history', function (Nutgram $bot) {
    SubscriptionCallbackHandler::showPaymentHistory($bot);
});

// Retour au menu abonnement
$bot->onCallbackQueryData('subscription_back', function (Nutgram $bot) {
    SubscriptionCallbackHandler::backToSubscription($bot);
});

/*
|--------------------------------------------------------------------------
| Callbacks Admin - Gestion des Paiements
|--------------------------------------------------------------------------
*/

// Voir les détails d'un paiement
$bot->onCallbackQueryData('admin_payment_view_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::viewPayment($bot, $id);
});

// Voir la preuve de paiement
$bot->onCallbackQueryData('admin_payment_proof_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::showProof($bot, $id);
});

// Approuver un paiement
$bot->onCallbackQueryData('admin_payment_approve_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::approvePayment($bot, $id);
});

// Rejeter un paiement
$bot->onCallbackQueryData('admin_payment_reject_{id}', function (Nutgram $bot, int $id) {
    AdminPaymentCallbackHandler::rejectPayment($bot, $id);
});

/*
|--------------------------------------------------------------------------
| Callbacks Gestion des Clients
|--------------------------------------------------------------------------
*/

// Menu clients
$bot->onCallbackQueryData('client_menu', function (Nutgram $bot) {
    ClientCallbackHandler::showMenu($bot);
});

// Lister les clients
$bot->onCallbackQueryData('client_list', function (Nutgram $bot) {
    ClientCallbackHandler::listClients($bot);
});

// Ajouter un client
$bot->onCallbackQueryData('client_add', function (Nutgram $bot) {
    ClientCallbackHandler::addClient($bot);
});

// Voir un client spécifique
$bot->onCallbackQueryData('client_view_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::viewClient($bot, $id);
});

// Éditer un client
$bot->onCallbackQueryData('client_edit_{id}', function (Nutgram $bot, int $id) {
    $bot->answerCallbackQuery("⚠️ Fonctionnalité en développement", show_alert: true);
});

// Supprimer un client
$bot->onCallbackQueryData('client_delete_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::deleteClient($bot, $id);
});

// Confirmer la suppression
$bot->onCallbackQueryData('client_delete_confirm_{id}', function (Nutgram $bot, int $id) {
    ClientCallbackHandler::confirmDelete($bot, $id);
});

// Rechercher un client
$bot->onCallbackQueryData('client_search', function (Nutgram $bot) {
    $bot->answerCallbackQuery("⚠️ Fonctionnalité en développement", show_alert: true);
});

// Créer un devis pour un client
$bot->onCallbackQueryData('quote_create_{id}', function (Nutgram $bot, int $id) {
    $bot->answerCallbackQuery("⚠️ Fonctionnalité en développement", show_alert: true);
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
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
    $awaitingReject = $bot->getGlobalData('awaiting_reject_reason');

    // Si l'utilisateur envoie les infos d'entreprise (format message unique)
    if ($awaitingCompanyData) {
        CreateCompanyCommand::handleCompanyData($bot);
        return;
    }

    // Si l'utilisateur est en train d'ajouter un client
    if ($awaitingClient && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        ClientCallbackHandler::processClientData($bot);
        return;
    }

    // Si l'utilisateur envoie une preuve pour création d'entreprise
    if ($awaitingCreationProof && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleTransactionNumber($bot);
        return;
    }

    // Si l'utilisateur envoie un numéro de transaction pour renouvellement
    if ($awaitingProof && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleTransactionNumber($bot);
        return;
    }

    // Si admin donne une raison de rejet
    if ($awaitingReject && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        AdminPaymentCallbackHandler::processRejectReason($bot);
        return;
    }

    // Sinon, utiliser le handler par défaut
    $textHandler = new TextHandler();
    $textHandler->handle($bot);
});

/*
|--------------------------------------------------------------------------
| Photos et Documents (Preuves de paiement)
|--------------------------------------------------------------------------
*/

// Réception de photos (preuves de paiement)
$bot->onPhoto(function (Nutgram $bot) {
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');

    if (($awaitingProof || $awaitingCreationProof) && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handlePhoto($bot);
    }
});

// Réception de documents (PDF, images)
$bot->onDocument(function (Nutgram $bot) {
    $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
    $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');

    if (($awaitingProof || $awaitingCreationProof) && $bot->getGlobalData('user_telegram_id') == $bot->user()->id) {
        PaymentProofHandler::handleDocument($bot);
    }
});

/*
|--------------------------------------------------------------------------
| Protection des Commandes Admin avec Middleware (Optionnel)
|--------------------------------------------------------------------------
*/

// Décommenter si vous voulez activer la protection par middleware

// // Méthode 1 : Protéger une commande spécifique
// $bot->registerCommand(PendingPaymentsCommand::class)
//     ->middleware(AdminMiddleware::class);

// // Méthode 2 : Groupe de commandes admin
// $bot->group(function (Nutgram $bot) {
//     $bot->registerCommand(PendingPaymentsCommand::class);
//     // Ajoutez d'autres commandes admin ici
// })->middleware(AdminMiddleware::class);

// // Méthode 3 : Protection manuelle dans les callbacks
// $bot->onCallbackQueryData('admin_payment_view_{id}', function (Nutgram $bot, int $id) {
//     if (!AdminMiddleware::isAdmin($bot)) {
//         $bot->answerCallbackQuery("❌ Accès refusé", show_alert: true);
//         return;
//     }
//     AdminPaymentCallbackHandler::viewPayment($bot, $id);
// });

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
        parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
    );
});
