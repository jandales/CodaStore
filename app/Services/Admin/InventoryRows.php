<?php

namespace App\Services\Admin;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * The inventory page as a flat list: one row per variant ("Overshirt · M"), and one row per
 * product without variants. Filters and search narrow the products; low stock narrows the rows.
 */
class InventoryRows
{
    public const PER_PAGE = 25;

    /**
     * @param  callable|null  $rowFilter  keep only rows for which this returns true
     */
    public static function paginate(Builder $products, ?callable $rowFilter = null, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $rows = self::rows($products->with(['category', 'stock', 'skus'])->orderBy('name')->orderBy('id')->get());
        if ($rowFilter) {
            $rows = $rows->filter($rowFilter)->values();
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }

    /** @return Collection<object> */
    public static function rows(Collection $products): Collection
    {
        return $products->flatMap(function (Product $product) {
            if ($product->skus->isNotEmpty()) {
                return $product->skus->values()->map(fn ($sku, $i) => (object) [
                    'product' => $product,
                    'sku' => $sku,
                    'variant' => $sku->label(),
                    'code' => $sku->sku ?: $product->sku,
                    'qty' => max(0, (int) $sku->qty),
                    'image' => $sku->image ?: $product->imagePath,
                    'saveUrl' => route('admin.inventory.update.variant', [$sku->id]),
                    'firstOfProduct' => $i === 0,
                ]);
            }

            return [(object) [
                'product' => $product,
                'sku' => null,
                'variant' => null,
                'code' => $product->sku,
                'qty' => $product->stockQty(),
                'image' => $product->imagePath,
                'saveUrl' => $product->stock ? route('admin.inventory.update.quantity', [$product->stock->id]) : null,
                'firstOfProduct' => true,
            ]];
        })->values();
    }
}
