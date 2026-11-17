<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlaceOrderServices;
use App\Http\Requests\PaymentRequest;

class PlaceOrderController extends Controller
{ 
    public function store(PaymentRequest $request, PlaceOrderServices $service)
    {  
        try {
            $order = $service->storeOrder($request);   
            return response()->json([
                'status' => 200, 
                'message' => 'order successfully create',
                'route' => route('checkout.completed'),
            ]);         
        } catch (\App\Exceptions\OutOfStockException $e) {
            return response()->json(['status' => 422, 'message' => $e->getMessage(), 'route' => route('cart')], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 422, 'message' => $e->getMessage(), 'route' => route('cart')], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 500, 'message' => 'Something went wrong. Please try again.', 'route' => route('server.error')], 500);
        }
    }

   
    

  

    
}
