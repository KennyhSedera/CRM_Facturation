<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function getClientDetails(int $clientId): string
    {
        $client = Client::with([
            'quotes' => fn ($q) => $q->latest()->limit(2),
            'invoices' => fn ($q) => $q->latest()->limit(2),
        ])->find($clientId);

        if (!$client) {
            return "❌ Client introuvable.";
        }

        $text = "<b>{$client->name}</b>\n";
        $text .= "📧 <i>{$client->email}</i>\n";
        $text .= "📞 {$client->phone}\n";
        $text .= $client->address ? "🏠 {$client->address}\n" : '';
        $text .= $client->created_at ? "🗓️ Ajouté le : " . $client->created_at->format('d/m/Y') . "\n" : '';

        if ($client->quotes->count()) {
            $text .= "\n<b>📝 Derniers devis :</b>\n";
            foreach ($client->quotes as $quote) {
                $text .= "• #{$quote->id} - {$quote->created_at->format('d/m/Y')}\n";
            }
        }

        if ($client->invoices->count()) {
            $text .= "\n<b>💵 Dernières factures :</b>\n";
            foreach ($client->invoices as $invoice) {
                $text .= "• #{$invoice->id} - {$invoice->created_at->format('d/m/Y')}\n";
            }
        }

        return $text;
    }
}
