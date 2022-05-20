<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\OrderProduct;
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
        $orders = $this->services->listOrders($status);
        return view('account.orders')->with('orders', $orders);
    }

    public function review(Product $product)
    {     
        $review = $this->services->review($product);
        return view('account.order.review')->with(['review' =>  $review, 'product' => $product]);  
    }

    public function details(Order $order)
    {    
        return view('account.order.detail')->with('order', $order);
    }

    public function message(Order $order)
    {
        return view('message')->with('order',$order);
    }

    public function cancel(Order $order)
    {       
        $this->services->cancel($order);
        return back()->with('success',"You successfully cancelled your order");
    }



}
