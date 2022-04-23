<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'qty',
        'properties'
    ];

    public function scopeExist($query, $product)
    {
        return $query->where('user_id', auth()->user()->id )
                    ->where('product_id', $product);
    }



    public function product(){

        return $this->belongsTo(Product::class);
    
    }

    public function scopeByAuthUser()
    {
        return $this->where('user_id',auth()->user()->id)->with('product','product.stock'); 
    }
}
