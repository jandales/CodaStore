<?php

namespace App\Models;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponUsed extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'coupon_id',
        'discount_type',
        'amount'
    ];

    public function coupon()
    {
        
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
