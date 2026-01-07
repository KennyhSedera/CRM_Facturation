<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use App\Models\User;

class ProfileCommand extends Command
{
    protected string $command = 'profile';
    protected ?string $description = 'Voir mon profil';

    public function handle(Nutgram $bot): void
    {
        $user = $this->getUser($bot);

        if (!$user) {
            return;
        }

        \Log::info($user);

        // Informations utilisateur
        $message = "👤 <b>Votre Profil</b>\n\n"
            . "📝 Nom : {$user->name}\n"
            . "🆔 ID Telegram : <code>{$user->telegram_id}</code>\n"
            . "📧 Email : {$user->email}\n"
            . "👔 Rôle : " . $this->formatRole($user->user_role) . "\n"
            . "📅 Membre depuis : " . $user->created_at->format('d/m/Y') . "\n";

        // Informations entreprise si disponible
        if ($user->company) {
            $company = $user->company;
            $planEndDate = \Carbon\Carbon::parse($company->plan_end_date);
            $daysRemaining = (int) round(now()->diffInDays($planEndDate, false));

            $message .= "\n🏢 <b>Entreprise</b>\n\n"
                . "🏷️ Nom : {$company->company_name}\n"
                . "📧 Email : {$company->company_email}\n"
                . "📞 Téléphone : {$company->company_phone}\n"
                . "🌍 Pays : {$company->company_country}\n"
                . "📍 Adresse : {$company->company_address}\n"
                . "🌐 Site web : {$company->company_website}\n"
                . "💰 Devise : {$company->company_currency}\n"
                . "📊 Plan : " . strtoupper($company->plan_status) . "\n"
                . "📅 Fin du plan : " . $planEndDate->format('d/m/Y')
                . " (" . ($daysRemaining > 0 ? "{$daysRemaining} jours restants" : "Expiré") . ")\n"
                . "👥 Clients : {$company->client_count}\n"
                . "✅ Statut : " . ($company->is_active ? "Actif" : "Inactif");
        }

        $bot->sendMessage($message, parse_mode: 'HTML');
    }

    public static function getUser($bot)
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user) {
            $bot->sendMessage("❌ Profil non trouvé. Utilisez /start pour vous inscrire.");
            return null;
        }

        return $user;
    }

    private function formatRole($role): string
    {
        $roles = [
            'admin_company' => '🔑 Administrateur',
            'user' => '👤 Utilisateur',
            'manager' => '👨‍💼 Manager',
        ];

        return $roles[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }
}
