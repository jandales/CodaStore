<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

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

        if(!$result) return redirect()->route('cart'); 

        return view('checkout.information')->with(['cart' => $result->cart,'email' => $result->email,'address' => $result->address]);
    }

    public function shipping(Request $request)
    {  
       $result = $this->services->shipping($request); 

       if(!$result) return redirect()->route('cart'); 

       return view('checkout.shipping')->with(['cart' => $result->cart,'shipping_methods' => $result->shipping_methods]);
    }

    public function payment(Request $request)
    {
       $result =  $this->services->payment($request);

       if(!$result) return redirect()->route('cart'); 

       // Is cash on delivery offered for this bag? (Checked again when the order is placed.)
       $cod = app(\App\Services\Payments\CashOnDelivery::class);
       $codReason = null;
       try {
           $quote = app(\App\Services\PlaceOrderServices::class)->quote();
           $codReason = $cod->unavailableReason($quote['total'], $quote['shipping_method_id'], $request->user());
       } catch (\Throwable $e) {
           $codReason = $cod->enabled() ? null : 'Cash on delivery is not available right now.';
       }

       return view('checkout.payment')->with([
           'cart' => $result->cart,
           'codEnabled' => $cod->enabled(),
           'codFee' => $cod->fee(),
           'codReason' => $codReason,
       ]);
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
