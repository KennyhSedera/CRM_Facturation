<?php

namespace App\Telegram\Commands;

use App\Models\Invoice;
use App\Models\Quote;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class FacturePendingCommand extends Command
{
    protected string $name = 'facture_pending';
    protected string $description = 'Afficher les factures en attente';

    public function handle()
    {
        $this->showPendingFactures();
    }

    public function handleCallback($chatId)
    {
        $this->showPendingFactures($chatId);
    }

    private function showPendingFactures($chatId = null)
    {
        try {
            $quotes = Quote::with(['client'])
                ->whereDoesntHave('invoices')
                ->latest('date')
                ->limit(10)
                ->get();

            $invoices = Invoice::with(['client', 'quote'])
                ->whereIn('status', ['sent', 'draft'])
                ->latest('date')
                ->limit(10)
                ->get();

            $text = "⏳ **Factures et devis en attente** :\n\n";

            if (!empty($quotes)) {
                $text .= "📋 **DEVIS EN ATTENTE** :\n";
                foreach ($quotes as $quote) {
                    $text .= "🔹 Devis #{$quote->id} - {$quote->client->name}\n";
                    $text .= "   💰 {$quote->total}€ - 📅 {$quote->date}\n";
                    $text .= "   📊 Status: " . $this->getQuoteStatus($quote) . "\n\n";
                }
            }

            if (!empty($invoices)) {
                $text .= "🧾 **FACTURES EN ATTENTE** :\n";
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
                $text .= "Aucune facture ou devis en attente.";
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
            case 'sent':
                return "📤 Envoyée";
            case 'draft':
                return "📝 Brouillon";
            default:
                return "❓ Inconnu";
        }
    }

    private function getStatistics($quotes, $invoices)
    {
        $stats = "\n📊 **Statistiques** :\n";
        $quotesCount = count($quotes);
        $invoicesCount = count($invoices);
        $stats .= "• {$quotesCount} devis en attente\n";
        $stats .= "• {$invoicesCount} factures en attente\n";

        if ($quotesCount > 0) {
            $totalQuotes = $quotes->sum('total');
            $stats .= "• Total devis en attente : {$totalQuotes}€\n";
        }

        if ($invoicesCount > 0) {
            $totalInvoices = $invoices->sum('total');
            $stats .= "• Total factures en attente : {$totalInvoices}€\n";
        }

        return $stats;
    }
} 