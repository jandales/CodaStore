<?php

namespace App\Models;

use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory, DateTimeFormat;

    protected $fillable = [
        'order_id',
        'amount',
        'provider',
        'card_cvc',
        'reference_number',
        'status'       
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function statusColor()
    {
        switch ($this->status) {
            case 'paid':
            case 'completed':
                return 'bg-success';
            case 'pending':
                return 'bg-warning';
            case 'failed':
            case 'cancelled':
            case 'refunded':
                return 'bg-danger';
            default:
                return 'bg-primary';
        }
    }
}
