<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class PlaceOrderController extends Controller
{
    public function direct(Product $product,$qty, Request $request){
   
      
        $couponId = "0";
        $couponDiscount = "0";
               
        $shippingTo = auth()->user()->defaultAddress();      
        
        
        // check if user use coupon
        $coupon = Coupon::where('name', $request->coupon)->first();

        if(!empty($coupon))
        {
            $couponId = $coupon->id;
            $couponDiscount = $coupon->amount;
        }   
 
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'amount' => $request->amount, 
            'shipping_fee' => $request->shipping_fee,
            'coupon_id' => $couponId,
            'discount' => $couponDiscount,         
            'status' => 'pending'
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'reciept_name' => $shippingTo->reciept_name,
            'reciept_number' => $shippingTo->reciept_number,            
            'street'=> $shippingTo->street,          
            'barangay'=> $shippingTo->barangay,
            'city_municipality'=> $shippingTo->city_municipality,
            'province'=> $shippingTo->province          
        ]);

 

        $orderProduct =  OrderProduct::create([  
                'order_id' => $order->id,          
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => $product->prices->selling,
                'properties' => ""
        ]);   

           Stock::where('product_id', $product->id)->decrement('qty', $qty);
                   
           
         

        if(empty($couponId)) return response()->json(['status' => 'success', 'route' => route('orders.thankyou')]);        

        $userCoupon = auth()->user()->couponUse($couponId);
        // change status of use coupon
        $userCoupon->used = 1;
        $userCoupon->usage  += 1;
        $userCoupon->save();

        return response()->json(['status' => 'success', 'route' => route('orders.thankyou')]);     

    }

    public function fromCart(){

    }
}
