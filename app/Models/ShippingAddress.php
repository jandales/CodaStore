<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $table = 'shipping_address';
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

    public function name(){
        return $this->firstname . " " . $this->lastname;    
    }

    public function full()
    {
        return $this->street . " " . $this->city . " " . $this->region . " " . $this->country;      
    }
}
