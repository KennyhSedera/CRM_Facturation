<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class MainMenuKeyboard
{
    public static function make(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📄 Nouvelle Facture', callback_data: 'menu_new_invoice'),
                InlineKeyboardButton::make('📋 Mes Factures', callback_data: 'menu_my_invoices')
            )
            ->addRow(
                InlineKeyboardButton::make('👥 Nouvelle Client', callback_data: 'client_add'),
                InlineKeyboardButton::make('👥 Mes Clients', callback_data: 'client_menu'),
            )
            ->addRow(
                InlineKeyboardButton::make('⚙️ Paramètres', callback_data: 'menu_settings'),
            );
    }


    public static function initialize(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🆓 Gratuitement (0 FCFA)', callback_data: 'free_option'),
                InlineKeyboardButton::make('⭐ Premium (9.900 FCFA)', callback_data: 'premium_option')
            )
            ->addRow(
                InlineKeyboardButton::make('🏢 Entreprise (14.900 FCFA)', callback_data: 'entreprise_option'),
                InlineKeyboardButton::make('⏭️ Plus tard', callback_data: 'cancel_option'),
            );
    }
}
