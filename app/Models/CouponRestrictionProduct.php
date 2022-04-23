<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponRestrictionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'product_id',
        'type'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    
}
