<?php

namespace App\Services\Orders;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\OrderPayment;

/** Refunds an order's PayPal payment in full and records the result on the payment. */
class RefundPayment
{
    private $gateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @return bool|null true refunded, false the gateway refused (refund it by hand), null nothing to refund
     */
    public function handle(Order $order, string $note): ?bool
    {
        $payment = $order->payment()->first();
        if (!$payment || $payment->status !== OrderPayment::STATUS_PAID || $payment->provider !== $this->gateway->name()) {
            return null;
        }

        $refunded = $this->gateway->refund(
            (string) $payment->reference_number,
            (float) $payment->amount,
            config('paypal.currency', 'PHP'),
            $note
        );

        $payment->status = $refunded ? OrderPayment::STATUS_REFUNDED : OrderPayment::STATUS_REFUND_FAILED;
        $payment->save();

        if (!$refunded) {
            logger()->error('Automatic refund failed; refund manually in PayPal', [
                'order' => $order->order_number,
                'capture_id' => $payment->reference_number,
                'note' => $note,
            ]);
        }

        return $refunded;
    }
}
