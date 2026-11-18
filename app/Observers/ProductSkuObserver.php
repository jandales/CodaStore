<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductSku;

/** Activity log for variant stock and price changes made by staff, e.g. "Stock for Overshirt (M): 5 → 3". */
class ProductSkuObserver
{
    public function updated(ProductSku $sku)
    {
        if (!ActivityLog::byAdmin()) {
            return;
        }

        $product = $sku->product;
        $name = ($product ? $product->name : 'Product') . ' (' . $sku->label() . ')';

        if ($sku->wasChanged('qty')) {
            $from = (int) $sku->getOriginal('qty');
            $to = (int) $sku->qty;
            if ($from !== $to) {
                $diff = $to - $from;
                ActivityLog::record('stock.changed', $product ?: $sku, 'Stock for ' . $name . ': ' . $from . ' → ' . $to . ' (' . ($diff > 0 ? '+' : '') . $diff . ')', ['sku_id' => $sku->id, 'from' => $from, 'to' => $to]);
            }
        }

        if ($sku->wasChanged('price')) {
            $money = fn ($v) => $v === null ? 'product price' : '₱' . number_format((float) $v, 2);
            ActivityLog::record('product.updated', $product ?: $sku, $name . ': price ' . $money($sku->getOriginal('price')) . ' → ' . $money($sku->price), ['sku_id' => $sku->id]);
        }
    }
}
