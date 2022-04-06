<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\WishList;
use Illuminate\Http\Request;

class WishListController extends Controller
{
    public function store(Product $product){ 
  

       if( $product->isWishlisted())
       {
           $wishlist = Wishlist::where('product_id', $product->id)->first();
           $wishlist->delete();
           return response()->json(['status' => 'warning']);  
       } 

        WishList::create([
            'user_id' => auth()->user()->id,
            'product_id' => $product->id,
            'qty' => '12',   
        ]);

        return response()->json([ 'status' => 'success', 'message' => 'Successfully added to your wishlist' ]);
        

    }

    public function update(Wishlist $wishlist, Request $request)
    {   
        
       $product_id = $wishlist->product->id;
       $cart = Cart::Exist($product_id);
       
        if($cart)
        {
            $cart = Cart::where('product_id',$product_id)->first();     
            $cart->qty += $request->qty;
            $cart->save();
          
        }
        else
        {
            $cart = Cart::create([
                'user_id' => auth()->user()->id,
                'product_id' => $wishlist->product->id,
                'product_name' => $wishlist->product->name,
                'price' => $wishlist->product->prices->selling,
                'qty' => $request->qty,
                'properties' => json_decode($request->properties, TRUE),          
            ]);
        }        
       

        if($cart) $wishlist->delete();
       

        return redirect()->route('cart');
    }

    public function destroy(Wishlist $wishlist)
    {
        $wishlist->delete();
        return back()->with('success', 'Wishlit product successfully deleted');
    }

    public function destroyAll(Request $request){
        $items = json_decode($request->items, TRUE); 
        foreach($items as $id)
        {
            $wishlist = Wishlist::find($id)->first();
            $wishlist->delete();
        
        return back()->with('success', 'Wishlit product successfully deleted');
        }
    }

    public function index()
    {
        $wishlists = auth()->user()->wishlist;
        return view('account.wishlist')->with('wishlists',$wishlists);         
    
    }

    public function addtoCart(Wishlist $wishlist)
    {       
       return response()->json([
           'view' => SELF::returnView($wishlist), 
           'id' => $wishlist->product_id
       ]);
    }
   
    public function returnView(Wishlist  $wishlist)
    {
        return view('product-overview')->with([
            'product' => $wishlist->product, 
            'wishlist' => $wishlist
        ])->render();
    }

    public function count()
    {         
        return response()->json(['wishlists' => WishList::ByAuthUser()->count() ]);
    }

    public function list()
    {         
        $wishlists = WishList::ByAuthUser();
        $total = 0;
        foreach($wishlists as $item)
        {
            $total += $item->qty * $item->product->price;
        }
        return response()->json(['wishlists' => $wishlists, 'total' => $total ]);
    }

    public function delete(Wishlist $wishlist)
    {
        $wishlist->delete();
        return response()->json(['success' =>  'Wishlit product successfully deleted']);
    }




  
    


    
}
