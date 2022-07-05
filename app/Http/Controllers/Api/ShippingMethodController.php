<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Http\Controllers\Controller;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shipping_methods = ShippingMethod::where('status',1)->get();
        return response()->json($shipping_methods);
    }

    public function show($id)
    {
        $shipping_methods = ShippingMethod::find($id); 
        return response()->json($shipping_methods);
    }
}
