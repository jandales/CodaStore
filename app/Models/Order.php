<?php

namespace App\Models;

use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\BillingDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'status',
        'shipping_fee' 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts()
    {
       return $this->hasMany(OrderProduct::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function billing()
    {
      return $this->hasOne(BillingDetails::class);
    }   

    public function couponUsed()
    {
       return $this->hasOne(CouponUsed::class);
    }

    public function shipping()
    { 
      return $this->hasOne(Shipping::class);
    }

    public function totalItems()
    {
      return $this->orderProducts->sum('qty');
    }

    public function subtotal()
    {
      return $this->orderProducts->sum('price');
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

}
