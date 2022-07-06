<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Services\CheckOutServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutStoreRequest;

class CheckoutController extends Controller
{
    private $services;

    public function __construct(CheckOutServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
    
        $result = $this->services->information();     
        return response()->json($result);    
    }

    public function store(CheckoutStoreRequest $request)
    { 
       $request->session()->put([
            'email' => $request->email,
            'shipping_method' => session('shipping_method') ?? null,
            'shipping_address' => [
                'firstname' =>  $request->firstname,
                'lastname' => $request->lastname,
                'street' => $request->street,
                'city' => $request->city,
                'phone' => $request->phone,
                'country' => $request->country,
                'region' => $request->region,
                'zipcode' => $request->zipcode,
            ]
        ]);     

       return response()->json(200);    
              
    }

    public function shipping(Request $request)
    {  
        $email = $request->session()->get('email');
        $address = $request->session()->get('shipping_address');
    
        $shipping_methods = ShippingMethod::get();  
        $method = session()->get('shipping_method') ?? session(['shipping_method' => $shipping_methods[0]]);      
        return response()->json(['email' => $email, 'address' => $address, 'method'=> $method]);
    }

    public function updateShippingMethod(Request $request)
    {           
        $this->services->updateShippingMethod($request);        
        return response()->json(200);
    }

}
