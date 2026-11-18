<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One variant of a product: a combination of option values (e.g. {"size": "M", "color": "Black"})
 * with its own price, stock and photo. A null price means "same as the product's price".
 */
class ProductSku extends Model
{
    protected $fillable = ['product_id', 'option_key', 'options', 'price', 'qty', 'damaged_qty', 'image', 'sku', 'position'];

    protected $casts = [
        'options' => 'array',
        'qty' => 'integer',
        'damaged_qty' => 'integer',
        'position' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stable key for a set of option values, independent of order and case:
     * {"Size": "M", "color": "Black"} -> "color:black|size:m".
     */
    public static function keyFor(array $options): string
    {
        $parts = [];
        foreach ($options as $name => $value) {
            $parts[mb_strtolower(trim((string) $name))] = mb_strtolower(trim((string) $value));
        }
        ksort($parts);

        return implode('|', array_map(fn ($k, $v) => $k . ':' . $v, array_keys($parts), $parts));
    }

    /** Price before any sale: the variant's own price, or the product's. */
    public function basePrice(): float
    {
        return $this->price !== null ? (float) $this->price : (float) $this->product->regular_price;
    }

    /** The price a shopper pays for one unit; the product's sale takes the same % off every variant. */
    public function effectivePrice(): float
    {
        return round($this->basePrice() * $this->product->saleRatio(), 2);
    }

    /** "M / Black" */
    public function label(): string
    {
        return implode(' / ', array_map('strval', array_values((array) $this->options)));
    }

    public function isSoldOut(): bool
    {
        return (int) $this->qty <= 0;
    }
}
