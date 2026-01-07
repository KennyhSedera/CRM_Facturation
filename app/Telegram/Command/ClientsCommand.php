<?php

namespace App\Telegram\Commands;

use App\Models\Client;
use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Schema;

class ClientsCommand extends Command
{
    protected string $name = 'clients';
    protected string $description = 'Liste les clients avec leurs statistiques';

    public function handle()
    {
        $chatId = $this->getUpdate()->getChat()->getId();
        $this->showClientsList($chatId);
    }

    public function handleCallback($chatId)
    {
        $this->showClientsList($chatId);
    }

    private function showClientsList($chatId = null)
    {
        try {
            if (!Schema::hasTable('clients')) {
                $text = "❌ La table <b>clients</b> n'existe pas encore.\n\n➡️ Veuillez exécuter les migrations.";
                $this->sendMessage($text, $chatId);
                return;
            }

            $clients = Client::withCount(['quotes', 'invoices'])
                ->orderBy('name')
                ->limit(15)
                ->get();

            if ($clients->isEmpty()) {
                $this->sendMessage("Aucun client trouvé pour le moment.", $chatId);
                return;
            }

            $totalDevis = 0;
            $totalFactures = 0;
            $mostActiveClient = null;
            $mostActivityScore = 0;

            $text = "<b>👥 Liste des clients</b>\n\n";

            foreach ($clients as $client) {
                $score = $client->quotes_count + $client->invoices_count;
                if ($score > $mostActivityScore) {
                    $mostActivityScore = $score;
                    $mostActiveClient = $client;
                }

                $totalDevis += $client->quotes_count;
                $totalFactures += $client->invoices_count;

                $text .= "👤 <b>{$client->name}</b>\n";
                $text .= "📧 {$client->email}\n";
                $text .= "📱 {$client->phone}\n";
                $text .= "📊 <b>{$client->quotes_count}</b> devis | <b>{$client->invoices_count}</b> factures\n";
                $text .= "🔗 <code>/client {$client->name}</code>\n\n";
            }

            $text .= "\n📈 <b>Statistiques générales :</b>\n";
            $text .= "• {$clients->count()} clients au total\n";
            $text .= "• {$totalDevis} devis créés\n";
            $text .= "• {$totalFactures} factures émises\n";
            $text .= "• Client le plus actif : <b>" . ($mostActiveClient?->name ?? '-') . "</b>";

            $this->sendMessage($text, $chatId);
        } catch (\Throwable $e) {
            $this->sendMessage("❌ Erreur : " . $e->getMessage(), $chatId);
        }
    }

    private function sendMessage(string $text, $chatId)
    {
        if ($chatId) {
            \Telegram\Bot\Laravel\Facades\Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } else {
            $this->replyWithMessage([
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        }
    }
}
