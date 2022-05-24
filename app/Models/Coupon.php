<?php

namespace App\Models;

use Carbon\Carbon;
use App\Http\Traits\Crypted;
use App\Models\CouponUsageUser;
use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory, Crypted, DateTimeFormat;
    protected $primaryKey = "id";
    protected $fillable  = [
        'name',
        'description',
        'discount_type',
        'amount',       
        'min_amount',
        'max_amount',
        'limit_per_coupon',
        'limit_to_xitems',
        'limit_per_user',
        'usage',
        'expire_at'
    ];

 

    public function products(){

       return $this->hasMany(CouponRestrictionProduct::class, 'coupon_id');
       
    }

    public function containsUser()
    {
       return $this->usersUsage->contains('user_id', auth()->user()->id);
    }


    public function discount($cartTotal)
    {
        $total = $cartTotal;

        if ($this->discount_type == 0) return   ($total * ($this->amount / 100));

        if ($this->discount_type == 1) return  $this->amount;
       
        if ($this->discount_type == 2)
        {
           $productCount = self::compareCartProductsToCouponProducts();
           return  $this->amount * $productCount;           
        } 

       

    }

    public function compareCartProductsToCouponProducts()
    {
        $products = $this->products;
        if(empty($products)) return 0;

        $cart = Cart::ByAuthUser()->first();      
        $count = 0;
        foreach($cart->items as $item)
        {
            foreach($products as $product){
                if ($product->product_id == $item->product_id )
                {
                    $count++;
                }
            }
        }

        return $count;
    }



    public function limit()
    {
        if($this->limit_per_coupon < $this->usage)
        return true;
    }

    public function limitPerUser()
    {
       $coupon = auth()->user()->activeCoupon();

       if(empty($coupon)) return true;
         
       if($this->limit_per_user <= $coupon->usage)
            return false;
       
       return true;
       
    }

    public function userDeleted(){

        $coupon = auth()->user()->activeCoupon();  
        if(!empty($coupon)){
          
             if($coupon->deleted == 1)
             return true;
        }
        return false;

    }




    

  

    

  
  

   public function discountType()
   {
       switch($this->discount_type)
       {        

        
           case 1 : 
                return 'Fixed cart discount';
           break;

           case 2 :
                return 'Fixed product discount';
           break;

           default :
                 return 'Percentage discount';
           break;
            
       }
    }


    public function date($format)
    {
        if($this->expire_at == null)  return  null;
        return  $this->dateFormat($this->expire_at);        
    }

    public function expired()
    {
        $currentDate = date('Y-m-d H:i:s');
        if($currentDate > $this->expire_at)  return true;
        return false;       
    }

    public function usegeLimit()
    {
        return 0 . "/" . $this->limit_per_coupon;
    }

    public function scopeSearch($query, $input)
    {
        return $query->where('name','like','%' . $input . '%');
       
    }



  
   
}
