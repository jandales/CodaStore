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
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->regular_price;
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
        return "(CASE WHEN {$table}.on_sale = 1 AND {$table}.sale_price > 0 AND {$table}.sale_price < {$table}.regular_price"
            . " THEN {$table}.sale_price ELSE {$table}.regular_price END)";
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
