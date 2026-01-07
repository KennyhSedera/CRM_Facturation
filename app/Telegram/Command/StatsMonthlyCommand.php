<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;

class StatsMonthlyCommand extends Command
{
    protected string $name = 'stats_monthly';
    protected string $description = 'Statistiques mensuelles détaillées';

    public function handle()
    {
        $this->showMonthlyStats();
    }

    public function handleCallback($chatId)
    {
        $this->showMonthlyStats($chatId);
    }

    private function showMonthlyStats($chatId = null)
    {
        $stats = $this->calculateMonthlyStats();

        $text = "📊 **Statistiques Mensuelles** :\n\n";

        // Évolution du CA sur 6 mois
        $text .= "💰 **Évolution du Chiffre d'Affaires** :\n";
        foreach ($stats['ca_evolution'] as $month => $ca) {
            $text .= "   • {$month} : {$ca}€\n";
        }
        $text .= "\n";

        // Top mois
        $text .= "🏆 **Meilleur mois** : {$stats['meilleur_mois']} ({$stats['meilleur_ca']}€)\n";
        $text .= "📉 **Mois le plus faible** : {$stats['moins_bon_mois']} ({$stats['moins_bon_ca']}€)\n\n";

        // Évolution des devis
        $text .= "📄 **Évolution des Devis** :\n";
        foreach ($stats['devis_evolution'] as $month => $count) {
            $text .= "   • {$month} : {$count} devis\n";
        }
        $text .= "\n";

        // Évolution des factures
        $text .= "🧾 **Évolution des Factures** :\n";
        foreach ($stats['factures_evolution'] as $month => $count) {
            $text .= "   • {$month} : {$count} factures\n";
        }
        $text .= "\n";

        // Nouveaux clients par mois
        $text .= "👥 **Nouveaux Clients** :\n";
        foreach ($stats['nouveaux_clients_evolution'] as $month => $count) {
            $text .= "   • {$month} : {$count} nouveaux\n";
        }
        $text .= "\n";

        // Moyennes
        $text .= "📈 **Moyennes** :\n";
        $text .= "   • CA moyen/mois : {$stats['ca_moyen']}€\n";
        $text .= "   • Devis moyen/mois : {$stats['devis_moyen']}\n";
        $text .= "   • Factures moyen/mois : {$stats['factures_moyen']}\n";
        $text .= "   • Nouveaux clients/mois : {$stats['clients_moyen']}";

        if ($chatId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        } else {
            $this->replyWithMessage([
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    private function calculateMonthlyStats()
    {
        $now = Carbon::now();
        $stats = [];

        // Évolution du CA sur 6 mois
        $ca_evolution = [];
        $devis_evolution = [];
        $factures_evolution = [];
        $nouveaux_clients_evolution = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $monthName = $month->format('M Y');

            // CA du mois
            $ca = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('total');
            $ca_evolution[$monthName] = number_format($ca, 2);

            // Devis du mois
            $devis = Quote::whereBetween('date', [$startOfMonth, $endOfMonth])->count();
            $devis_evolution[$monthName] = $devis;

            // Factures du mois
            $factures = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])->count();
            $factures_evolution[$monthName] = $factures;

            // Nouveaux clients du mois
            $nouveaux_clients = Client::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $nouveaux_clients_evolution[$monthName] = $nouveaux_clients;
        }

        // Trouver le meilleur et le moins bon mois
        $ca_values = array_map(function($ca) {
            return (float) str_replace(',', '', $ca);
        }, $ca_evolution);

        $meilleur_mois = array_keys($ca_values, max($ca_values))[0];
        $moins_bon_mois = array_keys($ca_values, min($ca_values))[0];

        // Calculer les moyennes
        $ca_moyen = number_format(array_sum($ca_values) / count($ca_values), 2);
        $devis_moyen = number_format(array_sum($devis_evolution) / count($devis_evolution), 1);
        $factures_moyen = number_format(array_sum($factures_evolution) / count($factures_evolution), 1);
        $clients_moyen = number_format(array_sum($nouveaux_clients_evolution) / count($nouveaux_clients_evolution), 1);

        return [
            'ca_evolution' => $ca_evolution,
            'devis_evolution' => $devis_evolution,
            'factures_evolution' => $factures_evolution,
            'nouveaux_clients_evolution' => $nouveaux_clients_evolution,
            'meilleur_mois' => $meilleur_mois,
            'meilleur_ca' => $ca_evolution[$meilleur_mois],
            'moins_bon_mois' => $moins_bon_mois,
            'moins_bon_ca' => $ca_evolution[$moins_bon_mois],
            'ca_moyen' => $ca_moyen,
            'devis_moyen' => $devis_moyen,
            'factures_moyen' => $factures_moyen,
            'clients_moyen' => $clients_moyen,
        ];
    }
}
