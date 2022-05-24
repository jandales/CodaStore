<?php

namespace App\Models;

use App\Http\Traits\Crypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingMethod extends Model
{
    use HasFactory, Crypted;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'status',
    ];

    public function status()
    {       
        return  $this->status == 0 ? 'inactive' : 'active';
    }
}
