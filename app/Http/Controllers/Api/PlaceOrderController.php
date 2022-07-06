<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PlaceOrderServices;
use App\Http\Requests\PaymentRequest;

class PlaceOrderController extends Controller
{
    public function store(PaymentRequest $request, PlaceOrderServices $service)
    {  
        
        try {
            $order = $service->storeOrder($request);   
            return response()->json([               
                'message' => 'order successfully create',
                'order' => $order,
            ]);         
        } catch (Exception $e) {
            return response()->json(['error' => 'server.error', 500]);  
        } 
    }
}
