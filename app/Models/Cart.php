<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $casts = [
        'properties' => 'array'
    ];
    
    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'qty',
        'price',
        'properties'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public  function total()
    {

        // $discount = $this->discount / 100;
        
        $total = $this->price * $this->qty;

        return $total - $this->discount;
    }  

    public function scopeSelected($query)
    {
        return $query->where('user_id', auth()->user()->id)->where('selected', 1);
    }

    public function scopeUnselectAll($query)
    {
        $carts = $this->Selected()->get();

        if($carts->count() != 0)
        {
            foreach($carts as $cart){
                $cart->selected = 0;
                $cart->save();
            }
        }
       

        return $carts;
        
    }


    public function scopeSubtotal()
    {
        return Self::subtotal();     
    }

    public function totalCost()
    {
        return Self::subtotal() + shippingFee();
    }

    public function subtotal()
    {
        $user = auth()->user();
        $carts = Cart::where('user_id', $user->id)->get();
        $subtotal = 0;

        foreach($carts as $cart){
            $subtotal += $cart->total();
        }
        return $subtotal; 
    }

  

    public function scopeExist($query,$id)
    {
        $item = $query->where('product_id', $id)->first();
        if($item == null) return false;
        return true;
    }

    public function scopeCartByAuthUser()
    {
        return $this->where('user_id',auth()->user()->id)->with('product','product.stock')->get(); 
    }


   

  
    
}
