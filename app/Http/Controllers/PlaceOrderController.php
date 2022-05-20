<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlaceOrderServices;
use App\Http\Requests\PaymentRequest;

class PlaceOrderController extends Controller
{ 
    public function store(PaymentRequest $request, PlaceOrderServices $service)
    {      
        $order = $service->storeOrder($request);

        return response()->json([
            'status' => 200, 
            'message' => 'order successfully create',
            'route' => route('checkout.completed'),
        ]);

    }

   
    

  

    
}
