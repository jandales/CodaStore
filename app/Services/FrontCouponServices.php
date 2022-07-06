<?php 
namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsageUser;

class FrontCouponServices{

   

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
        
        return ['status' => $state, 'message' => $message, 500];
        
    }

    public function index($request)
    {
        $coupon = Coupon::where('name', $request->coupon_code)->first();
        
        if($coupon == null) return  response()->json(["status" => 500, "message" => "Coupon not Found"]);        
        
        $validated = Self::validateCoupon($coupon);

        if(!$validated['status']) return $validated;

        Self::addUserUsage($coupon);     

        return response()->json(['status' => 200, 'coupon' => $coupon, 'cartTotal' => Cart::Total() ]);
    }

    public function active()    
    {
        
        $user_coupon = auth()->user()->activeCoupon()->first();

        if(empty($user_coupon)) return response()->json(["status" => 500, "message" => "No active found"]);

        $coupon = Coupon::find($user_coupon->coupon_id);     
     
        if(empty($coupon)) return response()->json(["status" => 500, "message" => "No active found"]);


        $validated = Self::validateCoupon($coupon);

        if(!$validated['status']) return $validated;
      
        return response()->json(['status' => 200, 'coupon' => $coupon]);
        

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
       
       return response()->json(['status' => 200, 'message' => 'Successfully Remove' ]);
      
    }
    public function resetProductDiscount(){
        $carts = auth()->user()->carts;
        
        foreach($carts as $cart)
        {
            $cart->discount = 0;
            $cart->save();
        }
    }

    private function addUserUsage($coupon)
    {
        $userCoupon = CouponUsageUser::where('coupon_id', $coupon->id)->first();
        if($userCoupon){
            $userCoupon->deleted = 0;
            $userCoupon->usage += 1;
            $userCoupon->save();
        }else{
            CouponUsageUser::create([
                'coupon_id' => $coupon->id,
                'user_id' => auth()->user()->id,
                'usage' => 1
            ]);
        }
    }


}