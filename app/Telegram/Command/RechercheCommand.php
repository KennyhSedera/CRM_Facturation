<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\Client;
use App\Models\Product;

class RechercheCommand extends Command
{
    protected string $name = 'recherche';
    protected string $description = 'Rechercher un client ou un produit';

    public function handle()
    {
        $this->showSearchInterface();
    }

    public function handleCallback($chatId)
    {
        $this->showSearchInterface($chatId);
    }

    private function showSearchInterface($chatId = null)
    {
        // Créer un clavier avec des options de recherche
        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' => '👥 Rechercher client', 'callback_data' => 'search_client']),
                Keyboard::inlineButton(['text' => '📦 Rechercher produit', 'callback_data' => 'search_product']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => '🔍 Recherche globale', 'callback_data' => 'search_global']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => '🏠 Retour au menu', 'callback_data' => 'start']),
            ]);

        $text = "🔍 **Module de recherche**\n\n";
        $text .= "Que souhaitez-vous rechercher ?\n\n";
        $text .= "• **Client** : Rechercher par nom, email ou téléphone\n";
        $text .= "• **Produit** : Rechercher dans le catalogue\n";
        $text .= "• **Global** : Recherche dans toutes les données\n\n";
        $text .= "Ou tapez directement votre terme de recherche :";

        if ($chatId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard,
            ]);
        } else {
            $this->replyWithMessage([
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard,
            ]);
        }
    }

    /**
     * Rechercher des clients
     */
    public function searchClients($query, $chatId = null)
    {
        $clients = $this->searchClientsInDatabase($query);

        $text = "👥 **Résultats de recherche - Clients** :\n\n";
        $text .= "🔍 Recherche : \"*{$query}*\"\n\n";

        if ($clients->isEmpty()) {
            $text .= "Aucun client trouvé.";
        } else {
            foreach ($clients as $client) {
                // Calculer le CA total du client
                $caTotal = $client->invoices()->where('status', 'paid')->sum('total');
                $nbDevis = $client->quotes()->count();
                $nbFactures = $client->invoices()->count();

                $text .= "🔹 **{$client->name}**\n";
                $text .= "   📧 {$client->email}\n";
                if ($client->phone) {
                    $text .= "   📱 {$client->phone}\n";
                }
                if ($client->address) {
                    $text .= "   📍 " . substr($client->address, 0, 50) . "...\n";
                }
                $text .= "   💰 CA : " . number_format($caTotal, 2) . "€\n";
                $text .= "   📊 {$nbDevis} devis, {$nbFactures} factures\n\n";
            }

            $totalCA = $clients->sum(fn($client) => $client->invoices()->where('status', 'paid')->sum('total'));
            $text .= "📈 **Total CA clients trouvés** : " . number_format($totalCA, 2) . "€";
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

    /**
     * Rechercher des produits
     */
    public function searchProducts($query, $chatId = null)
    {
        $products = $this->searchProductsInDatabase($query);

        $text = "📦 **Résultats de recherche - Produits** :\n\n";
        $text .= "🔍 Recherche : \"*{$query}*\"\n\n";

        if ($products->isEmpty()) {
            $text .= "Aucun produit trouvé.";
        } else {
            foreach ($products as $product) {
                $stock_status = $product->stock > 10 ? '✅' : ($product->stock > 0 ? '⚠️' : '❌');
                $totalVendu = $product->invoiceItems()->sum('quantity');

                $text .= "🔹 **{$product->name}**\n";
                $text .= "   💰 Prix : " . number_format($product->price, 2) . "€\n";
                $text .= "   📦 Stock : {$product->stock} {$stock_status}\n";
                $text .= "   📈 Vendu : {$totalVendu} unités\n";
                if ($product->description) {
                    $text .= "   📝 " . substr($product->description, 0, 60) . "...\n";
                }
                $text .= "\n";
            }

            $stockTotal = $products->sum('stock');
            $valeurStock = $products->sum(fn($p) => $p->stock * $p->price);
            $text .= "📊 **Stock total** : {$stockTotal} unités\n";
            $text .= "💰 **Valeur stock** : " . number_format($valeurStock, 2) . "€";
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

    /**
     * Recherche globale
     */
    public function searchGlobal($query, $chatId = null)
    {
        $clients = $this->searchClientsInDatabase($query);
        $products = $this->searchProductsInDatabase($query);

        $text = "🔍 **Recherche globale** :\n\n";
        $text .= "🔍 Recherche : \"*{$query}*\"\n\n";

        // Clients
        if ($clients->count() > 0) {
            $text .= "👥 **Clients** ({$clients->count()}) :\n";
            foreach ($clients->take(3) as $client) {
                $caTotal = $client->invoices()->where('status', 'paid')->sum('total');
                $text .= "  • {$client->name} - {$client->email} - " . number_format($caTotal, 2) . "€\n";
            }
            if ($clients->count() > 3) {
                $text .= "  • ... et " . ($clients->count() - 3) . " autres\n";
            }
            $text .= "\n";
        }

        // Produits
        if ($products->count() > 0) {
            $text .= "📦 **Produits** ({$products->count()}) :\n";
            foreach ($products->take(3) as $product) {
                $stock_icon = $product->stock > 0 ? '✅' : '❌';
                $text .= "  • {$product->name} - " . number_format($product->price, 2) . "€ {$stock_icon}\n";
            }
            if ($products->count() > 3) {
                $text .= "  • ... et " . ($products->count() - 3) . " autres\n";
            }
            $text .= "\n";
        }

        if ($clients->isEmpty() && $products->isEmpty()) {
            $text .= "Aucun résultat trouvé dans la base de données.";
        } else {
            $text .= "📈 **Résumé** :\n";
            $text .= "• {$clients->count()} clients trouvés\n";
            $text .= "• {$products->count()} produits trouvés";
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

    /**
     * Rechercher des clients dans la base de données
     */
    private function searchClientsInDatabase($query)
    {
        return Client::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('phone', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%")
                    ->with(['quotes', 'invoices'])
                    ->limit(10)
                    ->get();
    }

    /**
     * Rechercher des produits dans la base de données
     */
    private function searchProductsInDatabase($query)
    {
        return Product::where('name', 'LIKE', "%{$query}%")
                     ->orWhere('description', 'LIKE', "%{$query}%")
                     ->with(['invoiceItems'])
                     ->limit(10)
                     ->get();
    }
}
