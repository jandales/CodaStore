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
    private $perpage;

    public function __construct(ShopServices $services)
    {
        $this->services = $services;
        $this->perpage = config('setting.shop.perpage');
    }

    public function index()
    {  
        $products = $this->services->list();
        return view('shop')->with(['products' => $products, 'category' => 'All Collection']);
    }

    public function category(Category $category)
    {    
        $products = $this->services->category($category);     
        return view('shop')->with(['products' => $products, 'category' => $category->name]);
    }

    public function view(Product $product)
    {           
        return view('product')->with('product',$product);
    }

    public function sortBy($value)
    {
        $products = $this->services->sortBy($value);
        if($products == null)  return redirect()->route('shop');             
        return view('shop')->with(['products'  => $products, 'category' => 'All Collection']);
    }    
    
    public function hasVariants(Product $product)
    {
        return $this->services->hasVariants($product);      
    }
   
}
