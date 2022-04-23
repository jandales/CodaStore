<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutStoreRequest;

class CheckOutController extends Controller
{
   
    public function index()
    {        
        $cart = Self::validateCart();

        if(!$cart) return redirect()->route('cart'); 

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

        return view('checkout.information')->with([
            'cart' => $cart,  
            'email' => $email ?? auth()->user()->email,
            'address' => $address ?? auth()->user()->shippingDefaultAddress(),                
        ]);

     
    }

    public function shipping(Request $request)
    {  
        $cart = Self::validateCart();

        if(!$cart) return redirect()->route('cart');    

        $shipping_methods = ShippingMethod::get();  
        session()->get('shipping_method') ?? session(['shipping_method' => $shipping_methods[0]]);
        session()->get('shipping_charge') ?? session(['shipping_charge' => $shipping_methods[0]->amount]);    
    
        return view('checkout.shipping')->with([
            'cart' => $cart,           
            'shipping_methods' => $shipping_methods,
        ]);

    }

    public function payment(Request $request)
    {
        $cart = Self::validateCart();

        if(!$cart) return redirect()->route('cart');    

        $user_payment_option = auth()->user()->defaultPayment()->first();     

        return view('checkout.payment')->with([
            'cart' => $cart,
            'user_payment_option' => $user_payment_option,                      
        ]);
    }


    public function store(CheckoutStoreRequest $request)
    {   
        session([
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

        return response()->json(['url' => route('checkout.shipping'), 'status' =>  200]);
        
    }
  

    public function updateShippingMethod(Request $request)
    {   
        $id = $request->shipping_method;
        $shipping_method = ShippingMethod::find($id);        
        session(['shipping_method' => $shipping_method]);
        session(['shipping_charge' => $shipping_method->amount]); 
        return redirect()->route('checkout.payment');
    }

    public function completed()
    {       
        return view('checkout.completed');
    }

    public function validateCart()
    {
        $cart = Cart::ByUser()->first();        
        if(empty($cart)) return false; 
        if($cart->items->count() == 0) return false;
        return $cart;       
    }
   
}
