<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\ProductAttribute;

class ShopController extends Controller
{

    public function index()
    {  
        $products = Product::with('wishlist')->get();
        return view('shop')->with('products' ,$products );
    }

    public function category(Category $category)
    { 
        $products = Product::where('category_id', $category->id)->with('reviews', 'wishlist')->get();
        return view('shop')->with( 'products' , $products );
    }
    public function view(Product $product)
    { 
        return view('product')->with('product',$product);
    }

   

    public function search(Request $request)
    {
        $input = $request->input;  
        $products = Product::Search($input)->with('reviews', 'wishlist')->get();
        return view('shop')->with(['products' => $products ]);

    }

    public function filterByPrice($prices)
    {
             
        $products = Price::Between($prices);
        return view('shop')->with([ 'products' => $products ]);
    }

    public function filterByAttribute($attribute)
    {

       $products = Attribute::Products($attribute);      

       return view('shop')->with([ 'products' => $products ]);

    }

    public function filterByTags($tag){      
        $products = Product::Tags($tag)->with('reviews', 'wishlist')->get();
        return view('shop')->with([ 'products' => $products ]);

    }

    public function filterPriceDesc($prices)
    {
       
        if($prices == 'high')
        {
            $products = Price::OrderbyDesc(); 
            return view('shop')->with(['products' => $products ]);
        }

        if( $prices == 'low')
        {
            $products = Price::OrderbyAsc();
            return view('shop')->with(['products' => $products ]);
        }
       
    }

    public function filterLatest()
    {
       $products = Product::latest()->with('reviews', 'wishlist')->get();
       return view('shop')->with(['products' => $products ]);
    }

    public function hasVariants(Product $product)
    {
        $count = $product->hasVariants();
        $variant = [];
        $hasvariants = false;    
        foreach($product->variants as $item)
        {
          array_push($variant,$item->varaints->name);
        }      
        if($count > 0){
           $hasvariants = true;
        }
        return response()->json(['variants' => $variant, 'hasvariant' => $hasvariants]);
    }

   
}
