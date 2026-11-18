<?php

namespace App\Models;

use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory, DateTimeFormat;

    public const STATUS_PENDING = 'pending';          // cash on delivery, not collected yet
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';            // cash on delivery, parcel not collected
    public const STATUS_CANCELLED = 'cancelled';      // cash on delivery, order cancelled before shipping
    public const STATUS_REFUND_PENDING = 'refund_pending'; // refund to be sent by hand (COD and manual payments)
    public const STATUS_AWAITING = 'awaiting';        // manual order, waiting for the customer to pay

    /** Manual-order payment methods (provider "manual"). */
    public const MANUAL_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'other' => 'Other',
    ];
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REFUND_FAILED = 'refund_failed';

    protected $fillable = [
        'order_id',
        'amount',
        'provider',
        'reference_number',
        'status',
        'collected_at',
        'refunded_at',
        'method',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function isCashOnDelivery(): bool
    {
        return $this->provider === 'cod';
    }

    /** Paid outside the website (cash on delivery, or a manual order's cash / bank transfer / GCash). */
    public function isOffline(): bool
    {
        return in_array($this->provider, ['cod', 'manual'], true);
    }

    /** "PayPal", "Cash on delivery", "GCash". */
    public function methodLabel(): string
    {
        if ($this->provider === 'manual') {
            return self::MANUAL_METHODS[$this->method] ?? 'Manual payment';
        }

        return ['paypal' => 'PayPal', 'cod' => 'Cash on delivery'][$this->provider] ?? ucfirst((string) $this->provider);
    }

    public function statusLabel(): string
    {
        if ($this->isCashOnDelivery()) {
            $labels = [
                self::STATUS_PENDING => 'Cash due on delivery',
                self::STATUS_PAID => 'Cash collected',
                self::STATUS_FAILED => 'Not collected',
                self::STATUS_CANCELLED => 'Cancelled, nothing to collect',
                self::STATUS_REFUND_PENDING => 'Refund to send',
                self::STATUS_REFUNDED => 'Refunded',
            ];
        } elseif ($this->provider === 'manual') {
            $labels = [
                self::STATUS_AWAITING => 'Awaiting payment',
                self::STATUS_PAID => 'Paid',
                self::STATUS_CANCELLED => 'Cancelled, nothing paid',
                self::STATUS_REFUND_PENDING => 'Refund to send',
                self::STATUS_REFUNDED => 'Refunded',
            ];
        } else {
            $labels = [
                self::STATUS_PAID => 'Paid',
                self::STATUS_REFUNDED => 'Refunded',
                self::STATUS_REFUND_FAILED => 'Refund failed',
            ];
        }

        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

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
            case 'awaiting':
            case 'refund_pending':
            case 'refund_failed':
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
