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

    public function updateStatus(Order $order)
    {
        $this->services->updateStatus($order);
        return back()->with('success','Order status successfully updated');
    }

    public function search(Request $request)
    {
        $data = $this->services->search($request);
        return view('admin.orders.index')->with(['orders' => $data['orders'], 'keyword' => $data['keyword']]);        
    }

    public function deliver()
    {
       $this->services->deliver();
    }
}
