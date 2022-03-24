<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;
          /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'qty',
        'remarks'     
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function minus($qty)
    {
        return $this->product->decrement('qty', $qty);
    }

    public function outOfStock()
    {
        if($this->qty == 0)  return true;      

        return false;

        
    }

   

    
}
