<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::with('items','items.product')->withCount('items')->get());
    }

    public function show($id) 
    {
        $order = Self::find($id);
        return response()->json($order);
                                  
    }


    private function find($id){
        return Order::with('items','items.product', 'shipping', 'billing', 'payment', 'shippingMethod')
                ->withCount('items')
                ->orWhere('id',$id)
                ->orWhere('order_number',$id)
                ->first();
    }

    public function cancel($id)
    {
        $order =  $order = Self::find($id);

        $status = $order->status;

        if($status != 'confirmed') return;

        $order->status = 'cancelled';
        $order->cancelled_at = now();
        $order->save();

        return response()->json($order);
        
        
    }

}
