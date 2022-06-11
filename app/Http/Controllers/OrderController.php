<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\OrderServices;

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

    public function message(Order $order)
    {
        $this->authorize('clientUpdate', $order);

        return view('message')->with('order',$order);
    }

    public function cancel(Order $order)
    {       
        $this->authorize('clientUpdate', $order);

        $this->services->cancel($order);

        return back()->with('success',"You successfully cancelled your order");
    }

}
