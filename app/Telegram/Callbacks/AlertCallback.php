<?php

namespace App\Telegram\Callbacks;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;



class AlertCallback
{
    public static function handle($bot)
    {
        $bot->sendMessage(text: "⚠️ Fonctionnalité en développement", parse_mode: 'HTML');
    }
}
