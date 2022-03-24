<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $casts = [
        'properties' => 'array'
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'price',
        'properties'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function total()
    {
        return $this->qty * $this->price;
    } 
  
}
