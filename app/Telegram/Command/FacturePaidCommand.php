<?php

namespace App\Telegram\Commands;

use App\Models\Invoice;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class FacturePaidCommand extends Command
{
    protected string $name = 'facture_paid';
    protected string $description = 'Afficher les factures payées';

    public function handle()
    {
        $this->showPaidFactures();
    }

    public function handleCallback($chatId)
    {
        $this->showPaidFactures($chatId);
    }

    private function showPaidFactures($chatId = null)
    {
        try {
            $invoices = Invoice::with(['client', 'quote'])
                ->where('status', 'paid')
                ->latest('date')
                ->limit(15)
                ->get();

            $text = "✅ **Factures payées** :\n\n";

            if (!empty($invoices)) {
                foreach ($invoices as $invoice) {
                    $text .= "🔸 Facture #{$invoice->id} - {$invoice->client->name}\n";
                    $text .= "   💰 {$invoice->total}€ - 📅 {$invoice->date}\n";
                    $text .= "   📊 Status: ✅ Payée\n";
                    if ($invoice->quote_id) {
                        $text .= "   🔗 Basée sur devis #{$invoice->quote_id}\n";
                    }
                    $text .= "\n";
                }
            } else {
                $text .= "Aucune facture payée trouvée.";
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
        $stats .= "• {$invoicesCount} factures payées\n";

        if ($invoicesCount > 0) {
            $totalInvoices = $invoices->sum('total');
            $stats .= "• Total factures payées : {$totalInvoices}€\n";
            
            // Moyenne par facture
            $average = $totalInvoices / $invoicesCount;
            $stats .= "• Moyenne par facture : " . number_format($average, 2) . "€\n";
            
            // Facture la plus élevée
            $maxInvoice = $invoices->max('total');
            $stats .= "• Facture la plus élevée : {$maxInvoice}€\n";
        }

        return $stats;
    }
} 