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
    ];

    /** Changing an item marks the bag as recently used (for bag reminders). */
    protected $touches = ['cart'];

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

    public function subtotal()
    {
        return $this->product->effectivePrice() * $this->qty;
    }







}
