<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\CouponUsed;
use App\Models\AddressBook;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Models\BillingDetails;

class CheckOutController extends Controller
{
    public function index()
    {       
        $user =  auth()->user();
    
        return view('checkout')->with([
            'carts' => $user->carts, 
            'addressBooks' => $user->addressBooks,        
        ]);
    }

    public function placeOrder(Request $request)
    {    
        $user = auth()->user();
        $address = $user->defaultAddress(); 
      
        $order = Order::create([
            'user_id' => auth()->user()->id,            
            'status' => 'confirm',
            'shipping_fee' => $request->shipping_fee,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'amount' => $request->amount,
        ]);

        BillingDetails::create([
            'order_id' => $order->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'phone' => auth()->user()->contact,
            'address' => $address->fullAddress(),
        ]);         

        Shipping::create([
            'order_id' => $order->id,
            'reciept_name' => $address->reciept_name,
            'reciept_number' => $address->reciept_number,            
            'street'=> $address->street,          
            'barangay'=> $address->barangay,
            'city_municipality'=> $address->city_municipality,
            'province'=> $address->province          
        ]);

        $carts = $user->carts;

        foreach($carts as $cart)
        {
           $product =  OrderProduct::create([  
                'order_id' => $order->id,          
                'product_id' => $cart->product_id,
                'qty' => $cart->qty,
                'price' => $cart->product->prices->selling,
                'properties' => $cart->properties
           ]);   

           Stock::where('product_id', $product->product_id)->decrement('qty', $cart->qty);
           $cart->delete();           
           
        } 

         // check if user use coupon
         $coupon = Coupon::where('name', $request->coupon)->first();

         if(!empty($coupon))
         {
             $couponId = $coupon->id;
             $couponDiscount = $coupon->amount;
 
             $couponUsed = CouponUsed::create([
                 'order_id' => $order->id,
                 'coupon_id' => $couponId,
                 'discount_type' => "none",          
                 'amount' => $couponDiscount,  
             ]);

             $coupon->usage += 1;
             $coupon->save();

             $userCoupon = auth()->user()->couponUse($couponId);
             // change status of use coupon
             $userCoupon->used = 1;
             $userCoupon->usage  += 1;
             $userCoupon->save();


         }

        return redirect()->route('checkout.details',[$order]);   

        
    }

    public function details(Order $order)
    { 
        return view('message')->with('order', $order);
    }

    public function remove($id)
    {      
        $cart = Cart::find($id);    
        $cart->selected = 0;
        $cart->save();
        return response()->json(['status' => 200]);
    }
   
}
