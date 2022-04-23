<?php

namespace App\Models;

use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\PaymentDetail;
use App\Models\BillingDetails;
use App\Http\Traits\DateAndTimeFormat ;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory,  DateAndTimeFormat ;

    protected $fillable = [
        'user_id', 
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
      return shippingFee() + $this->subtotal() - $coupon;
    }

    public function ordernumber()
    {
       $date = $this->created_at->format('Y-m-d');      
       return str_replace("-","",$date) . $this->id;
     
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

    public function scopeCompletedCount($query)
    {
        return $query->where('status', 'delivered')->count();
    }

    public function scopeReturnedCount($query)
    {
        return $query->where('status', 'returned')->count();
    }

    public function scopeCancelledCount($query)
    {
        return $query->where('status', 'cancelled')->count();
    }

    public function isDelivered(){
        return $this->status == 'delivered' ? true : false;
    }

    public function scopeSearch($query, $input)
    {
        return $query->where('id', $input);
                   
    }

    public function scopeByAuthUser($query)
    {
       return $query->where('user_id', auth()->user()->id)
                            ->with('payment_detail', 'items', 'items.product', 'shipping', 'billing', 'payment', 'couponUsed')
                            ->orderBy('id', 'desc');
    }

    public function scopeByAuthUserStatus($query, $status)
    {
       return $query->where([['user_id', auth()->user()->id],['status', $status]])
                            ->with('payment_detail', 'items', 'items.product', 'shipping', 'billing', 'payment', 'couponUsed')
                            ->orderBy('id', 'desc');
    }

 

}
