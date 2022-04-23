<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Checkout;
use App\Models\WishList;
use App\Models\AddressBook;
use App\Models\ShippingAddress;
use App\Http\Traits\DateAndTimeFormat;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, DateAndTimeFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dateofbirth',
        'age'
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


    public function scopeCurrentUser()
    {
       return $user = User::find(auth()->user()->id);
    }

    public function wishList()
    {
        return $this->hasMany(WishList::class);
    }

    public function rate()
    {
         return $this->hasMany(Rate::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payment_options(){
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

    public function checkout(){
        return $this->hasOne(Checkout::class);
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
 

    public function defaultAddress()
    {
        return  $this->addressBooks->where('status', 1)->first();
    }

    public function fullAddress()
    {
        return $this->defaultAddress()->street . ' ' . $this->defaultAddress()->barangay . ' ' . $this->defaultAddress()->city_municipality . ' ' . $this->defaultAddress()->province;
    }

    public function review(Product $product)
    {   
    
        return $this->reviews->where('product_id', $product->id)->first();

     
    }

    public function avatar()
    {
        return $this->imagePath ?? '/img/avatar.png';
    }

    public function scopeSearch($query,$input)
    {
        return $query->where('name','like','%' . $input . '%')                  
                     ->orWhere('email','like','%' . $input . '%')
                     ->orWhere('contact','like','%' . $input . '%');
                
    }

    public function couponUse($id)
    {
        return $this->coupons->where('coupon_id', $id)->first();
    }

    public function completed()
    {
        return $this->orders->where('status', 'delivered')->count();
    }

    public function returned()
    {
        return $this->orders->where('status', 'returned')->count();
    }

    public function cancelled()
    {
        return $this->orders->where('status', 'cancelled')->count();
    }

    public function age()
    {
        return Carbon::parse($this->dateofbirth)->diffInYears(Carbon::now());
    }



   

  
}
