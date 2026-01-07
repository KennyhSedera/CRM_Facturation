<?php
namespace App\Services;

use App\Models\Product;

class StockService
{
    public function getLowStockProducts($threshold = 5)
    {
        return Product::where('stock', '<=', $threshold)->get();
    }

    public function updateStockAfterInvoice($invoice)
    {
        foreach ($invoice->items as $item) {
            $product = $item->product;
            $product->stock -= $item->quantity;
            $product->save();
        }
    }

    public function restockProduct(Product $product, int $quantity)
    {
        $product->stock += $quantity;
        $product->save();
    }
}
