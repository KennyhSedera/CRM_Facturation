<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Statistiques principales
        $stats = $this->getMainStats($companyId);

        // Statut des factures
        $invoicesStatus = $this->getInvoicesStatus($companyId);

        // Activités récentes
        $recentActivities = $this->getRecentActivities($companyId);

        // Top articles
        $topArticles = $this->getTopArticles($companyId);

        // Top clients
        $topClients = $this->getTopClients($companyId);

        // Données pour le graphique CA
        $revenueData = $this->getRevenueData($companyId, $request->input('period', '30days'));

        return response()->json([
            'stats' => $stats,
            'invoicesStatus' => $invoicesStatus,
            'recentActivities' => $recentActivities,
            'topArticles' => $topArticles,
            'topClients' => $topClients,
            'revenueData' => $revenueData,
        ]);
    }

    private function getMainStats($companyId)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Chiffre d'affaires du mois en cours
        $currentRevenue = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('created_at', '>=', $currentMonth)
            ->sum('total');

        // Chiffre d'affaires du mois précédent
        $lastRevenue = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total');

        $revenueChange = $lastRevenue > 0
            ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1)
            : 0;

        // Nombre de factures
        $currentInvoices = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('created_at', '>=', $currentMonth)
            ->count();

        $lastInvoices = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->count();

        $invoicesChange = $lastInvoices > 0
            ? round((($currentInvoices - $lastInvoices) / $lastInvoices) * 100, 1)
            : 0;

        // Nombre d'articles
        $currentArticles = Article::where('company_id', $companyId)->count();
        $lastArticlesCount = Article::where('company_id', $companyId)
            ->where('created_at', '<', $currentMonth)
            ->count();

        $articlesChange = $lastArticlesCount > 0
            ? round((($currentArticles - $lastArticlesCount) / $lastArticlesCount) * 100, 1)
            : 0;

        // Nombre de clients
        $currentClients = Client::where('company_id', $companyId)->count();
        $lastClientsCount = Client::where('company_id', $companyId)
            ->where('created_at', '<', $currentMonth)
            ->count();

        $clientsChange = $lastClientsCount > 0
            ? round((($currentClients - $lastClientsCount) / $lastClientsCount) * 100, 1)
            : 0;

        return [
            [
                'title' => 'Chiffre d\'affaires',
                'value' => number_format($currentRevenue, 2, ',', ' ') . ' €',
                'change' => ($revenueChange >= 0 ? '+' : '') . $revenueChange . '%',
                'changeType' => $revenueChange >= 0 ? 'positive' : 'negative',
                'icon' => 'Euro',
                'bgColor' => 'bg-blue-500',
            ],
            [
                'title' => 'Factures',
                'value' => (string) $currentInvoices,
                'change' => ($invoicesChange >= 0 ? '+' : '') . $invoicesChange . '%',
                'changeType' => $invoicesChange >= 0 ? 'positive' : 'negative',
                'icon' => 'FileText',
                'bgColor' => 'bg-green-500',
            ],
            [
                'title' => 'Articles',
                'value' => (string) $currentArticles,
                'change' => ($articlesChange >= 0 ? '+' : '') . $articlesChange . '%',
                'changeType' => $articlesChange >= 0 ? 'positive' : 'negative',
                'icon' => 'Package',
                'bgColor' => 'bg-purple-500',
            ],
            [
                'title' => 'Clients',
                'value' => (string) $currentClients,
                'change' => ($clientsChange >= 0 ? '+' : '') . $clientsChange . '%',
                'changeType' => $clientsChange >= 0 ? 'positive' : 'negative',
                'icon' => 'Users',
                'bgColor' => 'bg-orange-500',
            ],
        ];
    }

    private function getInvoicesStatus($companyId)
    {
        // Nombre total de factures et devis
        $totalInvoices = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->count();

        $totalInvoicesAmount = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->sum('total');

        // Devis en attente
        $pendingQuotes = Quote::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('quote_status', 'pending')
            ->count();

        $pendingQuotesAmount = Quote::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('quote_status', 'pending')
            ->sum('total_amount');

        // Devis acceptés (convertis en factures)
        $acceptedQuotes = Quote::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('quote_status', 'accepted')
            ->count();

        $acceptedQuotesAmount = Quote::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->where('quote_status', 'accepted')
            ->sum('total_amount');

        return [
            [
                'status' => 'Factures totales',
                'count' => $totalInvoices,
                'amount' => number_format($totalInvoicesAmount ?? 0, 2, ',', ' ') . ' €',
                'icon' => 'CheckCircle2',
                'color' => 'text-green-600 dark:text-green-400',
                'bgColor' => 'bg-green-100 dark:bg-green-900/30',
            ],
            [
                'status' => 'Devis en attente',
                'count' => $pendingQuotes,
                'amount' => number_format($pendingQuotesAmount ?? 0, 2, ',', ' ') . ' €',
                'icon' => 'Clock',
                'color' => 'text-orange-600 dark:text-orange-400',
                'bgColor' => 'bg-orange-100 dark:bg-orange-900/30',
            ],
            [
                'status' => 'Devis acceptés',
                'count' => $acceptedQuotes,
                'amount' => number_format($acceptedQuotesAmount ?? 0, 2, ',', ' ') . ' €',
                'icon' => 'FileCheck',
                'color' => 'text-blue-600 dark:text-blue-400',
                'bgColor' => 'bg-blue-100 dark:bg-blue-900/30',
            ],
        ];
    }

    private function getTopClients($companyId, $limit = 5)
    {
        return DB::table('clients')
            ->leftJoin('invoices', function ($join) use ($companyId) {
                $join->on('clients.client_id', '=', 'invoices.client_id')
                    ->whereExists(function ($query) use ($companyId) {
                        $query->select(DB::raw(1))
                            ->from('users')
                            ->whereColumn('invoices.user_id', 'users.id')
                            ->where('users.company_id', $companyId);
                    });
            })
            ->where('clients.company_id', $companyId)
            ->select(
                'clients.client_name',
                DB::raw('COUNT(invoices.id) as invoices_count'),
                DB::raw('COALESCE(SUM(invoices.total), 0) as total_amount')
            )
            ->groupBy('clients.client_id', 'clients.client_name')
            ->havingRaw('COUNT(invoices.id) > 0') // Utiliser la fonction complète au lieu de l'alias
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get()
            ->map(function ($client) {
                return [
                    'name' => $client->client_name, // Correction ici aussi
                    'invoices' => (int) $client->invoices_count,
                    'amount' => number_format($client->total_amount ?? 0, 2, ',', ' ') . ' €',
                ];
            });
    }

    private function getRecentActivities($companyId)
    {
        $activities = collect();

        // Dernières factures créées
        $invoices = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => 'invoice_' . $invoice->id,
                    'action' => 'Facture #' . $invoice->id . ' créée',
                    'client' => $invoice->client->client_name ?? 'Client inconnu',
                    'time' => $invoice->created_at->locale('fr')->diffForHumans(),
                    'type' => 'invoice',
                    'timestamp' => $invoice->created_at,
                ];
            });

        // Derniers devis créés
        $quotes = Quote::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($quote) {
                return [
                    'id' => 'quote_' . $quote->quote_id,
                    'action' => 'Devis #' . $quote->quote_id . ' créé',
                    'client' => $quote->client->client_name ?? 'Client inconnu',
                    'time' => $quote->created_at->locale('fr')->diffForHumans(),
                    'type' => 'quote',
                    'timestamp' => $quote->created_at,
                ];
            });

        // Alertes stock faible
        $stockFaible = Article::where('company_id', $companyId)
            ->where('quantity_stock', '<=', 10)
            ->orderBy('updated_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => 'stock_' . $article->id,
                    'action' => 'Stock faible: ' . $article->article_name,
                    'client' => $article->quantity_stock . ' unités restantes',
                    'time' => $article->updated_at->locale('fr')->diffForHumans(),
                    'type' => 'alert',
                    'timestamp' => $article->updated_at,
                ];
            });

        $clients = Client::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => 'client_' . $client->id,
                    'action' => 'Nouveau client ajouté',
                    'client' => $client->client_name,
                    'time' => $client->created_at->locale('fr')->diffForHumans(),
                    'type' => 'user',
                    'timestamp' => $client->created_at,
                ];
            });

        // Fusionner et trier par date
        $activities = $activities
            ->concat($invoices)
            ->concat($quotes)
            ->concat($stockFaible)
            ->concat($clients)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values()
            ->map(function ($activity) {
                unset($activity['timestamp']);
                return $activity;
            });

        return $activities;
    }

    private function getTopArticles($companyId, $limit = 5)
    {
        return DB::table('invoice_items')
            ->join('articles', 'invoice_items.article_id', '=', 'articles.article_id') // Changé de articles.id
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->where('users.company_id', $companyId)
            ->where('articles.company_id', $companyId)
            ->select(
                'articles.article_name as name',
                DB::raw('SUM(invoice_items.quantity) as sales'),
                DB::raw('SUM(invoice_items.quantity * invoice_items.price) as revenue')
            )
            ->groupBy('articles.article_id', 'articles.article_name') // Changé de articles.id
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sales' => (int) $item->sales,
                    'revenue' => number_format($item->revenue, 2, ',', ' ') . ' €',
                ];
            });
    }

    private function getRevenueData($companyId, $period)
    {
        $dates = $this->getPeriodDates($period);

        $data = Invoice::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->selectRaw('DATE(date) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d/m'),
                    'montant' => (float) $item->total,
                ];
            });

        return $data;
    }

    private function getPeriodDates($period)
    {
        switch ($period) {
            case '7days':
                return [
                    'start' => Carbon::now()->subDays(7),
                    'end' => Carbon::now(),
                ];
            case '30days':
            default:
                return [
                    'start' => Carbon::now()->subDays(30),
                    'end' => Carbon::now(),
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth(),
                ];
            case 'year':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear(),
                ];
        }
    }
}
