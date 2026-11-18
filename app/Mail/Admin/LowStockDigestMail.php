<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Concerns\SendsInBackground;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsInBackground;

    public $products;
    public $threshold;

    public function __construct(Collection $products, int $threshold)
    {
        $this->products = $products;
        $this->threshold = $threshold;
    }

    public function build()
    {
        $soldOut = $this->products->filter(fn ($item) => $item->qty <= 0)->count();
        $subject = "{$this->products->count()} " . ($this->products->count() === 1 ? 'item' : 'items') . " low on stock" . ($soldOut ? " ({$soldOut} sold out)" : '');

        return $this->subject($subject)->markdown('emails.admin.low-stock', [
            'products' => $this->products,
            'threshold' => $this->threshold,
        ]);
    }
}
