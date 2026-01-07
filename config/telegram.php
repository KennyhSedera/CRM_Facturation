<?php

return [

    'bots' => [
        'mybot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
            'allowed_updates' => null,
        ],
    ],

    'admin_chat_id' => env('TELEGRAM_ADMIN_IDS'),

    'default' => 'mybot',

    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    'http_client_handler' => null,

    'base_bot_url' => null,

    'resolve_command_dependencies' => true,

    'commands' => [
        App\Telegram\Commands\StartCommand::class,
        App\Telegram\Commands\HelpCommand::class,
        App\Telegram\Commands\FactureCommand::class,
        App\Telegram\Commands\FactureAllCommand::class,
        App\Telegram\Commands\ClientsCommand::class,
        App\Telegram\Commands\ClientCommand::class,
        App\Telegram\Commands\FacturePendingCommand::class,
        App\Telegram\Commands\FacturePaidCommand::class,
        App\Telegram\Commands\FactureUnpaidCommand::class,
        App\Telegram\Commands\StatsCommand::class,
        App\Telegram\Commands\StatsMonthlyCommand::class,
        App\Telegram\Commands\StatsChartsCommand::class,
        App\Telegram\Commands\StockCommand::class,
        App\Telegram\Commands\RechercheCommand::class,
        App\Telegram\Commands\GetMyIdCommand::class,
    ],

    'menu_commands' => [
        App\Telegram\Commands\StartCommand::class,
        App\Telegram\Commands\HelpCommand::class,
        App\Telegram\Commands\ProfileCommand::class,
        App\Telegram\Commands\ClientsCommand::class,
        App\Telegram\Commands\TicketCommand::class,
        App\Telegram\Commands\SubscriptionCommand::class,

        // Admin
        App\Telegram\Commands\Admin\PendingPaymentsCommand::class,
    ],

    'command_groups' => [],

    'shared_commands' => [],
];
