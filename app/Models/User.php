<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Product;
use App\Http\Traits\Crypted;
use App\Models\UserPaymentOption;
use App\Http\Traits\DateTimeFormat;
use App\Models\UserShippingAddress;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes ,Notifiable, DateTimeFormat, Crypted;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',       
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        
    ];
        /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $attributes = [
        'imagePath' => '/img/avatar/default-avatar.jpg',        
    ];   

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payment_options()
    {
        return $this->hasMany(UserPaymentOption::class);
    }

    public function defaultPayment()
    {
        return $this->payment_options->where('status', 1);
    } 

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
    
    public function shippingAddress()
    {
        return $this->hasMany(UserShippingAddress::class);
    }

    public function shippingDefaultAddress()
    {
        return $this->shippingAddress->where('status', 1)->first();
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function coupons()
    {
        return $this->hasMany(CouponUsageUser::class, 'user_id');
    }

    public function activeCoupon()
    {
        return $this->coupons->where('user_id', $this->id)->first();
    } 

    public function review(Product $product)
    {       
        return $this->reviews->where('product_id', $product->id)->first();     
    }

    public function scopeSearch($query,$input)
    {
        return $query->where('name','like','%' . $input . '%')                  
                     ->orWhere('email','like','%' . $input . '%');
    }

    public function couponUse($id)
    {
        return $this->coupons->where('coupon_id', $id)->first();
    }

    public function orderStatus($status)
    {
        return $this->orders->where('status', $status)->count();
    }    

    public function age()
    {
        return Carbon::parse($this->dateofbirth)->diffInYears(Carbon::now());
    }

    public function is_current_user()
    {
        return $this->id === auth()->user()->id;
    }
  
}
