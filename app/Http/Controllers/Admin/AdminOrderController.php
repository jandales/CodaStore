<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AdminOrderServices;
use App\Models\OrderReturn;
use App\Services\Orders\ProcessReturn;
use App\Exceptions\ReturnNotAllowedException;

class AdminOrderController extends Controller
{
    private $services;

    public function __construct(AdminOrderServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        $orders = $this->services->list(); 

        return view('admin.orders.index')->with('orders', $orders);
    }  

    public function listbyStatus($status)
    {        
        $orders = $this->services->list($status);

        return view('admin.orders.index')->with('orders', $orders);
    }

    public function show(Order $order)
    {    
        return view('admin.orders.show')->with('order' , $order);
    } 

    public function updateStatus(\App\Http\Requests\UpdateOrderStatusRequest $request, Order $order)
    {
        $order = $this->services->updateStatus($order);

        $messages = [
            Order::STATUS_SHIPPED => 'Order marked as shipped. The customer has been emailed the tracking number.',
            Order::STATUS_COMPLETED => 'Order marked as delivered.',
            Order::STATUS_CANCELLED => 'Order cancelled. Stock was restored and the customer has been emailed.',
        ];

        return back()->with('success', $messages[$order->status] ?? 'Order status updated');
    }

    /** Orders with a return waiting for a decision. */
    public function returns()
    {
        $orders = Order::with('user', 'items', 'returnRequest')
            ->whereHas('returnRequest', fn ($q) => $q->where('status', OrderReturn::STATUS_REQUESTED))
            ->latest()
            ->paginate(config('setting.app.perpage'));

        return view('admin.orders.index')->with('orders', $orders);
    }

    public function approveReturn(Request $request, Order $order, ProcessReturn $returns)
    {
        try {
            $returns->approve($order, $request->boolean('restock'));
        } catch (ReturnNotAllowedException $e) {
            return back()->with('error', $e->getMessage());
        }

        $payment = optional($order->payment()->first())->status;
        $message = $payment === 'refund_failed'
            ? 'Return approved, but the PayPal refund failed. Refund it by hand in PayPal.'
            : 'Return approved. The customer was refunded and emailed.';

        return back()->with($payment === 'refund_failed' ? 'error' : 'success', $message);
    }

    public function rejectReturn(Request $request, Order $order, ProcessReturn $returns)
    {
        $data = $request->validate(
            ['admin_note' => 'required|string|max:1000'],
            ['admin_note.required' => 'Tell the customer why the return was declined.']
        );

        try {
            $returns->reject($order, $data['admin_note']);
        } catch (ReturnNotAllowedException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return declined. The customer was emailed your note.');
    }

    public function invoice(Order $order)
    {
        return $this->document($order, 'invoice');
    }

    public function packingSlip(Order $order)
    {
        return $this->document($order, 'packing-slip');
    }

    private function document(Order $order, string $type)
    {
        $order->loadMissing('items.product', 'shipping', 'billing', 'payment', 'shippingMethod');

        return view('orders.document', [
            'order' => $order,
            'type' => $type,
            'backUrl' => route('admin.orders.show', [$order->encryptedId()]),
        ]);
    }

    public function search(Request $request)
    {
        $data = $this->services->search($request);

        return view('admin.orders.index')->with(['orders' => $data['orders'], 'keyword' => $data['keyword']]);        
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        $this->services->destroy($order);

        return back()->with('success','Order status successfully deleted');
    }

    public function deliver()
    {
       $this->services->deliver();
    }
}
