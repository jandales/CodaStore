<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $products;
    public $threshold;

    public function __construct(Collection $products, int $threshold)
    {
        $this->products = $products;
        $this->threshold = $threshold;
    }

    public function build()
    {
        $soldOut = $this->products->filter(fn ($p) => $p->stockQty() <= 0)->count();
        $subject = "{$this->products->count()} products low on stock" . ($soldOut ? " ({$soldOut} sold out)" : '');

        return $this->subject($subject)->markdown('emails.admin.low-stock', [
            'products' => $this->products,
            'threshold' => $this->threshold,
        ]);
    }
}
