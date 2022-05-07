<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShippingAddress extends Model
{
    use HasFactory;

    protected $table = 'user_shipping_address';
     protected $fillable = [  
        'firstname',
        'lastname',
        'street',
        'city',
        'phone',
        'country',
        'region',
        'zipcode',
        'user_id',
        'status'
     ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeCount($query)
    {
         return $query->get()->count();
    }

    public function fullName()
    {     
        return $this->firstname . " " . $this->lastname;    
    }

    public function fullAddress()
    {
        return $this->street . " " . $this->city . " " . $this->region . " " . $this->country;      
    }
}
