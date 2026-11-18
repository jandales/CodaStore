<?php

namespace App\Services\Orders;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Stock;
use App\Services\OrderNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Cancels an order that has not shipped yet: sets the status, puts the stock back, gives the
 * coupon use back, refunds a PayPal payment and emails the customer. Used by customer, admin and API.
 */
class CancelOrder
{
    private $refunder;
    private $notifier;

    public function __construct(RefundPayment $refunder, OrderNotifier $notifier)
    {
        $this->refunder = $refunder;
        $this->notifier = $notifier;
    }

    public function handle(Order $order): Order
    {
        $order = DB::transaction(function () use ($order) {
            // Re-read under a lock so two cancel requests can't both restore stock.
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!$order->canBeCancelled()) {
                throw new OrderNotCancellableException("Order {$order->order_number} can't be cancelled because it is {$order->status}.");
            }

            foreach ($order->items as $item) {
                \App\Services\Products\StockLedger::restore($item);
            }

            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->where('usage', '>', 0)->decrement('usage');
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->cancelled_at = now();
            $order->save();

            return $order;
        });

        // Outside the transaction: the cancellation stands even if PayPal or email is unavailable.
        $this->refunder->handle($order, "Order {$order->order_number} cancelled");

        // Cash on delivery / unpaid manual orders: nothing was paid, nothing left to collect.
        // Paid manual orders: the money goes back by hand ("Refund sent" on the order page).
        $payment = $order->payment()->first();
        if ($payment && $payment->isOffline()) {
            if (in_array($payment->status, [OrderPayment::STATUS_PENDING, OrderPayment::STATUS_AWAITING], true)) {
                $payment->update(['status' => OrderPayment::STATUS_CANCELLED]);
            } elseif ($payment->provider === 'manual' && $payment->status === OrderPayment::STATUS_PAID) {
                $payment->update(['status' => OrderPayment::STATUS_REFUND_PENDING]);
            }
        }
        $this->notifier->cancelled($order->fresh());

        return $order->fresh();
    }
}
