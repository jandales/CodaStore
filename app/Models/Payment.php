<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'reference_number',
        'amount'     
    ];

    public function method()
    {
        if($this->method == 'cash') return "Cash on Deliver";
        return "Card";
    }


}
