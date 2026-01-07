<?php

namespace App\Telegram\Callbacks;

use SergiX44\Nutgram\Nutgram;
use App\Telegram\Keyboards\MainMenuKeyboard;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MenuCallback
{
    /**
     * Afficher le menu FAQ
     */
    public static function showFaq(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "❓ <b>Questions Fréquentes - FacturePro</b>\n\n"
            . "• Comment créer une facture ?\n"
            . "• Gestion des clients\n"
            . "• Génération de PDF\n"
            . "• Statistiques et rapports\n"
            . "• Support et assistance";

        $bot->editMessageText(
            text: $message,
            reply_markup: self::getFaqKeyboard(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher les informations sur la création de facture
     */
    public static function showInvoiceInfo(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "📄 <b>Créer une Facture</b>\n\n"
            . "Pour créer une facture :\n"
            . "1️⃣ Utilisez /nouvelle_facture\n"
            . "2️⃣ Remplissez les informations client\n"
            . "3️⃣ Ajoutez les articles/services\n"
            . "4️⃣ Validez et téléchargez le PDF\n\n"
            . "💡 Vous pouvez également gérer vos factures via le menu principal.";

        $bot->editMessageText(
            text: $message,
            reply_markup: self::getBackToFaqKeyboard(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher les informations sur les clients
     */
    public static function showClientsInfo(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "👥 <b>Gestion des Clients</b>\n\n"
            . "• Utilisez /mes_clients pour voir la liste\n"
            . "• Ajoutez un client avec /nouveau_client\n"
            . "• Modifiez les informations facilement\n"
            . "• Consultez l'historique des factures par client";

        $bot->editMessageText(
            text: $message,
            reply_markup: self::getBackToFaqKeyboard(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher les informations sur les statistiques
     */
    public static function showStatsInfo(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "📊 <b>Statistiques et Rapports</b>\n\n"
            . "• Chiffre d'affaires mensuel\n"
            . "• Nombre de factures émises\n"
            . "• Factures payées/en attente\n"
            . "• Graphiques et analyses\n\n"
            . "Utilisez /statistiques pour accéder au tableau de bord.";

        $bot->editMessageText(
            text: $message,
            reply_markup: self::getBackToFaqKeyboard(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher les informations de support
     */
    public static function showSupportInfo(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "🆘 <b>Support et Assistance</b>\n\n"
            . "📧 Email : support@facturepro.com\n"
            . "⏰ Horaires : Lun-Ven 9h-18h\n"
            . "⚡ Réponse moyenne : 2-4 heures\n\n"
            . "Utilisez /aide pour obtenir de l'aide immédiate.";

        $bot->editMessageText(
            text: $message,
            reply_markup: self::getBackToFaqKeyboard(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Retour au menu principal
     */
    public static function backToMenu(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "🏠 <b>FacturePro - Menu Principal</b>\n\n"
            . "Bienvenue sur votre assistant de facturation professionnel !\n\n"
            . "📄 <b>Gestion de Factures</b>\n"
            . "Créez, consultez et gérez vos factures en quelques clics.\n\n"
            . "👥 <b>Base Clients</b>\n"
            . "Organisez vos contacts et historiques clients.\n\n"
            . "📊 <b>Suivi d'Activité</b>\n"
            . "Analysez votre chiffre d'affaires et performances.\n\n"
            . "💡 <i>Sélectionnez une option ci-dessous pour commencer</i>";

        $bot->editMessageText(
            text: $message,
            reply_markup: MainMenuKeyboard::make(),
            parse_mode: 'HTML'
        );
    }

    /**
     * Clavier FAQ principal
     */
    private static function getFaqKeyboard(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('📄 Créer une facture', callback_data: 'faq_invoice'))
            ->addRow(InlineKeyboardButton::make('👥 Gestion clients', callback_data: 'faq_clients'))
            ->addRow(InlineKeyboardButton::make('📊 Statistiques', callback_data: 'faq_stats'))
            ->addRow(InlineKeyboardButton::make('🆘 Support', callback_data: 'faq_support'))
            ->addRow(InlineKeyboardButton::make('⬅️ Retour au menu', callback_data: 'menu_back'));
    }

    /**
     * Clavier de retour à la FAQ
     */
    private static function getBackToFaqKeyboard(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('⬅️ Retour à la FAQ', callback_data: 'menu_faq'))
            ->addRow(InlineKeyboardButton::make('🏠 Menu principal', callback_data: 'menu_back'));
    }
}
