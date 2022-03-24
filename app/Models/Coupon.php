<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\CouponUsageUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
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



    public function limit()
    {
        if($this->limit_per_coupon < $this->usage)
        return true;
    }

    public function limitPerUser()
    {
       $coupon = auth()->user()->activeCoupon();

       if(!empty($coupon)){
         
            if($this->limit_per_user < $coupon->usage)
            return true;
       }
       return false;
       
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
        if($this->expire_at == null){
            return  null;
        }

        return Carbon::parse($this->expire_at)->format($format);
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
