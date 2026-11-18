<?php

namespace App\Services\Products;

use App\Models\OrderProduct;
use App\Models\ProductSku;
use App\Models\Stock;

/**
 * Puts stock back when an order is cancelled, returned or not collected: into the exact
 * variant that was sold (and the product total), or into the product for products without
 * variants.
 */
class StockLedger
{
    public static function restore(OrderProduct $item): void
    {
        $qty = (int) $item->qty;
        if ($qty <= 0) {
            return;
        }

        if ($item->sku_id && ProductSku::whereKey($item->sku_id)->exists()) {
            ProductSku::whereKey($item->sku_id)->increment('qty', $qty);
        }

        Stock::where('product_id', $item->product_id)->increment('qty', $qty);
    }
}
