<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;

class StatsChartsCommand extends Command
{
    protected string $name = 'stats_charts';
    protected string $description = 'Graphiques et visualisations';

    public function handle()
    {
        $this->showCharts();
    }

    public function handleCallback($chatId)
    {
        $this->showCharts($chatId);
    }

    private function showCharts($chatId = null)
    {
        $stats = $this->calculateChartData();

        $text = "📈 **Graphiques et Visualisations** :\n\n";

        // Graphique CA des 6 derniers mois
        $text .= "💰 **Évolution du CA (6 mois)** :\n";
        $text .= $this->createBarChart($stats['ca_data'], '€');
        $text .= "\n";

        // Répartition des statuts de factures
        $text .= "🧾 **Répartition des Factures** :\n";
        $text .= $this->createPieChart($stats['factures_status']);
        $text .= "\n";

        // Top clients
        $text .= "👥 **Top 5 Clients (CA)** :\n";
        $text .= $this->createTopList($stats['top_clients'], '€');
        $text .= "\n";

        // Indicateurs de performance
        $text .= "🎯 **Indicateurs de Performance** :\n";
        $text .= $this->createPerformanceIndicators($stats['kpis']);

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

    private function calculateChartData()
    {
        $now = Carbon::now();
        
        // Données CA sur 6 mois
        $ca_data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $monthName = $month->format('M');
            
            $ca = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('total');
            $ca_data[$monthName] = $ca;
        }

        // Statuts des factures
        $factures_status = [
            'Payées' => Invoice::where('status', 'paid')->count(),
            'En attente' => Invoice::where('status', 'sent')->count(),
            'Brouillons' => Invoice::where('status', 'draft')->count(),
            'En retard' => Invoice::where('status', 'overdue')->count(),
        ];

        // Top clients par CA
        $top_clients = Client::withSum('invoices', 'total')
            ->orderByDesc('invoices_sum_total')
            ->limit(5)
            ->get()
            ->mapWithKeys(function($client) {
                return [$client->name => $client->invoices_sum_total ?? 0];
            })
            ->toArray();

        // KPIs
        $kpis = [
            'taux_conversion' => $this->calculateConversionRate(),
            'panier_moyen' => $this->calculateAverageCart(),
            'taux_retard' => $this->calculateOverdueRate(),
        ];

        return [
            'ca_data' => $ca_data,
            'factures_status' => $factures_status,
            'top_clients' => $top_clients,
            'kpis' => $kpis,
        ];
    }

    private function createBarChart($data, $unit = '')
    {
        $max = max($data);
        $chart = '';
        
        foreach ($data as $label => $value) {
            $percentage = $max > 0 ? ($value / $max) * 100 : 0;
            $bars = str_repeat('█', round($percentage / 10));
            $chart .= sprintf("  %-3s | %-10s %s%s\n", $label, $bars, number_format($value, 2), $unit);
        }
        
        return $chart;
    }

    private function createPieChart($data)
    {
        $total = array_sum($data);
        $chart = '';
        
        foreach ($data as $label => $value) {
            $percentage = $total > 0 ? ($value / $total) * 100 : 0;
            $chart .= sprintf("  %-10s : %s (%s%%)\n", $label, $value, number_format($percentage, 1));
        }
        
        return $chart;
    }

    private function createTopList($data, $unit = '')
    {
        $chart = '';
        $rank = 1;
        
        foreach ($data as $label => $value) {
            $chart .= sprintf("  %d. %-20s : %s%s\n", $rank, substr($label, 0, 20), number_format($value, 2), $unit);
            $rank++;
        }
        
        return $chart;
    }

    private function createPerformanceIndicators($kpis)
    {
        $indicators = '';
        $indicators .= sprintf("  📈 Taux de conversion : %s%%\n", number_format($kpis['taux_conversion'], 1));
        $indicators .= sprintf("  🛒 Panier moyen : %s€\n", number_format($kpis['panier_moyen'], 2));
        $indicators .= sprintf("  ⚠️ Taux de retard : %s%%", number_format($kpis['taux_retard'], 1));
        
        return $indicators;
    }

    private function calculateConversionRate()
    {
        $total_quotes = Quote::count();
        $accepted_quotes = Quote::where('status', 'accepted')->count();
        
        return $total_quotes > 0 ? ($accepted_quotes / $total_quotes) * 100 : 0;
    }

    private function calculateAverageCart()
    {
        $total_invoices = Invoice::count();
        $total_amount = Invoice::sum('total');
        
        return $total_invoices > 0 ? $total_amount / $total_invoices : 0;
    }

    private function calculateOverdueRate()
    {
        $total_invoices = Invoice::count();
        $overdue_invoices = Invoice::where('status', 'overdue')->count();
        
        return $total_invoices > 0 ? ($overdue_invoices / $total_invoices) * 100 : 0;
    }
} 