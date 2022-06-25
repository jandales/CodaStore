<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UserPaymentOptionServices;

class UserPaymentOptionController extends Controller
{
    private $services;

    public function __construct(UserPaymentOptionServices $services)
    {
        $this->services = $services;
    }
    
    public function index($user_id, $id)
    {  
        $paymentOption =  $this->services->index($user_id, $id);
        return response()->json($paymentOption);
    }
    
    public function store(StorePaymentOptionRequest $request)
    {  
        $option = $this->services->store($request);  
        return response()->json($option);
       
    }

    public function update(UpdatePaymentOptionRequest $request, $user_id, $id)
    {    
        $option = $this->services->update($request, $user_id, $id);
        return response()->json($option);      
    }

    public function destroy($user_id, $id)
    {         
        $option = $this->services->getPaymentOption($user_id,$id);
        $option->delete();  
        return response()->json($option);
    }

    public function updateStatus()
    {  
        $option = $this->services->updateStatus($user_id,$id);
        return response()->json($option);
      
    }
}
