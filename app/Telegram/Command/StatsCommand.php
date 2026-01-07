<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;

class StatsCommand extends Command
{
    protected string $name = 'stats';
    protected string $description = 'Afficher les statistiques';

    public function handle()
    {
        $this->showStats();
    }

    public function handleCallback($chatId)
    {
        $this->showStats($chatId);
    }

    private function showStats($chatId = null)
    {
        // Calculer les statistiques depuis la DB
        $stats = $this->calculateStats();

        $text = "📊 **Dashboard - Statistiques** :\n\n";

        // Chiffre d'affaires
        $text .= "💰 **Chiffre d'affaires** :\n";
        $text .= "   • Total facturé : {$stats['ca_total']}€\n";
        $text .= "   • Factures payées : {$stats['ca_paye']}€\n";
        $text .= "   • En attente : {$stats['ca_attente']}€\n\n";

        // Devis
        $text .= "📄 **Devis** :\n";
        $text .= "   • Total : {$stats['devis_total']}\n";
        $text .= "   • En cours : {$stats['devis_en_cours']}\n";
        $text .= "   • Acceptés : {$stats['devis_acceptes']}\n";
        $text .= "   • Brouillons : {$stats['devis_brouillons']}\n\n";

        // Factures
        $text .= "🧾 **Factures** :\n";
        $text .= "   • Total : {$stats['factures_total']}\n";
        $text .= "   • Payées : {$stats['factures_payees']}\n";
        $text .= "   • En retard : {$stats['factures_retard']}\n\n";

        // Clients
        $text .= "👥 **Clients** :\n";
        $text .= "   • Total : {$stats['clients_total']}\n";
        $text .= "   • Actifs ce mois : {$stats['clients_actifs']}\n";
        $text .= "   • Nouveaux ce mois : {$stats['nouveaux_clients']}\n\n";

        // Produits
        $text .= "📦 **Produits** :\n";
        $text .= "   • Total : {$stats['produits_total']}\n";
        $text .= "   • En stock : {$stats['produits_stock']}\n";
        $text .= "   • Stock faible : {$stats['produits_stock_faible']}\n\n";

        // Performance ce mois
        $text .= "📈 **Ce mois** :\n";
        $text .= "   • CA : {$stats['ca_mois']}€\n";
        $text .= "   • Nouveaux devis : {$stats['devis_mois']}\n";
        $text .= "   • Factures émises : {$stats['factures_mois']}";

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

    private function calculateStats()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        return [
            // Chiffre d'affaires
            'ca_total' => number_format(Invoice::sum('total'), 2),
            'ca_paye' => number_format(Invoice::where('status', 'paid')->sum('total'), 2),
            'ca_attente' => number_format(Invoice::whereIn('status', ['sent', 'draft'])->sum('total'), 2),
            'ca_mois' => number_format(Invoice::where('date', '>=', $startOfMonth)->sum('total'), 2),

            // Devis
            'devis_total' => Quote::count(),
            'devis_en_cours' => Quote::where('status', 'sent')->count(),
            'devis_acceptes' => Quote::where('status', 'accepted')->count(),
            'devis_brouillons' => Quote::where('status', 'draft')->count(),
            'devis_mois' => Quote::where('date', '>=', $startOfMonth)->count(),

            // Factures
            'factures_total' => Invoice::count(),
            'factures_payees' => Invoice::where('status', 'paid')->count(),
            'factures_retard' => Invoice::where('status', 'overdue')->count(),
            'factures_mois' => Invoice::where('date', '>=', $startOfMonth)->count(),

            // Clients
            'clients_total' => Client::count(),
            'clients_actifs' => Client::whereHas('quotes', function($q) use ($startOfMonth) {
                $q->where('date', '>=', $startOfMonth);
            })->orWhereHas('invoices', function($q) use ($startOfMonth) {
                $q->where('date', '>=', $startOfMonth);
            })->count(),
            'nouveaux_clients' => Client::where('created_at', '>=', $startOfMonth)->count(),

            // Produits
            'produits_total' => Product::count(),
            'produits_stock' => Product::where('stock', '>', 0)->count(),
            'produits_stock_faible' => Product::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
        ];
    }
}
