<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\WishList;
use App\Models\AddressBook;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {    
        
        Cart::UnselectAll();     
        $carts = Cart::CartByAuthUser(); 
        return view('cart')->with(['carts' =>  $carts]);
    }


    public function carts()
    {
        $carts = Cart::CartByAuthUser();  

        $total = 0;
        foreach($carts as $item)
        {
            $total += $item->qty * $item->price;
        }
        
        return response()->json(['carts' => $carts, 'total' => $total ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product)
    {

        $productQuantity = $product->stock->qty;
        $cartQuantity = $request->qty;
    
        if($product->hasInCart())
        {
            $cart = Cart::where('product_id',$product->id)->first();
            $cart->qty += $cartQuantity;
            $cart->save();
            return response()->json(['status' => 501, 'message' => 'Product is already in your cart'  ]); 
        }            

        if($productQuantity < $cartQuantity)
        {
            $cartQuantity = $productQuantity; 
        }
        
        Cart::create([
            'user_id' => auth()->user()->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'qty' => $cartQuantity,
            'price' => $product->regular_price,
            'properties' => $request->properties
        ]);

        return response()->json(['status' => 500, 'message' => 'Product successfully added in your cart']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function count()
    {       
        $wishlist =  WishList::ByAuthUser()->count();
        return response()->json(['cart' => Cart::CartByAuthUser()->count()]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {   
       $cart->qty = newQuantity($cart->qty, $request->quantity);
       $cart->save();
       return response()->json(['status' => 200, 'message' => 'Cart Quantity Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();        
        return response()->json(['status' => 200, 'message' => 'Product successfully remove' ]);
    }
    
    public function destroies(Request $request)
    {
       
        foreach($request->products as $product)
        {
           $item = Cart::find($product)->first();
           $item->delete();
        }
        return response()->json(['status' => 200, 'message' => 'Product successfully remove' ]);
       
    }

    public function checkout(Request $request)
    {
        // if(!$request->selected) return back()->with('error', 'Please select item to checkout');
        
        // $cartItems = $request->selected;

        // foreach($cartItems as $cartItem){
        //      $item = Cart::find($cartItem);
        //      $item->selected = 1;
        //      $item->save();
        // } 
        
        // return redirect()->route('checkout');

    }


    public function updateProductsDiscount(Request $request)
    {

      
     $products = $request->products;
     $amount = $request->amount;

     $carts = Cart::CartByAuthUser(); 

        foreach($carts as $cart)
        {
           for($i = 0; $i < count($products); $i++)
           {
                if($cart->product_id == $products[$i]['product_id'])
                {
                    $cartitem =  Cart::where('product_id', $cart->product_id)->first();
                    $cartitem->discount = $amount;
                    $cartitem->save();
                }
            }
        }

        return response()->json(["status"=> "success"]);
    }

    

    
}
