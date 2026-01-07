<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Product;

class StockCommand extends Command
{
    protected string $name = 'stock';
    protected string $description = 'Afficher le stock';

    public function handle()
    {
        $this->showStock();
    }

    public function handleCallback($chatId)
    {
        $this->showStock($chatId);
    }

    private function showStock($chatId = null)
    {
        // Récupérer les produits depuis la DB
        $products = Product::orderBy('stock', 'asc')->get();

        $text = "📦 **État du stock** :\n\n";

        if ($products->isEmpty()) {
            $text .= "Aucun produit en stock.";
        } else {
            // Grouper par statut de stock
            $stockCritique = $products->where('stock', 0);
            $stockFaible = $products->where('stock', '>', 0)->where('stock', '<=', 5);
            $stockNormal = $products->where('stock', '>', 5)->where('stock', '<=', 20);
            $stockElevé = $products->where('stock', '>', 20);

            // Stock critique (rupture)
            if ($stockCritique->count() > 0) {
                $text .= "🚨 **RUPTURE DE STOCK** :\n";
                foreach ($stockCritique as $product) {
                    $text .= "❌ {$product->name} : **0 unité**\n";
                    $text .= "   💰 Prix : {$product->price}€\n\n";
                }
            }

            // Stock faible
            if ($stockFaible->count() > 0) {
                $text .= "⚠️ **STOCK FAIBLE** :\n";
                foreach ($stockFaible as $product) {
                    $text .= "🟡 {$product->name} : **{$product->stock} unités**\n";
                    $text .= "   💰 Prix : {$product->price}€ - Valeur : " .
                             number_format($product->price * $product->stock, 2) . "€\n\n";
                }
            }

            // Stock normal
            if ($stockNormal->count() > 0) {
                $text .= "✅ **STOCK NORMAL** :\n";
                foreach ($stockNormal as $product) {
                    $text .= "🟢 {$product->name} : **{$product->stock} unités**\n";
                    $text .= "   💰 Prix : {$product->price}€ - Valeur : " .
                             number_format($product->price * $product->stock, 2) . "€\n\n";
                }
            }

            // Stock élevé
            if ($stockElevé->count() > 0) {
                $text .= "📈 **STOCK ÉLEVÉ** :\n";
                foreach ($stockElevé as $product) {
                    $text .= "🔵 {$product->name} : **{$product->stock} unités**\n";
                    $text .= "   💰 Prix : {$product->price}€ - Valeur : " .
                             number_format($product->price * $product->stock, 2) . "€\n\n";
                }
            }

            // Résumé
            $totalValue = $products->sum(function($product) {
                return $product->price * $product->stock;
            });

            $text .= "📊 **RÉSUMÉ** :\n";
            $text .= "• Total produits : {$products->count()}\n";
            $text .= "• Ruptures : {$stockCritique->count()}\n";
            $text .= "• Stock faible : {$stockFaible->count()}\n";
            $text .= "• Valeur totale : " . number_format($totalValue, 2) . "€\n";
            $text .= "• Unités totales : " . $products->sum('stock');
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
}
