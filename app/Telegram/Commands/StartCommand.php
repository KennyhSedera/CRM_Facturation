<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use App\Models\User;

class StartCommand extends Command
{
    protected string $command = 'start';
    protected ?string $description = 'Démarrer le bot';

    public function handle(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user) {
            (new CreateCompanyCommand())->handle($bot);
            return;
        } else if ($user->company === null) {
            (new CreateCompanyCommand())->handle($bot);
            return;
        } else {
            $message = $this->getWelcomeMessage($bot->user()->first_name, $user);
            $bot->sendMessage(
                text: $message,
                parse_mode: 'HTML'
            );
        }
    }

    /**
     * Message de bienvenue pour nouvel utilisateur
     */
    public static function getWelcomeMessageNewUser(string $firstName): string
    {
        return "👋 Bienvenue <b>{$firstName}</b> !\n\n"
            . "🌞 Je suis votre <b>assistant FacturePro</b>.\n\n"
            . "✨ <b>Ce que je peux faire pour vous :</b>\n"
            . "📊 Calculer vos installations solaires\n"
            . "👥 Gérer vos clients\n"
            . "📋 Générer des devis professionnels\n"
            . "📦 Accéder aux articles et matériels\n\n"
            . "🎁 <b>Vous démarrez en mode GRATUIT :</b>\n"
            . "• Jusqu'à <b>3 clients</b>\n"
            . "• <b>5 devis par mois</b>\n"
            . "• Calculatrice illimitée\n\n"
            . "⚡ <b>Première étape importante :</b>\n"
            . "Avant de commencer, vous devez <b>créer votre entreprise</b> pour pouvoir générer des devis à votre nom.\n\n"
            . "💎 <b>Nos plans disponibles :</b>\n"
            . "• 🆓 <b>GRATUIT</b> - Idéal pour démarrer\n"
            . "• ⭐ <b>PREMIUM</b> - Pour les professionnels\n"
            . "• 🏢 <b>ENTREPRISE</b> - Solutions sur mesure\n\n"
            . "💡 Vous pourrez choisir votre plan lors de la création de votre entreprise !";
    }

    /**
     * Message de bienvenue pour utilisateur existant
     */
    public function getWelcomeMessage(string $firstName, User $user): string
    {
        $company = $user->company;
        $planEmoji = $this->getPlanEmoji($company->plan_status ?? 'free');
        $planName = strtoupper($company->plan_status ?? 'GRATUIT');

        // Calcul des jours restants
        $daysRemaining = 0;
        if ($company && $company->plan_end_date) {
            $planEndDate = \Carbon\Carbon::parse($company->plan_end_date);
            $daysRemaining = (int) round(now()->diffInDays($planEndDate, false));
        }

        $message = "👋 Bon retour <b>{$firstName}</b> !\n\n"
            . "🏢 Entreprise : <b>{$company->company_name}</b>\n"
            . "{$planEmoji} Plan : <b>{$planName}</b>";

        if ($daysRemaining > 0) {
            $message .= " ({$daysRemaining} jours restants)";
        } elseif ($daysRemaining == 0) {
            $message .= " (Expire aujourd'hui ⚠️)";
        } else {
            $message .= " (Expiré ❌)";
        }

        $message .= "\n👥 Clients : <b>{$company->client_count}</b>\n\n"
            . "✨ <b>Que souhaitez-vous faire ?</b>\n\n"
            . "📊 /calculate - Calculer une installation\n"
            . "👥 /clients - Gérer vos clients\n"
            . "📋 /quotes - Créer un devis\n"
            . "📦 /articles - Gérer vos articles\n"
            . "👤 /profile - Mon profil\n"
            . "⚙️ /settings - Paramètres\n\n"
            . "💡 Besoin d'aide ? Tapez /help";

        return $message;
    }

    /**
     * Obtenir l'emoji du plan
     */
    private function getPlanEmoji(string $plan): string
    {
        $emojis = [
            'free' => '🆓',
            'premium' => '⭐',
            'enterprise' => '🏢',
        ];

        return $emojis[$plan] ?? '📦';
    }
}
