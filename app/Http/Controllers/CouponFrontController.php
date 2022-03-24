<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\CouponUsageUser;

class CouponFrontController extends Controller
{
    public function index($coupon)
    {   
        $dbcoupon = Coupon::where('name', $coupon)->first(); 
        
        if($dbcoupon == null)  return  response()->json(["status" => "error", "message" => "Coupon not Found"]);            
       
        if($dbcoupon->expired()) return response()->json(["status" => "error", "message" => "Coupon already expired"]);      
        
        if($dbcoupon->limit()) return  response()->json(["status" => "error", "message" => "This coupon already reach the limit"]);      
      
        if($dbcoupon->limitPerUser()) return  response()->json(["status" => "error", "message" => "You already used the coupon"]); 

        return response()->json(['coupon' => $dbcoupon, 'products' => $dbcoupon->products ]);
    }



    public function active()
    
    {
        
        $user_coupon = auth()->user()->activeCoupon();
        if(empty($user_coupon)) return;
        $coupon = Coupon::find($user_coupon->coupon_id)->first();     

        if(empty($user_coupon)) return response()->json(["status" => 400, "Coupon not found"]);

        if($coupon->expired()) return response()->json(["status" => "error", "message" => "Coupon already expired"]);
        
        if($coupon->limit()) return  response()->json( ["status" => "error", "message" => "This coupon already reach the limit"]);

        if($coupon->limitPerUser()) return  response()->json( ["status" => "error", "message" => "You already used the coupon"]); 

        if($coupon->userDeleted()) return response()->json(["status" => 400]);
        
        return response()->json(['coupon' => $coupon, 'products' => $coupon->products ]);

    }


 
    public function remove($id)
    {   
        // get Coupon user Usage
       $userCoupon = CouponUsageUser::where('coupon_id', $id)->first();
       $userCoupon->deleted = 1;
       $userCoupon->save();  

       $coupon = $userCoupon->coupon;

       if($coupon->discount_type == 0)
       {
            $this->resetProductDiscount();
       }
       
       return response()->json(['status' => 500, 'message' => 'Successfully Remove' ]);
       
    }


    public function resetProductDiscount()
    {
        $carts = auth()->user()->carts;
        
        foreach($carts as $cart)
        {
            $cart->discount = 0;
            $cart->save();
        }
    }


  
}
