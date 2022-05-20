<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckOutServices;
use App\Http\Requests\CheckoutStoreRequest;

class CheckOutController extends Controller
{
   private $services;

   public function __construct(CheckOutServices $services)
   {
       $this->services = $services;
   }

   public function index()
   {   
        $result = $this->services->information();
        return view('checkout.information')->with(['cart' => $result->cart,'email' => $result->email,'address' => $result->address]);
    }

    public function shipping(Request $request)
    {  
       $result = $this->services->shipping($request);    
       return view('checkout.shipping')->with(['cart' => $result->cart,'shipping_methods' => $result->shipping_methods]);
    }

    public function payment(Request $request)
    {
       $result =  $this->services->payment($request);
       return view('checkout.payment')->with(['cart' => $result->cart,'user_payment_option' => $result->user_payment_option ]);
    }


    public function store(CheckoutStoreRequest $request)
    {  
        $this->services->store($request);
        return response()->json(['url' => route('checkout.shipping'), 'status' =>  200]);        
    }
  

    public function updateShippingMethod(Request $request)
    {   
        $this->services->updateShippingMethod($request);
        return redirect()->route('checkout.payment');
    }

    public function completed()
    {       
        return view('checkout.completed');
    }

    
   
}
