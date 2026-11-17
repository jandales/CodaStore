<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /** The signed-in user's orders only. */
    private function mine()
    {
        return Order::where('user_id', auth()->id());
    }

    public function index()
    {
        return response()->json($this->mine()->with('items', 'items.product')->withCount('items')->latest()->get());
    }

    public function show($id)
    {
        return response()->json($this->find($id));
    }

    private function find($id)
    {
        return $this->mine()
            ->with('items', 'items.product', 'shipping', 'billing', 'payment', 'shippingMethod')
            ->withCount('items')
            ->where(function ($query) use ($id) {
                $query->where('id', $id)->orWhere('order_number', $id);
            })
            ->firstOrFail();
    }

    public function cancel($id)
    {
        $order = $this->find($id);

        try {
            $order = app(\App\Services\Orders\CancelOrder::class)->handle($order);
        } catch (\App\Exceptions\OrderNotCancellableException $e) {
            return response()->json(['message' => "This order can't be cancelled because it has already been {$order->status}."], 422);
        }

        return response()->json($order);
    }
}
