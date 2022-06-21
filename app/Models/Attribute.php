<?php

namespace App\Models;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'name', 
        'description',
        'slug'    
    ];

    function variants()
    {
        return $this->hasMany(Variant::class);
    }
  

    public function scopeExist($query,$name)
    {   
        return $query->where('name', $name);               
                
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name','like','%' . $keyword . '%')                  
                     ->orWhere('slug','like','%' . $keyword . '%');
    }
    // protected $fillable = [
    //     'variant_id',
    //     'value',
          
    // ];

    // public function scopeExist($query,$value)
    // {   
    //     return $query->where('value', $value );                 
                
    // }

    // public function productAttribute(){

    //     return $this->hasMany(ProductAttribute::class);

    // }

    
    
    // public function scopeProducts($query, $value)
    // {
    //     $results = $query->where('value',$value)->first();
   
    //     $products = array();

    //     if($results ==  null){
    //         return $products;
    //     }

    //     foreach($results->productAttribute as $productAttribute )
    //     {
    //        array_push( $products, $productAttribute->product);
    //     }
  
    //     return $products;
    // }

}
