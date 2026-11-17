<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Stock;

class StockObserver
{
    public function updated(Stock $stock)
    {
        if (!ActivityLog::byAdmin() || !$stock->wasChanged('qty')) {
            return;
        }

        $from = (int) $stock->getOriginal('qty');
        $to = (int) $stock->qty;
        if ($from === $to) {
            return;
        }

        $product = $stock->product;
        $name = $product ? $product->name : 'product #' . $stock->product_id;
        $diff = $to - $from;

        ActivityLog::record(
            'stock.changed',
            $product ?: $stock,
            'Stock for ' . $name . ': ' . $from . ' → ' . $to . ' (' . ($diff > 0 ? '+' : '') . $diff . ')',
            ['from' => $from, 'to' => $to]
        );
    }
}
