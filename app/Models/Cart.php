<?php

namespace App\Models;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    
    protected $fillable = [       
        'cart_id',
        'total',  
        'expired_at',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function coupon(){
        return $this->belongsTo(Coupon::class);
    }



    // public function scopeExist($query,$id)
    // {
    //     $item = $query->where('product_id', $id)->first();
    //     if($item == null) return false;
    //     return true;
    // }

    public function scopeByUser()
    {
        $id = Cookie::get('cart-id');
        return $this->where('cart_id',$id)->with('items','items.product','items.product.stock', 'coupon');
    }

    public function scopeGrandTotal()
    {       
        $cart = self::scopeCartByUser();
        $total = 0;
        foreach($cart->items as $item)
        {
            $total += $item->qty * $item->product->regular_price;
        }
        return $total;
    }

    public function hasThisProduct($id)
    {
        return $this->items->where('product_id', $id)->first();
    }

    public function scopeUpdateTotal()
    {
        $cart = self::scopeByUser()->first();
        $total = 0;
        foreach($cart->items as $item)
        {
            $total += $item->qty * $item->product->regular_price;
        }       
        $cart->total = $total;
        $cart->save();
        return $cart;
    }

    public function scopeTotalItems()
    {
        $cart = self::scopeByUser()->first();  
        return $cart->items->sum('qty') ?? 0;
    }


    public function netTotal()
    {
        return $this->total + $this->discount;
    }

    public function grandTotal()
    {
        $shipping_charge = (double)session()->get('shipping_charge') ?? 0;
        return ($this->total + $shipping_charge) - $this->discount;
    }
    

    public function couponName($name)
    {
        return $this->coupon_id != null ? $this->coupon->$name : '';
    }
 



   

  
    
}
