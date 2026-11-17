<?php

namespace App\Services\Orders;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * Cancels an order that has not shipped yet: sets the status, puts the stock back and
 * gives the coupon use back. Used by both the customer and the admin.
 */
class CancelOrder
{
    public function handle(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            // Re-read under a lock so two cancel requests can't both restore stock.
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!$order->canBeCancelled()) {
                throw new OrderNotCancellableException("Order {$order->order_number} can't be cancelled because it is {$order->status}.");
            }

            foreach ($order->items as $item) {
                Stock::where('product_id', $item->product_id)->increment('qty', $item->qty);
            }

            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->where('usage', '>', 0)->decrement('usage');
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->cancelled_at = now();
            $order->save();

            return $order;
        });
    }
}
