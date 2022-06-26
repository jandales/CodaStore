<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UserPaymentOptionServices;
use App\Http\Requests\StorePaymentOptionRequest;
use App\Http\Requests\UpdatePaymentOptionRequest;

class UserPaymentOptionController extends Controller
{
    private $services;

    public function __construct(UserPaymentOptionServices $services)
    {
        $this->services = $services;
    }
    
    public function index($user_id)
    {  
        $paymentOption =  $this->services->getPaymentOptions($user_id);
        return response()->json($paymentOption);
    }
    
    public function store(StorePaymentOptionRequest $request, $user_id)
    {  
        $option = $this->services->store($request, $user_id);  
        return response()->json($option);
       
    }

    public function show($user_id, $id)
    {
        return response()->json($this->services->getPaymentOption($user_id, $id));
    }

    public function findByCardNumber($user_id,$card_number)
    {
        $option = $this->services->getPaymentOptionByCardNumber($user_id, $card_number);
        return response()->json($option);
    }

    public function update(Request $request, $user_id, $id)
    {    
        $validated = $request->validate([
            'card_name' => 'required',
            'card_number'  => "required|min:16|unique:user_payment_options,card_number,". $id,
            'card_expire_date' => 'required',
            'card_cvc' => 'required|min:3|max:3',    
        ]);
      
        $option = $this->services->getPaymentOption($user_id, $id);
        $option = $this->services->update($request, $option);
        return response()->json($option);      
    }

    public function destroy($user_id, $id)
    {         
        $option = $this->services->getPaymentOption($user_id,$id);
        $option->delete();  
        return response()->json($option);
    }

    public function setActive($user_id, $id)
    {  
        $option = $this->services->getPaymentOption($user_id, $id);
        $option = $this->services->updateStatus($option, $user_id);
        return response()->json($option);
      
    }
}
