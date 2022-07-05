<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\CartServices;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class CartController extends Controller
{
    private $services;

    public function __construct(CartServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        $cart = Cart::ByUser()->first();     
        return response()->json($cart);
    }


    public function store(Request $request)
    {       
        
        $product = Product::find($request->id);
        $productQuantity = $product->stock->qty; 
        if ($productQuantity == 0)  return response()->json(['status' => 500, 'message' => 'Product is not available' ]); 
          
        $newQuantity = (int)$request->qty;  
        $total = 0; 

        $attributes = self::setAttributes($request->input('attributes'));

        $cart = Cart::ByUser()->first();        

        if ($productQuantity <  $newQuantity) $newQuantity = $productQuantity;         
        $total = $newQuantity * $product->regular_price;

        if ($cart) {   
            //check if an item  already in the cart;
            $item = $cart->hasThisProduct($product->id);  

            if ($item)               
                self::updateCartItem($item, $newQuantity, $attributes);            
            else 
                self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes); 

            Cart::UpdateTotal(); 
            
        } else {
               // stored new cart and item
                $cart = Cart::create([             
                    'total' => $total,
                    'cart_id' =>  Cookie::get('cart-id'),
                    'expired_at' => Carbon::now()->addDays(5),
                ]);       
                    
                self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes); 
        }  
          
       return response()->json(['count' => Cart::TotalItems()]);   
     
        
    }

    public function update(Request $request, $id)
    {  
        $item = CartItem::find($id);  
        return $this->services->update($request, $item);
    }

    public function destroy($id)
    {
        $item = CartItem::find($id);
        return $this->services->destroyCartItem($item);
    }

    public function count()
    {
        return response()->json(Cart::TotalItems());
    }













    private function updateCartItem(CartItem $cartitem, $qty, $attributes)
    {        

        $cartitem->qty += $qty;
        $cartitem->attributes = $attributes;
        $cartitem->save(); 
        return $cartitem;
    }

    private function storeCartItem($cart, $product, $quantity, $attributes)
    {
        return CartItem::create([
            'cart_id' => $cart,
            'product_id' => $product,
            'qty' => $quantity,
            'attributes' => $attributes,
        ]);
    }

    public function setAttributes($attributes)
    {
        $array = [];        
     
        foreach ($attributes as $item) {           
            $array  += array($item['name'] => $item['value']);
        } 

        return $array;
    }



    public function setCookie()
    {        
      
        if(Cookie::has('cart-id')) return true;

        $minutes = (60 * 24) * 7;  
        $timestamp = Carbon::now()->timestamp;  
        $value = $timestamp;        
        $response = new Response('Hello World');
        $response->withCookie(cookie('cart-id', $value, $minutes));    

        Cart::create([    
            'cart_id' => $value,
            'expired_at' =>  Carbon::parse($timestamp)->addDays(5),
        ]); 

        return $response;
    }
}
