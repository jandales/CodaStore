<?php

namespace App\Models;

use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Http\Traits\Crypted;
use App\Models\PaymentDetail;
use App\Models\BillingDetails;
use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes, Crypted, DateTimeFormat;

    protected $fillable = [
        'user_id', 
        'order_number',
        'shipping_method_id',
        'status',
        'shipping_charge',
        'gross_total',
        'num_items_sold',
        'tax_total',
        'net_total',
        'coupon_id',
        'coupon_amount',
    ];

    private $COMPLETED = 'completed';
    private $TOSHIP = 'confirmed';
    private $TORECIEVE = 'shipped';
    private $CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment_detail()
    {
      return $this->hasOne(PaymentDetail::class);
    }

    public function items()
    {
       return $this->hasMany(OrderProduct::class);
    }

    public function coupon()
    {
      return $this->hasOne(Coupon::class);
    }
    public function payment()
    {
        return $this->hasOne(OrderPayment::class);
    }

    public function billing()
    {
      return $this->hasOne(OrderBilling::class);
    }   

    public function couponUsed()
    {
       return $this->hasOne(CouponUsed::class);
    }

    public function shipping()
    { 
      return $this->hasOne(OrderShipping::class);
    }

    public function shippingMethod()
    {
      return $this->hasOne(ShippingMethod::class,'id', 'shipping_method_id');   
    }

    public function totalItems()
    {
      return $this->items->sum('qty');
    }

    public function subtotal()
    {
      return $this->items->sum('price');
    }
    
    public function amount()
    {
       return $this->payment->amount;
    }   

    public function total()
    { 
      $coupon = 0;
      if($this->couponUsed) $coupon = $this->couponUsed->amount;    
      return 0 + $this->subtotal() - $coupon;
    }



    public function statusColor()
    {
        switch($this->status)
        {
          case 'returned': 
            return 'bg-warning';
          case 'delivered':
            return 'bg-success';
          case  'cancelled':
            return 'bg-danger';
          default : 
            return 'bg-primary';
            
        }      
    }

    public function scopeCountByStatus($query, $status)
    {
        return $query->where('status', $status)->count();
    } 

    public function isDelivered()
    {
        return $this->status == $this->COMPLETED ? true : false;
    }

    public function isToRecieved()
    {
      return $this->status == $this->TORECIEVE ? true : false;
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->whereHas('user', function($q) use  ($keyword){
            return $q->where('name', 'like','%' . $keyword . '%')
                    ->orWhere('email','like','%' . $keyword . '%');
        })->orWhere('order_number', $keyword);
                   
    }

    public function scopeByAuthUser($query)
    {
       return $query->where('user_id', auth()->user()->id)
                            ->with('payment_detail', 'items', 'items.product', 'shipping', 'billing', 'payment', 'couponUsed')
                            ->orderBy('id', 'desc');
    }



 

}
