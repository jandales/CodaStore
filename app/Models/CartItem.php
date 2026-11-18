<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'attributes',
        'sku_id',
    ];

    /** Changing an item marks the bag as recently used (for bag reminders). */
    protected $touches = ['cart'];

    protected $appends = ['unit_price'];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** The variant chosen (size / colour), for products with variants. */
    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    /** Price of one unit: the variant's price, or the product's. */
    public function unitPrice(): float
    {
        return $this->sku ? $this->sku->effectivePrice() : $this->product->effectivePrice();
    }

    /** For the bag drawer (JSON). */
    public function getUnitPriceAttribute(): float
    {
        return $this->unitPrice();
    }

    /** How many can be bought: the variant's stock, or the product's. */
    public function availableQty(): int
    {
        return $this->sku ? max(0, (int) $this->sku->qty) : max(0, (int) optional($this->product->stock)->qty);
    }

    public function subtotal()
    {
        return $this->unitPrice() * $this->qty;
    }







}
