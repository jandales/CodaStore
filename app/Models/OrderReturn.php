<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A customer's request to return a delivered order, and the store's decision. */
class OrderReturn extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REFUNDED = 'refunded';

    public const REASONS = [
        'wrong-size' => 'Wrong size or fit',
        'damaged' => 'Arrived damaged or faulty',
        'not-as-described' => 'Not as described',
        'wrong-item' => 'Received the wrong item',
        'changed-mind' => 'Changed my mind',
        'other' => 'Other',
    ];

    protected $fillable = ['order_id', 'user_id', 'reason', 'details', 'status', 'admin_note', 'restocked', 'refund_amount', 'decided_at'];

    protected $casts = [
        'restocked' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst($this->reason);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }
}
