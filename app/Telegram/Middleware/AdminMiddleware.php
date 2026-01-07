<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;
use App\Models\User;

/**
 * Middleware pour protéger les commandes administrateur
 */
class AdminMiddleware
{
    /**
     * Vérifier si l'utilisateur a le rôle super_admin
     */
    public function __invoke(Nutgram $bot, $next): void
    {
        $user = User::where('telegram_id', $bot->userId())->first();

        // Si l'utilisateur n'existe pas ou n'est pas super_admin
        if (!$user || $user->user_role !== 'super_admin') {
            $bot->sendMessage(
                text: "❌ <b>Accès refusé</b>\n\n"
                . "Cette commande est réservée aux administrateurs.\n\n"
                . "Si vous pensez qu'il s'agit d'une erreur, contactez le support avec /help",
                parse_mode: 'HTML'
            );
            return;
        }

        // L'utilisateur est admin, continuer
        $next($bot);
    }

    /**
     * Vérifier rapidement si un utilisateur est admin
     */
    public static function isAdmin(Nutgram $bot): bool
    {
        $user = User::where('telegram_id', $bot->userId())->first();
        return $user && $user->user_role === 'super_admin';
    }

    /**
     * Vérifier si un utilisateur a un des rôles spécifiés
     */
    public static function hasRole(Nutgram $bot, array $roles): bool
    {
        $user = User::where('telegram_id', $bot->userId())->first();
        return $user && in_array($user->user_role, $roles);
    }
}
