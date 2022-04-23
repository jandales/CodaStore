<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipping extends Model
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
        'order_id',        
     ];

     public function address()
     {
         return $this->street . " " . $this->city . " " . $this->region . " " .  $this->country;
     }

     public function personName()
     {
        return $this->firstname . " " . $this->lastname;
     }
}
