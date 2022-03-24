<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'orderProducts')->paginate(10);
        return view('admin.orders')->with('orders', $orders);
    }

    public function listbyStatus($status)
    {
        $orders = Order::where('status', $status)->with('user', 'orderProducts')->paginate(10);
        return view('admin.orders')->with('orders', $orders);
    }

    public function show(Order $order)
    {    
        return view('admin.orders.show')->with('order' , $order);
    }

    public function toShip(Order $order, AdminOrderServices $service)
    {       
        $service->updateStatus();
        return redirect()->route("admin.orders.show",[$order]);
    }

    public function search(Request $request)
    {
        $array = str_split($request->search);
        $id = $array[count($array) - 1];
        $orders =  Order::Search($id)->paginate(10);
        return view('admin.orders.search')->with('orders', $orders);
        
    }

    public function deliver(AdminOrderServices $service)
    {
       $service->deliver();
    }
}
