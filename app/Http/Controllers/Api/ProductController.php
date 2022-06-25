<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{

    private $FEATURED = "featured";
    private $TITLE_ASCENDING = "title-ascending";
    private $TITLE_DESCENDING =  "title-descending";
    private $PRICE_ASCENDING = "price-ascending";
    private $PRICE_DESCENDING = "price-descending";
    private $CREATE_ASCENDING = "created-ascending";
    private $CREATE_DESCENDING = "created-descending";

    public function index($filter = null)
    {         
        $products = Product::with(['category', 'stock'])                            
                            ->when($filter != null, function($query) use($filter) {
                                $query->whereHas('category', function ($q) use ($filter) {                                   
                                    if ($filter != 'all') {
                                        $q->where('name', $filter); 
                                    };                                                                              
                                });                   
                            })->paginate(20);  

        return response()->json(['products' => $products]);
    }

    public function collection()
    {
        $collection = Category::get();
        return response()->json(['collection' => $collection]);
    }

    public function featured($limit= null)
    {        
        if ($limit != null) {
            return Product::with('category','stock')->where(['status' => 1, 'featured' => 1])->take($limit)->get();
        }
        return Product::with('category','stock')->where(['status' => 1, 'featured' => 1])->paginate(20);  
    }

    public function sort($collectionName, $sort)
    {        
        $condition = Self::queryString($sort);

        $products = Product::with(['category', 'stock'])
                    ->when($collectionName != null, function($query) use ($collectionName, $condition){ 
                        $query->whereHas('category', function ($q) use ($collectionName) {                                   
                           if( $collectionName != 'all'){
                                $q->where('name', $collectionName);
                           }                                                                            
                        });     
                    })
                    ->when($collectionName != null, function($q) use ($collectionName, $condition, $sort) {
                        if ($sort == $this->FEATURED) {                            
                            return $q->where($condition['name'], $condition['value']);
                        }
                        return $q->orderBy($condition['name'], $condition['value']);
                    })
                    ->paginate(20);

        return  response()->json(['products' => $products]);

        
    }

    public function show($slug)
    {
        $product = Product::with('category','photos','stock','stock','variants','attributes','reviews','attributes.attributes')->where('slug',$slug)->first();

        return response()->json(['product' => $product]);        
       
    }


    public function queryString($sort)
    {    
        switch ($sort) {  
            case $sort === $this->TITLE_ASCENDING :              
                return ['name' => 'name', 'value' => 'ASC'];               
            case $sort === $this->TITLE_DESCENDING :
                return ['name' => 'name', 'value' => 'DESC'];                
            case $sort === $this->PRICE_ASCENDING :
                return ['name' => 'regular_price', 'value' => 'ASC'];             
            case $sort === $this->PRICE_DESCENDING :
                return ['name' => 'regular_price', 'value' => 'DESC'];                 
            case $sort === $this->CREATE_ASCENDING : 
                return ['name' => 'created_at', 'value' => 'ASC'];             
            case $sort === $this->CREATE_DESCENDING : 
                return ['name' => 'created_at', 'value' => 'DESC'];             
            default :
                return ['name' => 'featured', 'value' => 1];                
        } 
       
    }



}
