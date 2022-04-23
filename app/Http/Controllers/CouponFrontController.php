<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Checkout;
use Illuminate\Http\Request;
use App\Models\CouponUsageUser;

class CouponFrontController extends Controller
{

    public function index(Request $request)
    {   
        $request->validate([
            'coupon_code' => 'required',
        ]);


        $coupon = Coupon::where('name', $request->coupon_code)->first();
        
        if($coupon == null) return  response()->json(["status" => 500, "message" => "Coupon not Found"]);        

        if(Self::validateCoupon($coupon))
        {
            
            Self::activateCoupon($coupon);
            return response()->json(['coupon' => $coupon, 'cartTotal' => Cart::subtotal() ]);
        }  

        
    }

    public function activateCoupon(Coupon $coupon)
    {
        $checkout = auth()->user()->checkout;
        if(!$checkout)
        {
            Checkout::create([
                    'user_id' => auth()->user()->id,
                    'coupon_id' => $coupon->id,                       
            ]);
        }
        $checkout->coupon_id = $coupon->id;
        $checkout->save();
    }



    public function active()
    
    {
        
        $user_coupon = auth()->user()->activeCoupon();

        if(empty($user_coupon)) return;

        $coupon = Coupon::find($user_coupon->coupon_id)->first();     

        if(empty($coupon)) return response()->json(["status" => 400, "Coupon not found"]);

       
        if(Self::validateCoupon($coupon))
        {
            return response()->json(['coupon' => $coupon]);
        }

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


  
}
