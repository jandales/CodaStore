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

    public function show($user_id, $id) 
    {
        return response()->json(Order::with('items','items.product', 'shipping', 'billing', 'payment', 'shippingMethod')
                                     ->withCount('items')
                                     ->where(['user_id' => $user_id, 'id' => $id ])
                                     ->first());
                                  
    }
}
