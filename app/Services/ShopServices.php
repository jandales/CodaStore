<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\BaseServices;


class ShopServices extends BaseServices
{   
    public function list()
    {  
        return Product::paginate($this->shop_perpage);
       
    }

    public function category(Category $category)
    {    
  
        return Product::where('category_id', $category->id)->paginate($this->shop_perpage);       
       
    }

    public function sortBy($value)
    {
        $column = '';
        $columnValue = '';

        if ( $value == 'all' ) return [];    
        
        if ( $value == 'featured-product' ) {
            return  Product::where('featured', 1)->orderBy('created_at', 'asc')->paginate(16);
        }
      
        switch ( $value )
        {
            case 'a-z':
                $column = 'name';
                $columnValue = 'desc';
            break;

            case  'z-a' : 
                $column = 'name';
                $columnValue = 'asc';
            break;

            case  'new-to-old' : 
                $column = 'created_at';
                $columnValue = 'asc';
            break;

            case  'old-to-new' : 
                $column = 'created_at';
                $columnValue = 'desc';
            break;

            case  'low-to-high' : 
                $column = 'regular_price';
                $columnValue = 'asc';
            break;

            case  'high-to-low' : 
                $column = 'regular_price';
                $columnValue = 'desc';
            break;
          
        };  

        return  Product::orderBy($column,$columnValue)->paginate(16);
        
    }

    public function hasVariants(Product $product)
    {
        $count = $product->hasVariants();
        $attributes = [];
        $hasvariants = false;

        foreach ( $product->attributes as $item ) {
          array_push( $attributes, $item->attributes->name );
        }  

        if ( $count > 0 ) $hasvariants = true;          
        
        return response()->json(['attributes' => $attributes, 'hasvariant' => $hasvariants]);
    }
}