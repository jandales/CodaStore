<?php

namespace  App\Services;

use App\Models\Coupon;
use App\Models\CouponRestrictionProduct;

class CouponServices
{

    public function store($request)
    {
        $coupon  = Coupon::create([
            'name' => $request->name,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'amount' => $request->amount,         
            'max_amount' => $request->max_amount,
            'limit_per_coupon' => $request->limit_per_coupon,
            'limit_to_xitems' => $request->limit_to_xitems,
            'limit_per_user' => $request->limit_per_user,
            'expire_at' => $request->expire_at,
        ]);

        Self::createProducts($request->products, $coupon->id);
        return $coupon;
    }

    public function update($request, Coupon $coupon)
    {
        $coupon->name = $request->name;
        $coupon->description = $request->description;
        $coupon->discount_type = $request->discount_type;
        $coupon->amount = $request->amount;      
        $coupon->max_amount = $request->max_amount;
        $coupon->limit_per_coupon = $request->limit_per_coupon;
        $coupon->limit_to_xitems = $request->limit_to_xitems;
        $coupon->limit_per_user = $request->limit_per_user;
        $coupon->expire_at = $request->expire_at;
        $coupon->save();

        CouponRestrictionProduct::where('coupon_id', $coupon->id)->delete();

        $this->createProducts($request->products, $coupon->id);
    }

    public function destroySelectedItem($request)
    {
        foreach($request->selected as $id)
        {
            Coupon::find($id)->delete();
        }
    }

    private function createProducts($arr, $id)
    {
        $products = json_decode($arr);

        foreach($products as $product){   
            $item = json_decode(json_encode($product)); 
            CouponRestrictionProduct::create([
                'coupon_id' => $id,
                'product_id' => $item->id,
                'type' => $item->type
            ]);
        }
    }
}