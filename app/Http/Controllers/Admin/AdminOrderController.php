<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AdminOrderServices;

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
