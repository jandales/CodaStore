<?php

namespace App\Console\Commands;

use App\Mail\CartReminderMail;
use App\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Emails signed-in customers who left items in their bag for a day without ordering.
 * Each bag gets at most one reminder; adding to it again allows another one later.
 */
class SendCartReminders extends Command
{
    protected $signature = 'store:cart-reminders';

    protected $description = 'Email customers about items left in their bag';

    public function handle()
    {
        $carts = Cart::query()
            ->whereNotNull('user_id')
            ->whereNull('reminded_at')
            ->whereBetween('updated_at', [now()->subDays(7), now()->subDay()])
            ->whereHas('items')
            ->whereHas('user', fn ($q) => $q->where('cart_reminders', true))
            ->with('user', 'items.product.stock')
            ->get();

        $sent = 0;
        foreach ($carts as $cart) {
            // Skip if they ordered after they last touched the bag (e.g. on another device).
            if ($cart->user->orders()->where('created_at', '>=', $cart->updated_at)->exists()) {
                $cart->forceFill(['reminded_at' => now()])->saveQuietly();
                continue;
            }

            // Only remind about things that can still be bought.
            if ($cart->items->filter(fn ($item) => $item->product && (int) $item->product->status === 1 && !$item->product->isSoldOut())->isEmpty()) {
                continue;
            }

            try {
                Mail::to($cart->user->email)->send(new CartReminderMail($cart));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            $cart->forceFill(['reminded_at' => now()])->saveQuietly();
        }

        $this->info("Sent {$sent} bag reminder" . ($sent === 1 ? '' : 's') . '.');

        return 0;
    }
}
