<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Wishlist::with('product.stock')
            ->where('user_id', auth()->id())
            ->whereHas('product')
            ->latest()
            ->get();

        return view('account.wishlist')->with('items', $items);
    }

    /** Save or un-save a product (heart button). */
    public function toggle(Product $product)
    {
        $existing = Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from your wishlist';
        } else {
            Wishlist::firstOrCreate(['user_id' => auth()->id(), 'product_id' => $product->id]);
            $message = 'Saved to your wishlist';
        }

        if (request()->expectsJson()) {
            return response()->json(['saved' => !$existing, 'message' => $message]);
        }

        return back()->with('wishlist_message', $message);
    }

    public function destroy(Product $product)
    {
        Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->delete();

        return back()->with('wishlist_message', 'Removed from your wishlist');
    }
}
