<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Concerns\SendsInBackground;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReturnApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsInBackground;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $this->order->loadMissing('items.product', 'shipping', 'payment', 'returnRequest', 'shippingMethod');

        return $this->subject("Return for order {$this->order->order_number} approved")
            ->markdown('emails.orders.return-approved', ['order' => $this->order, 'return' => $this->order->returnRequest]);
    }
}
