<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Support\Collection;

/**
 * What is running low: each variant at or below the threshold ("Overshirt (M)"), and products
 * without variants whose stock is at or below it. Used by the dashboard card, the inventory
 * "Low stock" filter and the daily email.
 */
class LowStock
{
    /**
     * @return Collection<object{name: string, qty: int, product: Product, sku: ?ProductSku}>  lowest first
     */
    public static function items(int $threshold, ?int $limit = null): Collection
    {
        $variants = ProductSku::with('product.stock')
            ->where('qty', '<=', $threshold)
            ->whereHas('product', fn ($q) => $q->published())
            ->get()
            ->map(fn ($sku) => (object) [
                'name' => $sku->product->name . ' (' . $sku->label() . ')',
                'qty' => max(0, (int) $sku->qty),
                'product' => $sku->product,
                'sku' => $sku,
            ]);

        $products = Product::published()->with('stock')
            ->whereDoesntHave('skus')
            ->whereHas('stock', fn ($q) => $q->where('qty', '<=', $threshold))
            ->get()
            ->map(fn ($product) => (object) [
                'name' => $product->name,
                'qty' => $product->stockQty(),
                'product' => $product,
                'sku' => null,
            ]);

        $items = $variants->concat($products)->sortBy(fn ($item) => sprintf('%08d-%s', $item->qty, $item->name))->values();

        return $limit ? $items->take($limit)->values() : $items;
    }
}
