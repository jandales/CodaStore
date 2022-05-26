<?php

namespace App\Models;

use App\Http\Traits\Crypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPaymentOption extends Model
{
    use HasFactory, Crypted;

    protected $fillable = [
        'card_name',
        'card_number',
        'card_expire_date',
        'card_cvc',
        'user_id',
        'status'
    ];



 
}
