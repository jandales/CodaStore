<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Order;

class OrderObserver
{
    public const LABELS = [
        Order::STATUS_CONFIRMED => 'To ship',
        Order::STATUS_SHIPPED => 'Shipped',
        Order::STATUS_COMPLETED => 'Delivered',
        Order::STATUS_CANCELLED => 'Cancelled',
        Order::STATUS_RETURNED => 'Returned',
    ];

    public function updated(Order $order)
    {
        if (!ActivityLog::byAdmin() || !$order->wasChanged('status')) {
            return;
        }

        $from = $order->getOriginal('status');
        $to = $order->status;
        $description = 'Status changed from ' . (self::LABELS[$from] ?? $from) . ' to ' . (self::LABELS[$to] ?? $to);
        if ($to === Order::STATUS_SHIPPED && $order->tracking_number) {
            $description .= ' (' . $order->courierName() . ' ' . $order->tracking_number . ')';
        }

        ActivityLog::record('order.status', $order, $description, ['from' => $from, 'to' => $to]);
    }
}
