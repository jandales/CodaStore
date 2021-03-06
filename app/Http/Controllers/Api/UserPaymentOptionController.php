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
    
    public function index()
    {  
        $paymentOption =  $this->services->getPaymentOptions(auth()->user()->id);
        return response()->json($paymentOption);
    }
    
    public function store(StorePaymentOptionRequest $request)
    {  
        $option = $this->services->store($request, auth()->user()->id);  
        return response()->json($option);
       
    }

    public function show($id)
    {
        return response()->json($this->services->getPaymentOption(auth()->user()->id, $id));
    }

    public function default()
    {
        return response()->json($this->services->getDefault(auth()->user()->id));
    }

    public function findByCardNumber($user_id,$card_number)
    {
        $option = $this->services->getPaymentOptionByCardNumber(auth()->user()->id, $card_number);
        return response()->json($option);
    }

    public function update(Request $request, $id)
    {    
        $validated = $request->validate([
            'card_name' => 'required',
            'card_number'  => "required|min:16|unique:user_payment_options,card_number,". $id,
            'card_expire_date' => 'required',
            'card_cvc' => 'required|min:3|max:3',    
        ]);
      
        $option = $this->services->getPaymentOption(auth()->user()->id, $id);
        $option = $this->services->update($request, $option);
        return response()->json($option);      
    }

    public function destroy($id)
    {    
        $option = $this->services->getPaymentOption(auth()->user()->id,$id);
        $option->delete();  
        return response()->json($option);
    }

    public function setActive($id)
    {  
   
        $option = $this->services->getPaymentOption(auth()->user()->id, $id);
        $option = $this->services->updateStatus($option, auth()->user()->id);
        return response()->json($option);
      
    }
}
