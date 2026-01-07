<?php

return [
    'token' => env('TELEGRAM_TOKEN'),

    'safe_mode' => env('APP_ENV', 'local') === 'production',

    'config' => [
        'client' => [
            'timeout' => 10,
            'http_version' => '1.1',
            'verify' => false,
        ],
    ],

    'routes' => true,
    'mixins' => false,
    'namespace' => app_path('Telegram'),

    'log_channel' => env('TELEGRAM_LOG_CHANNEL', 'stack'),
];
