<?php

namespace App\Telegram\Commands;

use App\Models\Invoice;
use App\Models\Quote;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class FactureAllCommand extends Command
{
    protected string $name = 'facture_all';
    protected string $description = 'Afficher toutes les factures';

    public function handle()
    {
        $this->showAllFactures();
    }

    public function handleCallback($chatId)
    {
        $this->showAllFactures($chatId);
    }

    private function showAllFactures($chatId = null)
    {
        try {
            $quotes = Quote::with(['client'])->latest('date')->limit(10)->get();
            $invoices = Invoice::with(['client', 'quote'])->latest('date')->limit(10)->get();

            $text = "📄 **Toutes vos factures et devis** :\n\n";

            if (!empty($quotes)) {
                $text .= "📋 **DEVIS** :\n";
                foreach ($quotes as $quote) {
                    $text .= "🔹 Devis #{$quote->id} - {$quote->client->name}\n";
                    $text .= "   💰 {$quote->total}€ - 📅 {$quote->date}\n";
                    $text .= "   📊 Status: " . $this->getQuoteStatus($quote) . "\n\n";
                }
            }

            if (!empty($invoices)) {
                $text .= "🧾 **FACTURES** :\n";
                foreach ($invoices as $invoice) {
                    $text .= "🔸 Facture #{$invoice->id} - {$invoice->client->name}\n";
                    $text .= "   💰 {$invoice->total}€ - 📅 {$invoice->date}\n";
                    $text .= "   📊 Status: " . $this->getInvoiceStatus($invoice) . "\n";
                    if ($invoice->quote_id) {
                        $text .= "   🔗 Basée sur devis #{$invoice->quote_id}\n";
                    }
                    $text .= "\n";
                }
            }

            if (empty($quotes) && empty($invoices)) {
                $text .= "Aucun devis ou facture trouvé.";
            }

            $text .= $this->getStatistics($quotes, $invoices);

        } catch (\Exception $e) {
            $text = "❌ Erreur lors de la récupération des données : " . $e->getMessage();
        }

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

    private function getQuoteStatus($quote)
    {
        if ($quote->invoices()->exists()) {
            return "✅ Facturé";
        }
        $createdDate = \Carbon\Carbon::parse($quote->date);
        $now = \Carbon\Carbon::now();
        if ($createdDate->diffInDays($now) > 30) {
            return "⏰ Expiré";
        }
        return "⏳ En attente";
    }

    private function getInvoiceStatus($invoice)
    {
        switch ($invoice->status) {
            case 'paid':
                return "✅ Payée";
            case 'sent':
                return "📤 Envoyée";
            case 'draft':
                return "📝 Brouillon";
            case 'overdue':
                return "⚠️ En retard";
            default:
                return "❓ Inconnu";
        }
    }

    private function getStatistics($quotes, $invoices)
    {
        $stats = "\n📊 **Statistiques** :\n";
        $quotesCount = count($quotes);
        $invoicesCount = count($invoices);
        $stats .= "• {$quotesCount} devis récents\n";
        $stats .= "• {$invoicesCount} factures récentes\n";

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