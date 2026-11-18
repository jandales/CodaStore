<?php

namespace App\Services\Orders;

use App\Exceptions\ReturnNotAllowedException;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Stock;
use App\Services\OrderNotifier;
use Illuminate\Support\Facades\DB;

/**
 * The return flow: the customer asks to return a delivered order, then the store approves it
 * (refund through PayPal, optional restock, order becomes "returned") or rejects it with a note.
 */
class ProcessReturn
{
    private $refunder;
    private $notifier;

    public function __construct(RefundPayment $refunder, OrderNotifier $notifier)
    {
        $this->refunder = $refunder;
        $this->notifier = $notifier;
    }

    public function request(Order $order, string $reason, ?string $details): OrderReturn
    {
        $return = DB::transaction(function () use ($order, $reason, $details) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!$order->canRequestReturn()) {
                throw new ReturnNotAllowedException("Order {$order->order_number} can't be returned.");
            }

            return OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'reason' => $reason,
                'details' => $details,
                'status' => OrderReturn::STATUS_REQUESTED,
            ]);
        });

        $this->notifier->returnRequested($return->order);

        return $return;
    }

    public function approve(Order $order, bool $restock): OrderReturn
    {
        $return = DB::transaction(function () use ($order, $restock) {
            $return = OrderReturn::where('order_id', $order->id)->lockForUpdate()->first();
            if (!$return || !$return->isOpen()) {
                throw new ReturnNotAllowedException('This return has already been decided.');
            }

            if ($restock) {
                foreach ($order->items as $item) {
                    \App\Services\Products\StockLedger::restore($item);
                }
            }

            $order->status = Order::STATUS_RETURNED;
            $order->returned_at = now();
            $order->save();

            $return->update([
                'status' => OrderReturn::STATUS_REFUNDED,
                'restocked' => $restock,
                'refund_amount' => optional($order->payment)->amount ?? $order->gross_total,
                'decided_at' => now(),
            ]);

            return $return;
        });

        // Outside the transaction: the return stands even if PayPal or email is unavailable.
        $this->refunder->handle($order, "Order {$order->order_number} returned");

        // Cash on delivery can't be refunded through a provider: an admin sends it by hand,
        // then records it with "Refund sent".
        $payment = $order->payment()->first();
        if ($payment && $payment->isCashOnDelivery() && $payment->status === \App\Models\OrderPayment::STATUS_PAID) {
            $payment->update(['status' => \App\Models\OrderPayment::STATUS_REFUND_PENDING]);
        }
        $this->notifier->returnApproved($order->fresh());

        return $return->fresh();
    }

    public function reject(Order $order, string $note): OrderReturn
    {
        $return = DB::transaction(function () use ($order, $note) {
            $return = OrderReturn::where('order_id', $order->id)->lockForUpdate()->first();
            if (!$return || !$return->isOpen()) {
                throw new ReturnNotAllowedException('This return has already been decided.');
            }

            $return->update([
                'status' => OrderReturn::STATUS_REJECTED,
                'admin_note' => $note,
                'decided_at' => now(),
            ]);

            return $return;
        });

        $this->notifier->returnRejected($order->fresh());

        return $return->fresh();
    }
}
