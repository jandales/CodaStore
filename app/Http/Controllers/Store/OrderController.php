<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;


use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\OrderServices;
use App\Models\OrderReturn;
use Illuminate\Validation\Rule;
use App\Services\Orders\ProcessReturn;
use App\Exceptions\ReturnNotAllowedException;

class OrderController extends Controller
{
    private $services;

    public function __construct(OrderServices $services)
    {
        $this->services = $services;
    }

    public function index($status)
    {  
        $this->authorize('clientViewAny', Order::class);

        $orders = $this->services->listOrders($status);

        return view('account.orders')->with('orders', $orders);
    }

    public function review(Product $product)
    {     
        $this->authorize('clientCreate', Order::class);

        $review = $this->services->review($product);

        return view('account.order.review')->with(['review' =>  $review, 'product' => $product]);  
    }

    public function details(Order $order)
    {    
        $this->authorize('clientView', $order);

        return view('account.order.detail')->with('order', $order);
    }

    public function invoice(Order $order)
    {
        $this->authorize('clientView', $order);

        $order->loadMissing('items.product', 'shipping', 'billing', 'payment', 'shippingMethod');

        return view('orders.document', [
            'order' => $order,
            'type' => 'invoice',
            'backUrl' => route('orders.details', [$order->encryptedId()]),
        ]);
    }

    public function requestReturn(Request $request, Order $order, ProcessReturn $returns)
    {
        $this->authorize('clientView', $order);

        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(OrderReturn::REASONS))],
            'details' => ['nullable', 'string', 'max:1000', 'required_if:reason,other'],
        ], [
            'reason.required' => 'Choose a reason for the return.',
            'details.required_if' => 'Tell us a little about why you are returning it.',
        ]);

        try {
            $returns->request($order, $data['reason'], $data['details'] ?? null);
        } catch (ReturnNotAllowedException $e) {
            return back()->with('error', "This order can't be returned. Returns are accepted within " . config('store.return_days') . ' days of delivery.');
        }

        return back()->with('success', "We've received your return request. We'll email you once it's reviewed.");
    }

    public function message(Order $order)
    {
        $this->authorize('clientUpdate', $order);

        return view('message')->with('order',$order);
    }

    public function cancel(Order $order)
    {       
        $this->authorize('clientUpdate', $order);

        try {
            $this->services->cancel($order);
        } catch (\App\Exceptions\OrderNotCancellableException $e) {
            return back()->with('error', "This order can't be cancelled because it has already been {$order->status}.");
        }

        return back()->with('success', 'Your order has been cancelled');
    }

}
