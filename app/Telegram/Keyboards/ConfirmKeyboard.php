<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class ConfirmKeyboard
{
    /**
     * Clavier de confirmation simple (Oui/Non)
     */
    public static function yesNo(string $confirmCallback = 'confirm_yes', string $cancelCallback = 'confirm_no'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui', callback_data: $confirmCallback),
                InlineKeyboardButton::make('❌ Non', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier de confirmation avec bouton retour
     */
    public static function yesNoBack(string $confirmCallback = 'confirm_yes', string $cancelCallback = 'confirm_no', string $backCallback = 'menu_back'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui', callback_data: $confirmCallback),
                InlineKeyboardButton::make('❌ Non', callback_data: $cancelCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour', callback_data: $backCallback)
            );
    }

    /**
     * Clavier de confirmation pour ticket
     */
    public static function ticketConfirm(int $ticketId): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Confirmer', callback_data: "ticket_confirm_{$ticketId}"),
                InlineKeyboardButton::make('✏️ Modifier', callback_data: "ticket_edit_{$ticketId}")
            )
            ->addRow(
                InlineKeyboardButton::make('❌ Annuler', callback_data: "ticket_cancel_{$ticketId}")
            );
    }

    /**
     * Clavier de confirmation de suppression
     */
    public static function delete(string $entityType, int $entityId): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🗑️ Oui, supprimer', callback_data: "{$entityType}_delete_confirm_{$entityId}"),
                InlineKeyboardButton::make('❌ Annuler', callback_data: "{$entityType}_delete_cancel_{$entityId}")
            );
    }

    /**
     * Clavier de confirmation avec avertissement
     */
    public static function warningConfirm(string $confirmCallback, string $cancelCallback): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('⚠️ Oui, je confirme', callback_data: $confirmCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('❌ Non, annuler', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier Continue / Cancel
     */
    public static function continueCancel(string $continueCallback = 'continue', string $cancelCallback = 'cancel'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('▶️ Continuer', callback_data: $continueCallback),
                InlineKeyboardButton::make('❌ Annuler', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier Ok / Cancel
     */
    public static function okCancel(string $okCallback = 'ok', string $cancelCallback = 'cancel'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ OK', callback_data: $okCallback),
                InlineKeyboardButton::make('❌ Annuler', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier avec 3 options (Oui/Non/Plus tard)
     */
    public static function yesNoLater(string $yesCallback, string $noCallback, string $laterCallback): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui', callback_data: $yesCallback),
                InlineKeyboardButton::make('❌ Non', callback_data: $noCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('⏰ Plus tard', callback_data: $laterCallback)
            );
    }

    /**
     * Clavier de validation d'action (Valider/Annuler)
     */
    public static function validateCancel(string $validateCallback = 'validate', string $cancelCallback = 'cancel'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Valider', callback_data: $validateCallback),
                InlineKeyboardButton::make('❌ Annuler', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier de fermeture de ticket
     */
    public static function closeTicket(int $ticketId): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui, clôturer', callback_data: "ticket_close_confirm_{$ticketId}")
            )
            ->addRow(
                InlineKeyboardButton::make('💬 Ajouter un message', callback_data: "ticket_add_message_{$ticketId}"),
                InlineKeyboardButton::make('❌ Annuler', callback_data: "ticket_close_cancel_{$ticketId}")
            );
    }

    /**
     * Clavier de confirmation avec détails
     */
    public static function confirmWithDetails(string $confirmCallback, string $viewDetailsCallback, string $cancelCallback): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Confirmer', callback_data: $confirmCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('👁️ Voir les détails', callback_data: $viewDetailsCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('❌ Annuler', callback_data: $cancelCallback)
            );
    }

    /**
     * Clavier d'acceptation de conditions
     */
    public static function acceptTerms(string $acceptCallback = 'terms_accept', string $declineCallback = 'terms_decline'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ J\'accepte', callback_data: $acceptCallback),
                InlineKeyboardButton::make('❌ Je refuse', callback_data: $declineCallback)
            )
            ->addRow(
                InlineKeyboardButton::make('📄 Lire les conditions', url: 'https://example.com/terms')
            );
    }

    /**
     * Clavier Reply (pour forcer une réponse utilisateur)
     */
    public static function replyYesNo(bool $oneTime = true): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(
            resize_keyboard: true,
            one_time_keyboard: $oneTime
        )
            ->addRow(
                KeyboardButton::make('✅ Oui'),
                KeyboardButton::make('❌ Non')
            );
    }

    /**
     * Clavier Reply avec bouton Annuler
     */
    public static function replyWithCancel(bool $oneTime = true): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(
            resize_keyboard: true,
            one_time_keyboard: $oneTime
        )
            ->addRow(
                KeyboardButton::make('✅ Confirmer')
            )
            ->addRow(
                KeyboardButton::make('❌ Annuler')
            );
    }

    /**
     * Clavier Reply pour terminer ou continuer
     */
    public static function replyFinishOrContinue(bool $oneTime = true): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(
            resize_keyboard: true,
            one_time_keyboard: $oneTime
        )
            ->addRow(
                KeyboardButton::make('✅ Terminer'),
                KeyboardButton::make('➕ Continuer')
            )
            ->addRow(
                KeyboardButton::make('❌ Annuler')
            );
    }

    /**
     * Supprimer le clavier Reply
     */
    public static function remove(): ReplyKeyboardRemove
    {
        return ReplyKeyboardRemove::make(true);
    }

    /**
     * Clavier personnalisé avec callback dynamique
     */
    public static function custom(array $buttons, int $buttonsPerRow = 2): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        $count = 0;

        foreach ($buttons as $text => $callback) {
            $row[] = InlineKeyboardButton::make($text, callback_data: $callback);
            $count++;

            if ($count === $buttonsPerRow) {
                $keyboard->addRow(...$row);
                $row = [];
                $count = 0;
            }
        }

        // Ajouter la dernière ligne si elle n'est pas complète
        if (!empty($row)) {
            $keyboard->addRow(...$row);
        }

        return $keyboard;
    }

    /**
     * Clavier de notation (1-5 étoiles)
     */
    public static function rating(string $callbackPrefix = 'rating'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('⭐', callback_data: "{$callbackPrefix}_1"),
                InlineKeyboardButton::make('⭐⭐', callback_data: "{$callbackPrefix}_2"),
                InlineKeyboardButton::make('⭐⭐⭐', callback_data: "{$callbackPrefix}_3")
            )
            ->addRow(
                InlineKeyboardButton::make('⭐⭐⭐⭐', callback_data: "{$callbackPrefix}_4"),
                InlineKeyboardButton::make('⭐⭐⭐⭐⭐', callback_data: "{$callbackPrefix}_5")
            )
            ->addRow(
                InlineKeyboardButton::make('❌ Annuler', callback_data: "{$callbackPrefix}_cancel")
            );
    }

    /**
     * Clavier de satisfaction (Satisfait/Pas satisfait)
     */
    public static function satisfaction(string $satisfiedCallback = 'satisfied_yes', string $notSatisfiedCallback = 'satisfied_no'): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('😊 Satisfait', callback_data: $satisfiedCallback),
                InlineKeyboardButton::make('😞 Pas satisfait', callback_data: $notSatisfiedCallback)
            );
    }
}
