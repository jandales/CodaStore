<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Attribute;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',       
    ];

    public function attributes()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id','id');        
    }




    public function product()
    {        
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function scopeExist($query,$product,$attribute)
    {   
        return $query->where('product_id', $product)->where('attribute_id',$attribute);                 
                
    } 

}
