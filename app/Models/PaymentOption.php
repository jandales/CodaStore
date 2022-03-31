<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_name',
        'card_number',
        'cart_expired_date',
        'card_cvc',
        'user_id'
    ];
}
