<?php

use App\Console\Commands\SetTelegramWebhook;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\PollTelegramUpdates;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

return [
    SetTelegramWebhook::class,
    PollTelegramUpdates::class,
];

