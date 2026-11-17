<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AdminOrderServices;
use App\Models\OrderReturn;
use App\Models\OrderNote;
use App\Models\ActivityLog;
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
        // Notes and activity for this order, newest first, in one timeline.
        $timeline = $order->notes()->with('admin')->get()->map(fn ($note) => ['type' => 'note', 'at' => $note->created_at, 'item' => $note])
            ->concat(ActivityLog::for($order)->with('admin')->get()->map(fn ($log) => ['type' => 'log', 'at' => $log->created_at, 'item' => $log]))
            ->sortByDesc('at')
            ->values();

        return view('admin.orders.show')->with(['order' => $order, 'timeline' => $timeline]);
    }

    public function storeNote(Request $request, Order $order)
    {
        $data = $request->validate(['body' => 'required|string|max:2000'], ['body.required' => 'Write the note first.']);

        $order->notes()->create(['admin_id' => auth('admin')->id(), 'body' => trim($data['body'])]);

        return back()->with('success', 'Note added.');
    }

    public function destroyNote(Order $order, OrderNote $note)
    {
        $admin = auth('admin')->user();
        abort_unless($note->order_id === $order->id, 404);
        abort_unless($note->admin_id === $admin->id || $admin->is_admin(), 403);

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }

    public function updateStatus(\App\Http\Requests\UpdateOrderStatusRequest $request, Order $order)
    {
        if ($request->status === Order::STATUS_CANCELLED && !auth('admin')->user()->hasPermission('refunds')) {
            return back()->with('error', 'Cancelling refunds the customer, which needs the Refunds permission.');
        }

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
        abort_unless(auth('admin')->user()->hasPermission('refunds'), 403);

        try {
            $return = $returns->approve($order, $request->boolean('restock'));
            ActivityLog::record('order.return.approved', $order, 'Return approved: refund of ₱' . number_format((float) $return->refund_amount, 2) . ($return->restocked ? ', items restocked' : ', not restocked'));
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
        abort_unless(auth('admin')->user()->hasPermission('refunds'), 403);

        $data = $request->validate(
            ['admin_note' => 'required|string|max:1000'],
            ['admin_note.required' => 'Tell the customer why the return was declined.']
        );

        try {
            $returns->reject($order, $data['admin_note']);
            ActivityLog::record('order.return.rejected', $order, 'Return declined: ' . $data['admin_note']);
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
