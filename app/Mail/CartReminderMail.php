<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CartReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function build()
    {
        $items = $this->cart->items->filter(fn ($item) => $item->product && (int) $item->product->status === 1 && !$item->product->isSoldOut());

        return $this->subject('You left something in your bag')
            ->markdown('emails.cart.reminder', [
                'user' => $this->cart->user,
                'items' => $items,
                // Opens this bag in whatever browser the customer uses (they sign in first if needed).
                'bagUrl' => URL::temporarySignedRoute('cart.restore', now()->addDays(14), [$this->cart->id]),
                'optOutUrl' => URL::signedRoute('cart.reminders.off', [$this->cart->user->encryptedId()]),
            ]);
    }
}
