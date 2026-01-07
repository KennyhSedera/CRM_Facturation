<?php

namespace App\Telegram\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use App\Models\User;
use Illuminate\Support\Str;

class RegisterConversation extends Conversation
{
    protected array $data = [];

    public function run()
    {
        $this->askName();
    }

    protected function askName(): void
    {
        $this->ask('Quel est votre nom complet ?', function (Answer $answer) {
            $name = trim($answer->getText() ?? '');
            if ($name === '') {
                $this->say('Le nom ne peut pas être vide.');
                return $this->repeat();
            }
            $this->data['name'] = $name;
            $this->askEmail();
        });
    }

    protected function askEmail(): void
    {
        $this->ask("Quelle est votre adresse e-mail ?", function (Answer $answer) {
            $email = trim($answer->getText() ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->say("Adresse e-mail invalide.");
                return $this->repeat();
            }
            $this->data['email'] = $email;
            $this->askPhone();
        });
    }

    protected function askPhone(): void
    {
        $this->ask("Numéro de téléphone (optionnel) :", function (Answer $answer) {
            $phone = trim($answer->getText() ?? '');
            $this->data['phone'] = $phone === '' ? null : $phone;
            $this->saveUser();
        });
    }

    protected function saveUser(): void
    {
        $telegramUser = $this->bot->getUser();
        $telegramId = $telegramUser ? $telegramUser->getId() : null;

        $user = User::updateOrCreate(
            ['telegram_id' => $telegramId ?? Str::random(24)],
            [
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'phone' => $this->data['phone'],
            ]
        );

        $this->say("Inscription terminée. Merci, {$user->name} !");
    }
}
