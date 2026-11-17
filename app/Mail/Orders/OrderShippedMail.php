<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable implements ShouldQueue
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

        return $this->subject("Your order {$this->order->order_number} is on its way")
            ->markdown('emails.orders.shipped', ['order' => $this->order]);
    }
}
