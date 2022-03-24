<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Variant;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'variant',
    ];


    public function products()
    {
        return $this->hasone(Product::class, 'id' , $primaryKey);
    }

    public function varaints()
    {

        return $this->belongsTo(Variant::class, 'variant_id', 'id');

    }

    public function attributes(){ 
        return $this->hasMany(ProductAttribute::class);
    }

    public function scopeExist($query,$product,$variant)
    {   
        return $query->where('product_id', $product)->where('variant_id', $variant);                 
                
    }


}
