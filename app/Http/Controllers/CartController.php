<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\WishList;
use App\Models\AddressBook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cookie;

class CartController extends Controller
{

    public function __construct()
    {
        // $this->middleware(function ($request, $next) {  
        //    Self::deleteExpireCart();
        //     return $next($request);
        // });
    }     

    public function index()
    { 
        $shipping_methods = ShippingMethod::where('status', 1)->get(); 
        session(['shipping_charge' => $shipping_methods[0]->amount]);
        
        $cart = Cart::ByUser()->first();           
        return view('cart')->with(['cart' =>  $cart, 'shipping_methods' => $shipping_methods]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'required|numeric',
        ]); 


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
            {  
                $item->qty += $newQuantity;
                $item->attributes =  $attributes;
                $item->save();        
                Cart::UpdateTotal();  
                return; 
            }
            self::createItem($cart->id, $product->id, $newQuantity, $attributes);
            Cart::UpdateTotal();
            return;            
                 
            
        };  
 
        // stored new cart and item
        $cart = Cart::create([             
             'total' => $total,
             'cart_id' =>  Cookie::get('cart-id'),
             'expired_at' => Carbon::now()->addDays(5),
        ]);      
        
        self::createItem($cart->id, $product->id, $newQuantity, $attributes);   
           
        return;
    }

    private function createItem($cart, $product, $quantity, $attributes)
    {
        return CartItem::create([
            'cart_id' => $cart,
            'product_id' => $product,
            'qty' => $quantity,
            'attributes' => $attributes,
        ]);
    }

    public function createCart()
    {     
        $cart_id =  Cookie::get('cart-id');
        $cart = Cart::where('cart_id', $cart_id)->first();
        if(!empty($cart)) return;      

        Cart::create([             
            'total' => 0,
            'cart_id' => $cart_id,
            'expired_at' => Carbon::now()->addDays(5),
        ]); 
    }

  
    public function count()
    {       
        $cart =  Cart::ByUser()->first();
        $cartItemsCount = $cart->items->sum('qty'); 
        return response()->json(['cartItemsCount' => $cartItemsCount]);
    }

 
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CartItem $cartitem)
    {        
       $cartitem->qty = (integer)$request->quantity;
       $cartitem->save();
      
       $cart = Cart::UpdateTotal();
       return response()->json(['status' => 200,
        'item_subtotal' => $cartitem->subtotal(),
        'items_count' => $cart->totalItems(),
        'subtotal' => $cart->total,
        'grand_total' => $cart->grandTotal()
       ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(CartItem $item)
    {        
        $item->delete(); 
        Cart::UpdateTotal();
        return response()->json(['status' => 200]);
    } 

    public function couponActivate(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required',
        ]);

        $coupon = Coupon::where('name', $request->coupon_code)->first();
        
        if($coupon == null) return  response()->json(["status" => 500, "message" => "Coupon not Found"]);        

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
   
    public function couponRemove()
    {
        $cart = Cart::ByUser()->first();
        $cart->coupon_id = null;        
        $cart->discount = 0;
        $cart->save();
        return response()->json(['status' => 200, 'grand_total' => $cart->grandTotal()]);
    }

    public function selectShippingMethod($id)
    { 
        $shipping_method = ShippingMethod::find($id);  
        // create session shipping charge 
        session(['shipping_charge' => $shipping_method->amount]);
        
        $cart = Cart::ByUser()->first(); 
        $grand_total = $cart->grandTotal() + session()->get('shipping_charge');
        return response()->json(['shipping_method' => $shipping_method, 'grand_total' => $grand_total ]);
    }

    public function validateCoupon(Coupon $coupon)
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

    public function getTotal()
    {
        $cart = Cart::ByUser()->first();     
        return response()->json(['grand_total' => $cart->grandTotal()]);
    }

    public function get_user_cart()
    {
        $cart = Cart::ByUser()->first();     
        return response()->json(['cart' => $cart]);
    }
    
}
