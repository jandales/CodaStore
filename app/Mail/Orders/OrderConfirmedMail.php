<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $this->order->loadMissing('items.product', 'shipping', 'payment', 'shippingMethod');

        return $this->subject("Order {$this->order->order_number} confirmed")
            ->markdown('emails.orders.confirmed', ['order' => $this->order]);
    }
}
