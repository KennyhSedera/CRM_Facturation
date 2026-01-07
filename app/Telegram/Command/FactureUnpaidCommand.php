<?php

namespace App\Telegram\Commands;

use App\Models\Invoice;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class FactureUnpaidCommand extends Command
{
    protected string $name = 'facture_unpaid';
    protected string $description = 'Afficher les factures impayées';

    public function handle()
    {
        $this->showUnpaidFactures();
    }

    public function handleCallback($chatId)
    {
        $this->showUnpaidFactures($chatId);
    }

    private function showUnpaidFactures($chatId = null)
    {
        try {
            $invoices = Invoice::with(['client', 'quote'])
                ->whereIn('status', ['sent', 'overdue'])
                ->latest('date')
                ->limit(15)
                ->get();

            $text = "⚠️ **Factures impayées** :\n\n";

            if (!empty($invoices)) {
                foreach ($invoices as $invoice) {
                    $statusIcon = $invoice->status === 'overdue' ? '🚨' : '📤';
                    $statusText = $invoice->status === 'overdue' ? 'En retard' : 'Envoyée';
                    
                    $text .= "🔸 Facture #{$invoice->id} - {$invoice->client->name}\n";
                    $text .= "   💰 {$invoice->total}€ - 📅 {$invoice->date}\n";
                    $text .= "   📊 Status: {$statusIcon} {$statusText}\n";
                    if ($invoice->quote_id) {
                        $text .= "   🔗 Basée sur devis #{$invoice->quote_id}\n";
                    }
                    $text .= "\n";
                }
            } else {
                $text .= "Aucune facture impayée trouvée.";
            }

            $text .= $this->getStatistics($invoices);

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

    private function getStatistics($invoices)
    {
        $stats = "\n📊 **Statistiques** :\n";
        $invoicesCount = count($invoices);
        $stats .= "• {$invoicesCount} factures impayées\n";

        if ($invoicesCount > 0) {
            $totalInvoices = $invoices->sum('total');
            $stats .= "• Total factures impayées : {$totalInvoices}€\n";
            
            // Factures en retard
            $overdueInvoices = $invoices->where('status', 'overdue');
            $overdueCount = $overdueInvoices->count();
            $overdueTotal = $overdueInvoices->sum('total');
            
            if ($overdueCount > 0) {
                $stats .= "• Factures en retard : {$overdueCount} ({$overdueTotal}€)\n";
            }
            
            // Facture la plus élevée
            $maxInvoice = $invoices->max('total');
            $stats .= "• Facture la plus élevée : {$maxInvoice}€\n";
        }

        return $stats;
    }
} 