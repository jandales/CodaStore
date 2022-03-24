<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;

     protected $fillable = [  
        'firstname',
        'lastname',
        'street',
        'city',
        'phone',
        'country',
        'region',
        'zipcode',
        'user_id'
     ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
