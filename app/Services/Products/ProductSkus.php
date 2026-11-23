<?php

namespace App\Services\Products;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;

/**
 * Saves a product's variants from the product form and keeps the product's totals in step:
 * stock = sum of variant stock, price_from / price_to = lowest / highest variant price.
 */
class ProductSkus
{
    public const MAX_OPTIONS = 2;
    public const MAX_VARIANTS = 100;

    /**
     * @param  array  $rows  [['options' => {"size":"M"} (array or JSON), 'price' => ?, 'qty' => int, 'image' => ?, 'sku' => ?, 'barcode' => ?], ...]
     */
    public function sync(Product $product, array $rows): void
    {
        $keep = [];
        foreach (array_values($rows) as $position => $row) {
            $options = is_string($row['options'] ?? null) ? json_decode($row['options'], true) : ($row['options'] ?? []);
            if (!is_array($options) || !$options) {
                continue;
            }
            $key = ProductSku::keyFor($options);
            $price = isset($row['price']) && $row['price'] !== '' && $row['price'] !== null ? round((float) $row['price'], 2) : null;

            $sku = ProductSku::updateOrCreate(
                ['product_id' => $product->id, 'option_key' => $key],
                [
                    'options' => $options,
                    // A price equal to the product's price is stored as "same as product".
                    'price' => $price !== null && $price != (float) $product->regular_price ? $price : null,
                    'qty' => max(0, (int) ($row['qty'] ?? 0)),
                    'image' => ($row['image'] ?? null) ?: null,
                    'sku' => ($row['sku'] ?? null) ?: null,
                    'barcode' => ($row['barcode'] ?? null) ?: null,
                    'position' => $position,
                ]
            );
            $keep[] = $sku->id;
        }

        $this->removeExcept($product, $keep);
        self::refreshTotals($product);
    }

    /** The product no longer has options: drop its variants (bag lines for them too). */
    public function clear(Product $product): void
    {
        $this->removeExcept($product, []);
        $product->forceFill(['price_from' => null, 'price_to' => null])->saveQuietly();
    }

    /** Stock total and price range from the variants. */
    public static function refreshTotals(Product $product): void
    {
        $skus = ProductSku::where('product_id', $product->id)->get();
        if ($skus->isEmpty()) {
            return;
        }

        $prices = $skus->map(fn ($s) => $s->price !== null ? (float) $s->price : (float) $product->regular_price);
        $product->forceFill([
            'price_from' => $prices->min(),
            'price_to' => $prices->max(),
        ])->saveQuietly();

        $stock = Stock::firstOrNew(['product_id' => $product->id]);
        $stock->qty = (int) $skus->sum('qty');
        $stock->damaged_qty = (int) $skus->sum('damaged_qty');
        $stock->save();
    }

    private function removeExcept(Product $product, array $keepIds): void
    {
        $removed = ProductSku::where('product_id', $product->id)->whereNotIn('id', $keepIds)->pluck('id');
        if ($removed->isNotEmpty()) {
            // Bag lines for a variant that no longer exists can't be bought any more.
            CartItem::whereIn('sku_id', $removed)->delete();
            ProductSku::whereIn('id', $removed)->delete();
        }
    }
}
