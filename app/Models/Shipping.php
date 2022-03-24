<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reciept_name',
        'reciept_number',        
        'street',        
        'barangay',
        'city_municipality',
        'province',           
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function address(){
        return $this->street . ' ' . $this->barangay . ' ' . $this->city_municipality . ' ' . $this->province;
    }
}
