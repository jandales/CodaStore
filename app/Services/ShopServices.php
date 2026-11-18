<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\BaseServices;


class ShopServices extends BaseServices
{
    /** Sort options shown in the shop: key => label. */
    public const SORTS = [
        'newest' => 'Newest',
        'featured' => 'Featured',
        'price-asc' => 'Price: low to high',
        'price-desc' => 'Price: high to low',
        'name-asc' => 'Name: A to Z',
        'name-desc' => 'Name: Z to A',
        'oldest' => 'Oldest',
    ];

    /** Old /shop/sort-by/{value} keys mapped to the new sort keys. */
    public const LEGACY_SORTS = [
        'all' => 'newest', 'featured-product' => 'featured', 'low-to-high' => 'price-asc',
        'high-to-low' => 'price-desc', 'a-z' => 'name-asc', 'z-a' => 'name-desc',
        'new-to-old' => 'newest', 'old-to-new' => 'oldest',
    ];

    /**
     * Published products with optional filters, as a paginator that keeps the query string.
     * Filters: size[] (variant names), price_min, price_max, sale=1, in_stock=1, sort.
     */
    public function filter(array $filters, ?Category $category = null)
    {
        $price = Product::effectivePriceSql();
        $query = Product::query()->published()->with('stock');

        if ($category) {
            $query->where('category_id', $category->id);
        }

        $sizes = array_values(array_filter((array) ($filters['size'] ?? []), 'strlen'));
        if ($sizes) {
            $query->where(function ($q) use ($sizes) {
                // Products with variants: that size must have stock.
                $q->whereHas('skus', function ($sku) use ($sizes) {
                    $sku->where('qty', '>', 0)->where(function ($match) use ($sizes) {
                        foreach ($sizes as $size) {
                            $segment = 'size:' . mb_strtolower(trim($size));
                            $match->orWhere('option_key', $segment)
                                ->orWhere('option_key', 'like', $segment . '|%')
                                ->orWhere('option_key', 'like', '%|' . $segment)
                                ->orWhere('option_key', 'like', '%|' . $segment . '|%');
                        }
                    });
                })
                // Products without variants: the size is listed.
                ->orWhere(function ($plain) use ($sizes) {
                    $plain->whereDoesntHave('skus')->whereHas('variants', fn ($v) => $v->whereIn('name', $sizes));
                });
            });
        }

        if (is_numeric($filters['price_min'] ?? null)) {
            // CAST keeps the comparison numeric on every database (SQLite compares bound values as text).
            $query->whereRaw("$price >= CAST(? AS DECIMAL(12,2))", [(float) $filters['price_min']]);
        }
        if (is_numeric($filters['price_max'] ?? null)) {
            $query->whereRaw("$price <= CAST(? AS DECIMAL(12,2))", [(float) $filters['price_max']]);
        }

        if (!empty($filters['sale'])) {
            $query->where('on_sale', 1)->where('sale_price', '>', 0)->whereColumn('sale_price', '<', 'regular_price');
        }

        if (!empty($filters['in_stock'])) {
            $query->whereHas('stock', function ($q) {
                $q->where('qty', '>', 0);
            });
        }

        switch ($filters['sort'] ?? 'newest') {
            case 'featured':
                $query->orderByDesc('featured')->latest();
                break;
            case 'price-asc':
                $query->orderByRaw("$price asc");
                break;
            case 'price-desc':
                $query->orderByRaw("$price desc");
                break;
            case 'name-asc':
                $query->orderBy('name');
                break;
            case 'name-desc':
                $query->orderByDesc('name');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
        }

        return $query->orderBy('id')->paginate($this->shop_perpage)->withQueryString();
    }

    /** Size names that exist on published products, in a sensible order (S, M, L, XL, then others). */
    public static function availableSizes(): array
    {
        $order = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL'];
        $names = \App\Models\ProductVariant::query()
            ->whereIn('product_id', Product::where('status', 1)->select('id'))
            ->distinct()
            ->pluck('name')
            ->filter()
            ->all();

        usort($names, function ($a, $b) use ($order) {
            $ia = array_search(strtoupper($a), $order);
            $ib = array_search(strtoupper($b), $order);
            if ($ia === false && $ib === false) return strnatcasecmp($a, $b);
            if ($ia === false) return 1;
            if ($ib === false) return -1;
            return $ia <=> $ib;
        });

        return array_values($names);
    }

    public function hasVariants(Product $product)
    {
        $count = $product->hasVariants();
        $attributes = [];
        $hasvariants = false;

        foreach ( $product->attributes as $item ) {
          array_push( $attributes, $item->attributes->name );
        }

        if ( $count > 0 ) $hasvariants = true;

        return response()->json(['attributes' => $attributes, 'hasvariant' => $hasvariants]);
    }
}