<?php

namespace App\Services\Orders;

use App\Exceptions\OrderNotCancellableException;
use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/** Admin actions that only exist for cash on delivery orders. */
class CashOnDeliveryOrders
{
    /**
     * The courier couldn't collect the cash (parcel refused or undeliverable) and returned it:
     * the order becomes "returned", the stock and the coupon use go back, the payment fails.
     */
    public function notCollected(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $order->payment()->first();

            if ($order->status !== Order::STATUS_SHIPPED || !$payment || !$payment->isCashOnDelivery() || $payment->status !== OrderPayment::STATUS_PENDING) {
                throw new OrderNotCancellableException('Only shipped cash on delivery orders that are still unpaid can be marked as not collected.');
            }

            foreach ($order->items as $item) {
                Stock::where('product_id', $item->product_id)->increment('qty', $item->qty);
            }
            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->where('usage', '>', 0)->decrement('usage');
            }

            $order->status = Order::STATUS_RETURNED;
            $order->returned_at = now();
            $order->save();

            $payment->update(['status' => OrderPayment::STATUS_FAILED]);

            ActivityLog::record('order.cod.not_collected', $order, 'Cash on delivery not collected: parcel returned, stock restored');

            return $order;
        });
    }

    /** An admin sent the refund for a returned COD order by hand (bank or GCash). */
    public function refundSent(Order $order): OrderPayment
    {
        $payment = $order->payment()->first();
        if (!$payment || !$payment->isCashOnDelivery() || $payment->status !== OrderPayment::STATUS_REFUND_PENDING) {
            throw new OrderNotCancellableException('There is no refund waiting to be sent for this order.');
        }

        $payment->update(['status' => OrderPayment::STATUS_REFUNDED, 'refunded_at' => now()]);
        ActivityLog::record('order.cod.refund_sent', $order, 'Refund of ₱' . number_format((float) $payment->amount, 2) . ' sent by hand');

        return $payment;
    }
}
