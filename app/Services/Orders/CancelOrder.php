<?php

namespace App\Services\Orders;

use App\Contracts\PaymentGateway;
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
    private $gateway;
    private $notifier;

    public function __construct(PaymentGateway $gateway, OrderNotifier $notifier)
    {
        $this->gateway = $gateway;
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

        // Outside the transaction: the cancellation stands even if PayPal or email is unavailable.
        $this->refund($order);
        $this->notifier->cancelled($order->fresh());

        return $order->fresh();
    }

    private function refund(Order $order): void
    {
        $payment = $order->payment;
        if (!$payment || $payment->status !== OrderPayment::STATUS_PAID || $payment->provider !== $this->gateway->name()) {
            return;
        }

        $refunded = $this->gateway->refund(
            (string) $payment->reference_number,
            (float) $payment->amount,
            config('paypal.currency', 'PHP'),
            "Order {$order->order_number} cancelled"
        );

        $payment->status = $refunded ? OrderPayment::STATUS_REFUNDED : OrderPayment::STATUS_REFUND_FAILED;
        $payment->save();

        if (!$refunded) {
            logger()->error('Automatic refund failed for cancelled order; refund manually in PayPal', [
                'order' => $order->order_number,
                'capture_id' => $payment->reference_number,
            ]);
        }
    }
}
