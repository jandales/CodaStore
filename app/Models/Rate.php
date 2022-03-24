<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'rate',
      
    ];


    public function scopeRatedby($query, $user)
    {
        return $this->where('user_id', $user);
    }

    public function product(){

        return $this->belongsTo(Product::class);

    }

   

}

    