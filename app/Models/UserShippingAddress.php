<?php

namespace App\Models;

use App\Http\Traits\Crypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserShippingAddress extends Model
{
    use HasFactory, Crypted;

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

    public function user()
    {
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
