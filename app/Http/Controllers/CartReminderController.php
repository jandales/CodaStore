<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class CartReminderController extends Controller
{
    /** Link in the reminder email: makes this bag the current one in this browser. */
    public function restore(Request $request, Cart $cart)
    {
        abort_unless($cart->user_id && auth()->id() === (int) $cart->user_id, 403);

        $cart->forceFill(['reminded_at' => null])->saveQuietly();

        return redirect()->route('cart')->withCookie(cookie('cart-id', $cart->cart_id, 60 * 24 * 30));
    }

    /** "Turn them off" link in the reminder email. */
    public function off(User $user)
    {
        $user->forceFill(['cart_reminders' => false])->save();

        return view('newsletter.reminders-off');
    }
}
