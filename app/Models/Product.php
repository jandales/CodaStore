<?php

namespace App\Models;

use App\Models\GeneralSetting;

use App\Models\Cart;
use App\Models\Stock;
use App\Models\Variant;
use App\Models\Category;
use App\Http\Traits\Crypted;
use App\Models\ProductAttribute;
use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{

    use HasFactory, SoftDeletes, DateTimeFormat, Crypted;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $appends = ['effective_price'];

    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'short_description',
        'imagePath',
        'long_description',
        'sku',
        'barcode',
        'tags',
        'sale_price',
        'regular_price',
        'on_sale',
        'status',
        'is_taxable',
        'featured',
        'admin_id'
    ];

    // protected $attributes = [
    //     'is_taxable' => 0,
    // ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** effective_price in JSON responses (used by the side-cart drawer). */
    public function getEffectivePriceAttribute(): float
    {
        return $this->effectivePrice();
    }

    /** On sale only when the admin switched it on and the sale price is below the regular price. */
    public function isOnSale(): bool
    {
        return (bool) $this->on_sale
            && (float) $this->sale_price > 0
            && (float) $this->sale_price < (float) $this->regular_price;
    }

    /**
     * The price a shopper pays for one unit right now. Every cart, checkout and order total
     * reads this, so sale pricing lives in one place.
     */
    public function effectivePrice(): float
    {
        // Products with variants: the lowest variant price ("From ₱X"), with the sale applied.
        if ($this->price_from !== null) {
            return round((float) $this->price_from * $this->saleRatio(), 2);
        }

        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->regular_price;
    }

    /** Lowest price before any sale (the "was" price on cards). */
    public function basePrice(): float
    {
        return $this->price_from !== null ? (float) $this->price_from : (float) $this->regular_price;
    }

    /** Fraction of the price a shopper pays: 1.0, or e.g. 0.8 for 20% off. Applies to every variant. */
    public function saleRatio(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price / (float) $this->regular_price : 1.0;
    }

    /** Variants cost different amounts, so lists show "From ₱X". */
    public function hasVariablePrice(): bool
    {
        return $this->price_from !== null && $this->price_to !== null && (float) $this->price_to > (float) $this->price_from;
    }

    /** Whole-number percentage off, e.g. 20 for 20% off; 0 when not on sale. */
    public function discountPercent(): int
    {
        if (!$this->isOnSale()) {
            return 0;
        }

        return (int) round((1 - (float) $this->sale_price / (float) $this->regular_price) * 100);
    }

    /** SQL for the effective price, for sorting and filtering in queries. */
    public static function effectivePriceSql(string $table = 'products'): string
    {
        $onSale = "{$table}.on_sale = 1 AND {$table}.sale_price > 0 AND {$table}.sale_price < {$table}.regular_price";

        // Variants: lowest variant price, with the sale percentage applied.
        return "(CASE WHEN {$table}.price_from IS NULL"
            . " THEN (CASE WHEN {$onSale} THEN {$table}.sale_price ELSE {$table}.regular_price END)"
            . " ELSE (CASE WHEN {$onSale} THEN ROUND({$table}.price_from * {$table}.sale_price * 1.0 / {$table}.regular_price, 2) ELSE {$table}.price_from END)"
            . " END)";
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'id', 'product_id');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** Sellable variants (option combinations) with their own price, stock and photo. */
    public function skus()
    {
        return $this->hasMany(ProductSku::class)->orderBy('position')->orderBy('id');
    }

    public function hasSkus(): bool
    {
        return $this->relationLoaded('skus') ? $this->skus->isNotEmpty() : $this->skus()->exists();
    }

    /** The variant matching the shopper's choices, e.g. ['size' => 'M'], or null. */
    public function skuFor(array $options): ?ProductSku
    {
        if (!$options) {
            return null;
        }
        $key = ProductSku::keyFor($options);

        return $this->relationLoaded('skus')
            ? $this->skus->firstWhere('option_key', $key)
            : $this->skus()->where('option_key', $key)->first();
    }

    public function hasVariants()
    {
        return $this->variants->count();
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Whether the signed-in customer saved this product (false for guests). */
    public function isWishlisted(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->wishlist->contains('user_id', auth()->id());
    }

    public function reviewby(User $user)
    {
        return $this->reviews->contains('user_id', $user->id);
    }

    public function rating()
    {
        $result = $this->reviews->sum('rating') / 5;
        return round($result);
    }


    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function hasInCart()
    {
        return $this->carts->where('product_id', $this->id)->contains('user_id',auth()->user()->id);
    }



    public function imageDetail()
    {
        return $this->photos->where('path', $this->imagePath)->first();
    }

    public function status()
    {
        if ($this->status == 0) return 'Draft';
        return 'Published';
    }
    public function scopePublished($query)
    {
       return $query->where('status',1);
    }


    public function scopeSearch($query,$input)
    {
        return $query->where('name','like','%' . $input . '%')
                     ->orWhere('slug','like','%' . $input . '%');

    }

    /**
     * Public product URL that stays the same between visits, e.g. /shop/item/linen-shirt-12.
     * (Encrypted ids change on every page load, which search engines treat as new pages.)
     */
    public function url(): string
    {
        $slug = \Illuminate\Support\Str::slug($this->name) ?: 'product';

        return route('shop.product', [$slug . '-' . $this->id]);
    }

    /** Accepts "name-12" (storefront) or an encrypted id (admin and old links). */
    public function resolveRouteBinding($value, $field = null)
    {
        if (preg_match('/^[a-z0-9-]*-(\d+)$/', (string) $value, $m)) {
            return $this->where('id', $m[1])->firstOrFail();
        }

        try {
            $id = decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        return $this->where('id', $id)->firstOrFail();
    }

    /** Published products in the same category first, then others, for "You may also like". */
    public function related(int $limit = 4)
    {
        $base = static::published()->with('stock')->where('id', '!=', $this->id)
            ->whereHas('stock', fn ($q) => $q->where('qty', '>', 0));

        $related = (clone $base)->where('category_id', $this->category_id)
            ->orderByDesc('featured')->latest()->take($limit)->get();

        if ($related->count() < $limit) {
            $related = $related->concat(
                (clone $base)->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('featured')->latest()->take($limit - $related->count())->get()
            );
        }

        return $related;
    }

    /** Products whose stock is at or below the threshold (sold-out ones included). */
    public function scopeLowStock($query, int $threshold)
    {
        return $query->whereHas('stock', function ($q) use ($threshold) {
            $q->where('qty', '<=', $threshold);
        });
    }

    public function scopeFilterByCategory($query, $filter)
    {
        return $query->with(['category', 'stock'])
                ->whereHas('category', function ($q) use ($filter) {
                    if($filter != 'all'){
                        $q->where('slug', $filter)
                        ->orWhere('name', $filter);
                    }
                });
    }


    public function scopePublishedSearch($query,$input)
    {
        return $query->where('status',1)
                     ->where('name','like','%' . $input . '%')
                     ->orWhere('slug','like','%' . $input . '%');

    }

    public function scopeTags($query, $tag)
    {
        return $query->where('tags','like','%' . $tag . '%');
    }

    public function stockQty(): int
    {
        return max(0, (int) optional($this->stock)->qty);
    }

    public function isSoldOut(): bool
    {
        return $this->stockQty() === 0;
    }

    /** Short stock note for the product page, or null when there is plenty. */
    public function minQty()
    {
        if ($this->isSoldOut()) return 'Sold out';

        if ($this->stockQty() <= GeneralSetting::lowStockThreshold()) return 'Only ' . $this->stockQty() . ' left';

        return null;
    }








}
