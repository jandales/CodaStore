<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_name',
        'card_number',
        'card_expire_date',
        'card_cvc',
        'order_id'       
    ];

}
