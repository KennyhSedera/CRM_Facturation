<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class WebAppKeyboard
{

    public static function buttonCreateCompany(Nutgram $bot): InlineKeyboardMarkup
    {

        $telegramUser = $bot->user();
        $webAppUrl = route('webapp.form.company', ['user_id' => $telegramUser->id]);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📝 Créer mon entreprise',
                    web_app: new WebAppInfo($webAppUrl)
                )
            );

        return $keyboard;
    }

    public static function buttonCreateClient(Nutgram $bot): InlineKeyboardMarkup
    {

        $telegramUser = $bot->user();
        $webAppUrl = route('webapp.form.client', ['user_id' => $telegramUser->id]);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📝 Créer un client',
                    web_app: new WebAppInfo($webAppUrl)
                )
            );

        return $keyboard;
    }


}
