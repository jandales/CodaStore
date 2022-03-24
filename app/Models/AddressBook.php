<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AddressBook extends Model
{
    use HasFactory;

     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'email',
        'reciept_name',
        'reciept_number',        
        'street',        
        'barangay',
        'city_municipality',
        'province',
        'type',
        'status',    
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   

    public function default()
    {
        $user = auth()->user(); 
        $address = $this->where('user_id', $user)->get();
        return $address->where('status', 1)->first();
    }

   

    public function defaultUserAddress(User $user)
    {
       
        return $this->where('user_id', $user)->where('status', 1)->first();
    }
   
    public function fullAddress()
    {
        return  $this->street . ' ' . $this->barangay  . ' ' .   $this->city_municipality . ' ' . $this->province;
    }
  
}
