<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index($status)
    {
        if( $status == "all" )  $orders =  Order::ByAuthUser()->get();
      
        else                   
          $orders =  Order::ByAuthUserStatus($status)->get();        
      
        return view('account.orders')->with('orders', $orders);
    }

    public function review(Product $product)
    {
        if($product->reviewby(auth()->user()))
        {
            $review =  auth()->user()->review($product);
            return view('account.order.review')->with(['review' =>  $review, 'product' => $product]);
        }
      
        return view('account.order.review')->with('product', $product);
    }

    public function details(Order $order)
    {    
        return view('account.order.detail')->with('order', $order);
    }

    public function message(Order $order)
    {
        return view('message')->with('order',$order);
    }

    public function cancel(Order $order)
    {
       
        $order->status = "canceled";
        $order->save();
        return back()->with('success',"You successfully cancelled your order");
    }

    // public function coupon()
    // {
    //     $dbcoupon = Coupon::where('name', $coupon)->first(); 
        
    //     if($dbcoupon == null)  return  response()->json(["status" => "error", "message" => "Coupon not Found"]);            
       
    //     if($dbcoupon->expired()) return response()->json(["status" => "error", "message" => "Coupon already expired"]);      
        
    //     if($dbcoupon->limit()) return  response()->json(["status" => "error", "message" => "This coupon already reach the limit"]);      
      
    //     if($dbcoupon->limitPerUser()) return  response()->json(["status" => "error", "message" => "You already used the coupon"]);     
    

    //     // update usage of coupon
    //     if(!$dbcoupon->containsUser()){
    //         $dbcoupon->usage = $dbcoupon->usage + 1;
    //         $dbcoupon->save();    
    //     }
     
    //     // create or update user usage of coupon 
    //     $this->UserCouponUsage($dbcoupon);

    //     return response()->json(['coupon' => $dbcoupon, 'products' => $dbcoupon->products ]);
    // }

    // public function validateCoupon($coupon)
    // {
    //     $dbcoupon = Coupon::where('name', $coupon)->first(); 
        
    //     if($dbcoupon == null)  return  response()->json(["status" => "error", "message" => "Coupon not Found"]);            
       
    //     if($dbcoupon->expired()) return response()->json(["status" => "error", "message" => "Coupon already expired"]);      
        
    //     if($dbcoupon->limit()) return  response()->json(["status" => "error", "message" => "This coupon already reach the limit"]);      
      
    //     if($dbcoupon->limitPerUser()) return  response()->json(["status" => "error", "message" => "You already used the coupon"]); 

    //     return 1;
    // }

}
