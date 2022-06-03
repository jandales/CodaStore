<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cookie;

class CartServices
{
    

    public function index()
    {              
        return Cart::ByUser()->first();
    }

    public function store($request, Product $product)
    {
        $productQuantity = $product->stock->qty;  
        $attributes = $request->properties;
        $newQuantity = (int)$request->qty;  
        $total = 0; 
   
        if($productQuantity == 0)  return response()->json(['status' => 500, 'message' => 'Product is not available' ]);   

        $cart = Cart::ByUser()->first();
        

        if($productQuantity <  $newQuantity) $newQuantity = $productQuantity; 
        
        $total = $newQuantity * $product->regular_price;

        if($cart)
        {   
            //check if an item  already in the cart;
            $item = $cart->hasThisProduct($product->id);
   
            if($item)               
                self::updateCartItem($item, $newQuantity, $attributes);            
            else 
                self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes); 

            Cart::UpdateTotal(); 
                        
            return response()->json(['count' => Cart::TotalItems()]);           
                 
            
        };   

       // stored new cart and item
       $cart = Cart::create([             
             'total' => $total,
             'cart_id' =>  Cookie::get('cart-id'),
             'expired_at' => Carbon::now()->addDays(5),
       ]);       
              
       self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes);    
       return response()->json(['count' => Cart::TotalItems()]);   
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
            'attributes' => json_encode($attributes),
        ]);
    }


    public function update(Request $request, CartItem $cartitem)
    {        
       $cartitem->qty = (integer)$request->quantity;
       $cartitem->save();      
       $cart = Cart::UpdateTotal();
       return response()->json([
           'status' => 200,
           'item_subtotal' => $cartitem->subtotal(),
           'items_count' => $cart->totalItems(),
           'subtotal' => $cart->total,
           'grand_total' => $cart->grandTotal()
        ]);

    }

    public function count()
    {       
        $cart =  Cart::ByUser()->first();         
        $cartItemsCount = $cart == null ? 0 : $cart->items->sum('qty');
        return response()->json(['cartItemsCount' => $cartItemsCount]);
    }

    public function total()
    {
        $cart = Cart::ByUser()->first();     
        return response()->json(['grand_total' => $cart->grandTotal()]);
    }

    public function getCart()
    {
        $cart = Cart::ByUser()->first();     
        return response()->json(['cart' => $cart]);
    }


    public function couponActivate(Request $request)
    {      
        $coupon = Coupon::where('name', $request->coupon_code)->first();
        
        if($coupon == null) return response()->json(["status" => 500, "message" => "Coupon not Found"]);        

        if(Self::validateCoupon($coupon))
        {
            $cart = Cart::ByUser()->first();
            $total = $cart->total;  
            $cart->coupon_id = $coupon->id;          
            $cart->discount = $coupon->discount($total);
            $cart->save();

            return response()->json([
                'status' => 200, 
                'coupon' => $coupon,
                'discount' => $cart->discount,
                'subtotal' => $cart->total,
                'grand_total' => $cart->grandTotal()
        ]);
        }  
    }

    public function destroyCartItem(CartItem $item)
    {    
        $item->delete(); 
        Cart::UpdateTotal();
        return response()->json(['status' => 200, 'count' =>  Cart::TotalItems(), 'total' => Cart::Total() ]);
    } 


    public function couponRemove()
    {
        $cart = Cart::ByUser()->first();
        $cart->coupon_id = null;        
        $cart->discount = 0;
        $cart->save();
        return response()->json(['status' => 200, 'grand_total' => $cart->grandTotal()]);
        
    }

    public function selectShippingMethod(ShippingMethod $shipping_method)
    {        
        // create session shipping charge 
        session(['shipping_charge' => $shipping_method->amount]);        
        $cart = Cart::ByUser()->first(); 
        $grand_total = $cart->grandTotal() + session()->get('shipping_charge');
        return response()->json(['shipping_method' => $shipping_method, 'grand_total' => $grand_total]);
    }

    public function deleteExpiredCart()
    {
        $currentDate = now();
        $carts = Cart::with('items')->where('expired_at', '<', $currentDate)->get();       
        foreach($carts as $cart){                
            if($cart->expired_at < now()){
                foreach($cart->items as $item){
                    $item->delete();
                }
                $cart->delete();
            }
        }     
    }


    private function validateCoupon(Coupon $coupon)
    {      
      
        $state = true;
        $message = '';
        $currentDate = date('Y-m-d H:i:s');   
       
        if ($currentDate > $coupon->expire_at){
            $state = false;
            $message = "coupon already Expired";
        }
        
        if ($coupon->limit_per_coupon < $coupon->usage){
            $state = false;
            $message = 'This coupon already reach the limit';
        }
        if (!$coupon->limitPerUser()){
            $state = false;
            $message = 'You Already Used this Coupon';
        } 
        
        return ['status' => $state, 'message' => $message];
        
    }


}