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
    public function index()
    {
        $orders = Order::with('user', 'items')->paginate(10);
        return view('admin.orders.index')->with('orders', $orders);
    }

    public function listbyStatus($status)
    {
        $orders = Order::where('status', $status)->with('user', 'items')->paginate(10);
        return view('admin.orders')->with('orders', $orders);
    }

    public function show(Order $order)
    {    
        return view('admin.orders.show')->with('order' , $order);
    }

    public function toShip(Order $order, AdminOrderServices $service)
    {       
        $service->updateStatus($order);
        return redirect()->route("admin.orders.show",[$order]);
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $array = str_split($keyword);
        $id = $array[count($array) - 1];
        $orders =  Order::Search($id)->paginate(10);
        return view('admin.orders.index')->with(['orders' => $orders, 'keyword' => $keyword]);
        
    }

    public function deliver(AdminOrderServices $service)
    {
       $service->deliver();
    }
}
