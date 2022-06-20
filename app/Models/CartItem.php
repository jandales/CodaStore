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
        return $this->product->regular_price * $this->qty;
    }



    

 
    
}
