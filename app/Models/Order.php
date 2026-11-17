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
        'courier',
        'tracking_number',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Order lifecycle: confirmed (paid, to ship) -> shipped -> completed; or cancelled / returned.
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';

    private $COMPLETED = self::STATUS_COMPLETED;
    private $TOSHIP = self::STATUS_CONFIRMED;
    private $TORECIEVE = self::STATUS_SHIPPED;
    private $CANCELLED = self::STATUS_CANCELLED;

    /** Courier display name, e.g. "LBC Express". */
    public function courierName(): ?string
    {
        if (!$this->courier) {
            return null;
        }

        return config("couriers.{$this->courier}.name", $this->courier);
    }

    /** Public tracking link when the courier has a URL configured in config/couriers.php. */
    public function trackingUrl(): ?string
    {
        $template = config("couriers.{$this->courier}.url");
        if (!$template || !$this->tracking_number) {
            return null;
        }

        return str_replace('{number}', rawurlencode($this->tracking_number), $template);
    }

    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function returnRequest()
    {
        return $this->hasOne(OrderReturn::class);
    }

    /** Last day the customer can ask for a return, or null when the order isn't delivered. */
    public function returnWindowEndsAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            return null;
        }

        $delivered = $this->delivered_at ?: $this->updated_at;

        return $delivered ? $delivered->copy()->addDays(config('store.return_days', 30))->endOfDay() : null;
    }

    public function canRequestReturn(): bool
    {
        $ends = $this->returnWindowEndsAt();

        return $ends !== null && now()->lte($ends) && !$this->returnRequest()->exists();
    }

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

    /** Sum of the line totals (price is stored per unit). */
    public function subtotal()
    {
      return $this->items->sum(fn ($item) => $item->qty * $item->price);
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
          case 'completed':
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
