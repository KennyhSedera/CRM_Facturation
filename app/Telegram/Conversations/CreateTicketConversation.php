<?php

namespace App\Telegram\Conversations;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use App\Models\Ticket;

class CreateTicketConversation extends Conversation
{
    protected array $data = [];

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            "🎫 <b>Création d'un ticket</b>\n\n"
            . "Étape 1/3 : Choisissez la catégorie",
            reply_markup: $this->getCategoryKeyboard(),
            parse_mode: 'HTML'
        );

        $this->next('handleCategory');
    }

    public function handleCategory(Nutgram $bot): void
    {
        $callback = $bot->callbackQuery();

        if (!$callback) {
            $bot->sendMessage("❌ Veuillez utiliser les boutons");
            return;
        }

        $this->data['category'] = $callback->data;

        $bot->answerCallbackQuery("Catégorie sélectionnée ✅");

        $bot->editMessageText(
            "✅ Catégorie enregistrée\n\n"
            . "Étape 2/3 : Quel est le sujet de votre ticket ?",
            parse_mode: 'HTML'
        );

        $this->next('handleSubject');
    }

    public function handleSubject(Nutgram $bot): void
    {
        $subject = $bot->message()->text;

        if (!$subject || strlen($subject) < 5) {
            $bot->sendMessage("❌ Le sujet doit contenir au moins 5 caractères");
            return;
        }

        $this->data['subject'] = $subject;

        $bot->sendMessage(
            "✅ Sujet enregistré\n\n"
            . "Étape 3/3 : Décrivez votre problème en détail"
        );

        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        $description = $bot->message()->text;

        if (!$description || strlen($description) < 10) {
            $bot->sendMessage("❌ La description doit contenir au moins 10 caractères");
            return;
        }

        $this->data['description'] = $description;

        // Créer le ticket
        $ticket = Ticket::create([
            'user_telegram_id' => $bot->userId(),
            'category' => $this->data['category'],
            'subject' => $this->data['subject'],
            'description' => $description,
            'status' => 'open',
        ]);

        $bot->sendMessage(
            "✅ <b>Ticket créé avec succès !</b>\n\n"
            . "🎫 Numéro : #{$ticket->id}\n"
            . "📊 Statut : Ouvert\n\n"
            . "Nous vous répondrons dans les plus brefs délais.",
            parse_mode: 'HTML'
        );

        $this->end();
    }

    private function getCategoryKeyboard(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🔧 Technique', callback_data: 'tech'),
                InlineKeyboardButton::make('💳 Facturation', callback_data: 'billing')
            )
            ->addRow(
                InlineKeyboardButton::make('📦 Livraison', callback_data: 'shipping'),
                InlineKeyboardButton::make('❓ Autre', callback_data: 'other')
            );
    }
}
