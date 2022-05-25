<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Services\ShopServices;
use App\Models\ProductAttribute;

class ShopController extends Controller
{
    private $services;

    public function __construct(ShopServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {  
        $products = Product::with('reviews')->paginate(16);
        return view('shop')->with(['products' => $products, 'category' => 'All Collection']);
    }

    public function category(Category $category)
    {    
        $products = Product::where('category_id', $category->id)->with('reviews')->paginate(16);       
        return view('shop')->with(['products' => $products, 'category' => $category->name]);
    }

    public function view(Product $product)
    {           
        return view('product')->with('product',$product);
    }

    public function sortBy($value)
    {
        $products = $this->services->sortBy($value);
        if($products == null)
            return redirect()->route('shop');  
        return view('shop')->with(['products'  => $products, 'category' => 'All Collection']);
    }   

 
    
    public function hasVariants(Product $product)
    {
        return $this->services->hasVariants($product);      
    }
   
}
