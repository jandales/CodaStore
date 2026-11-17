<?php

namespace App\Services;

use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderShippedMail;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the customer emails for an order. Emails are queued, and a mail failure is logged
 * instead of thrown, so it can never undo a payment, a shipment or a cancellation.
 */
class OrderNotifier
{
    public function confirmed(Order $order): void
    {
        $this->send($order, new OrderConfirmedMail($order));
    }

    public function shipped(Order $order): void
    {
        $this->send($order, new OrderShippedMail($order));
    }

    public function cancelled(Order $order): void
    {
        $this->send($order, new OrderCancelledMail($order));
    }

    /** The address the customer used at checkout, falling back to their account email. */
    public function recipient(Order $order): ?string
    {
        return optional($order->billing)->email ?: optional($order->user)->email;
    }

    private function send(Order $order, Mailable $mail): void
    {
        $to = $this->recipient($order);
        if (!$to) {
            logger()->warning('Order email not sent: no recipient', ['order' => $order->order_number, 'mail' => get_class($mail)]);
            return;
        }

        try {
            Mail::to($to)->queue($mail);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
