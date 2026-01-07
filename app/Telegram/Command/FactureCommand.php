<?php

namespace App\Telegram\Commands;

use App\Models\Invoice;
use App\Models\Quote;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class FactureCommand extends Command
{
    protected string $name = 'facture';
    protected string $description = 'Afficher vos devis/factures';

    public function handle()
    {
        // La logique principale dans une méthode séparée
        $this->showFactures();
    }

    /**
     * Méthode pour exécuter depuis un callback
     */
    public function handleCallback($chatId)
    {
        // Utiliser la même logique que handle()
        $this->showFactures($chatId);
    }

    /**
     * Logique principale - utilisée par handle() et handleCallback()
     */
    private function showFactures($chatId = null)
    {
        try {
            // Récupérer les devis et factures depuis la base de données
            $quotes = $this->getQuotesFromDatabase();
            $invoices = $this->getInvoicesFromDatabase();

            $text = "📄 **Vos devis et factures** :\n\n";

            // Afficher les devis
            if (!empty($quotes)) {
                $text .= "📋 **DEVIS** :\n";
                foreach ($quotes as $quote) {
                    $text .= "🔹 Devis #{$quote->id} - {$quote->client->name}\n";
                    $text .= "   💰 {$quote->total}€ - 📅 {$quote->date}\n";
                    $text .= "   📊 Status: " . $this->getQuoteStatus($quote) . "\n\n";
                }
            }

            // Afficher les factures
            if (!empty($invoices)) {
                $text .= "🧾 **FACTURES** :\n";
                foreach ($invoices as $invoice) {
                    $text .= "🔸 Facture #{$invoice->id} - {$invoice->client->name}\n";
                    $text .= "   💰 {$invoice->total}€ - 📅 {$invoice->date}\n";
                    if ($invoice->quote_id) {
                        $text .= "   🔗 Basée sur devis #{$invoice->quote_id}\n";
                    }
                    $text .= "\n";
                }
            }

            // Si aucun devis ni facture
            if (empty($quotes) && empty($invoices)) {
                $text .= "Aucun devis ou facture trouvé.";
            }

            // Ajouter des statistiques
            $text .= $this->getStatistics($quotes, $invoices);

        } catch (\Exception $e) {
            $text = "❌ Erreur lors de la récupération des données : " . $e->getMessage();
        }

        // Si c'est un callback, envoyer directement
        if ($chatId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        } else {
            // Si c'est une commande normale, utiliser replyWithMessage
            $this->replyWithMessage([
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    /**
     * Récupérer les devis depuis la base de données
     */
    private function getQuotesFromDatabase()
    {
        return Quote::with(['client'])
            ->latest('date')
            ->limit(10)
            ->get();
    }

    /**
     * Récupérer les factures depuis la base de données
     */
    private function getInvoicesFromDatabase()
    {
        return Invoice::with(['client', 'quote'])
            ->latest('date')
            ->limit(10)
            ->get();
    }

    /**
     * Déterminer le status d'un devis
     */
    private function getQuoteStatus($quote)
    {
        // Si le devis a une facture associée
        if ($quote->invoices()->exists()) {
            return "✅ Facturé";
        }

        // Logique pour déterminer si un devis est expiré (exemple: 30 jours)
        $createdDate = \Carbon\Carbon::parse($quote->date);
        $now = \Carbon\Carbon::now();

        if ($createdDate->diffInDays($now) > 30) {
            return "⏰ Expiré";
        }

        return "⏳ En attente";
    }

    /**
     * Générer des statistiques rapides
     */
    private function getStatistics($quotes, $invoices)
    {
        $stats = "\n📊 **Statistiques** :\n";

        // Compter les devis et factures
        $quotesCount = count($quotes);
        $invoicesCount = count($invoices);

        $stats .= "• {$quotesCount} devis récents\n";
        $stats .= "• {$invoicesCount} factures récentes\n";

        // Calculer les totaux
        if ($quotesCount > 0) {
            $totalQuotes = $quotes->sum('total');
            $stats .= "• Total devis : {$totalQuotes}€\n";
        }

        if ($invoicesCount > 0) {
            $totalInvoices = $invoices->sum('total');
            $stats .= "• Total factures : {$totalInvoices}€\n";
        }

        return $stats;
    }
}
