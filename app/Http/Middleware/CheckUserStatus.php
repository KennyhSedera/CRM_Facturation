<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;
use App\Models\User;

class CheckUserStatus
{
    private array $allowedCommands = ['/start', '/help', '/cancel'];

    private array $allowedCallbacks = ['help_'];

    public function __invoke(Nutgram $bot, $next): void
    {
        $message = $bot->message();
        $callbackQuery = $bot->callbackQuery();

        $text = $message?->text ?? '';
        $callbackData = $callbackQuery?->data ?? '';

        // Permettre les commandes autorisées sans vérification
        foreach ($this->allowedCommands as $cmd) {
            if (str_starts_with($text, $cmd)) {
                $next($bot);
                return;
            }
        }

        // Permettre certains callbacks sans vérification
        foreach ($this->allowedCallbacks as $pattern) {
            if (str_starts_with($callbackData, $pattern)) {
                $next($bot);
                return;
            }
        }

        // Récupérer l'utilisateur avec sa company
        $telegramId = $bot->userId();
        $user = User::with('company')->where('telegram_id', $telegramId)->first();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->sendError(
                $bot,
                "Compte non trouvé",
                "Vous devez d'abord créer un compte.\nUtilisez /start pour commencer."
            );
            return;
        }

        // Vérifier si l'utilisateur a une entreprise pour les commandes qui le nécessitent
        $requiresCompany = !in_array($text, ['/createcompany', '/help'])
            && !str_starts_with($callbackData, 'help_')
            && !str_starts_with($callbackData, 'plan:')
            && !str_starts_with($callbackData, 'create_');

        if ($requiresCompany && !$user->company_id) {
            $this->sendError(
                $bot,
                "Entreprise requise",
                "Vous devez d'abord créer une entreprise.\nUtilisez /createcompany pour commencer."
            );
            return;
        }

        // ✅ Vérifier si l'entreprise est active (uniquement si l'utilisateur a une company)
        if ($user->company_id && $user->company) {
            // Vérifier si is_active est false (entreprise inactive)
            if (!$user->company->is_active) {
                $this->sendError(
                    $bot,
                    "Entreprise inactive",
                    "Votre entreprise est actuellement inactive.\n\n"
                    . "Raisons possibles :\n"
                    . "• Abonnement expiré\n"
                    . "• Suspension administrative\n"
                    . "• Paiement en attente\n\n"
                    . "Contactez l'administrateur ou utilisez /subscription pour renouveler."
                );
                return;
            }
        }

        // Stocker l'utilisateur pour utilisation ultérieure
        $bot->setUserData('current_user', $user);

        // Tout est OK, continuer
        $next($bot);
    }

    private function sendError(Nutgram $bot, string $title, string $message): void
    {
        // Répondre au callback si c'est un callback query
        if ($bot->callbackQuery()) {
            try {
                $bot->answerCallbackQuery(
                    text: "❌ {$title}",
                    show_alert: true
                );
            } catch (\Exception $e) {
                \Log::debug('Callback already answered');
            }
        }

        // Envoyer le message d'erreur
        try {
            $bot->sendMessage(
                "❌ <b>{$title}</b>\n\n{$message}",
                parse_mode: \SergiX44\Nutgram\Telegram\Properties\ParseMode::HTML
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send error message: ' . $e->getMessage());
        }
    }
}
