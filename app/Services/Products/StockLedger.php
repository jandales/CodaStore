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
    /** @param string $reason cancel | return | not_collected (shown in the stock history) */
    public static function restore(OrderProduct $item, string $reason = 'cancel'): void
    {
        $qty = (int) $item->qty;
        if ($qty <= 0) {
            return;
        }

        $skuId = $item->sku_id && ProductSku::whereKey($item->sku_id)->exists() ? (int) $item->sku_id : null;
        if ($skuId) {
            ProductSku::whereKey($skuId)->increment('qty', $qty);
        }

        Stock::where('product_id', $item->product_id)->increment('qty', $qty);

        StockMovements::record((int) $item->product_id, $skuId, $qty, $reason, ['order_id' => $item->order_id]);
    }
}
