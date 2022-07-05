<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;

class CheckOutServices
{
    public function information()
    {        
         $cart = Self::validateCart();
 
         if(!$cart) return false;
 
         session(['shipping_charge' => 0]);
        
         if(session()->get('shipping_address') )
         {
             $email = session()->get('email');
             $address = (object)[
                 'firstname' => session()->get('shipping_address')['firstname'],
                 'lastname' => session()->get('shipping_address')['lastname'],
                 'street' =>  session()->get('shipping_address')['street'],
                 'city' =>  session()->get('shipping_address')['city'],
                 'phone' => session()->get('shipping_address')['phone'], 
                 'country' => session()->get('shipping_address')['country'],
                 'region' =>  session()->get('shipping_address')['region'],
                 'zipcode' =>  session()->get('shipping_address')['zipcode'], 
            ];  
         }
         
         return (object)['cart' => $cart, 'email' => $email ?? auth()->user()->email, 'address' => $address ?? auth()->user()->shippingDefaultAddress()];      
     }
 
    public function store(Request $request)
    {   
    
        return session([
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

      
        
    }

    public function shipping($request)
    {  
        $cart = Self::validateCart();

        if(!$cart) return false;   

        $shipping_methods = ShippingMethod::get();  
        session()->get('shipping_method') ?? session(['shipping_method' => $shipping_methods[0]]);
        session()->get('shipping_charge') ?? session(['shipping_charge' => $shipping_methods[0]->amount]); 

        return (object)['cart' => $cart, 'shipping_methods' => $shipping_methods];
       

    }

    public function payment(Request $request)
    {
        $cart = Self::validateCart();

        if(!$cart) return false;

        $user_payment_option = auth()->user()->defaultPayment()->first();     
        return (object)['cart' => $cart,'user_payment_option' => $user_payment_option];      
    }

    public function updateShippingMethod(Request $request)
    {   
        $id = $request->shipping_method;
        $shipping_method = ShippingMethod::find($id);        
        session(['shipping_method' => $shipping_method]);
        session(['shipping_charge' => $shipping_method->amount]); 
       
    }

    private function validateCart()
    {
        $cart = Cart::ByUser()->first();   

        if (empty($cart) || $cart->items->count() == 0) return false; 

        return $cart;       
    }
}