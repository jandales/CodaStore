<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReturnRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $this->order->loadMissing('items.product', 'shipping', 'payment', 'returnRequest', 'shippingMethod');

        return $this->subject("Update on your return for order {$this->order->order_number}")
            ->markdown('emails.orders.return-rejected', ['order' => $this->order, 'return' => $this->order->returnRequest]);
    }
}
