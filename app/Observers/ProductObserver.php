<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product;

class ProductObserver
{
    /** Fields worth an audit entry. */
    private const WATCHED = ['name', 'regular_price', 'sale_price', 'on_sale', 'status'];

    public function created(Product $product)
    {
        if (ActivityLog::byAdmin()) {
            ActivityLog::record('product.created', $product, 'Created ' . $product->name . ' at ' . self::money($product->regular_price));
        }
    }

    public function updated(Product $product)
    {
        if (!ActivityLog::byAdmin()) {
            return;
        }

        $changes = [];
        $parts = [];
        foreach (self::WATCHED as $field) {
            if (!$product->wasChanged($field)) {
                continue;
            }
            $from = $product->getOriginal($field);
            $to = $product->getAttribute($field);
            // Ignore type-only changes such as "500" -> 500 or "1" -> true.
            if ($field !== 'name' && (float) $from === (float) $to) {
                continue;
            }
            $changes[$field] = ['from' => $from, 'to' => $to];
            $parts[] = self::describe($field, $from, $to);
        }

        if ($parts) {
            ActivityLog::record('product.updated', $product, $product->name . ': ' . implode('; ', $parts), $changes);
        }
    }

    public function deleted(Product $product)
    {
        if (ActivityLog::byAdmin()) {
            ActivityLog::record('product.deleted', $product, 'Deleted ' . $product->name);
        }
    }

    private static function describe(string $field, $from, $to): string
    {
        switch ($field) {
            case 'regular_price':
                return 'price ' . self::money($from) . ' → ' . self::money($to);
            case 'sale_price':
                return 'sale price ' . self::money($from) . ' → ' . self::money($to);
            case 'on_sale':
                return $to ? 'put on sale' : 'taken off sale';
            case 'status':
                return (int) $to === 1 ? 'published' : 'moved to drafts';
            default:
                return 'renamed from "' . $from . '"';
        }
    }

    private static function money($value): string
    {
        return '₱' . number_format((float) $value, 2);
    }
}
