<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsageUser extends Model
{
    use HasFactory;


    protected $fillable = [
        'coupon_id', 'user_id', 'usage'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    
    
}
