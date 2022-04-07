<?php

use App\Models\Variant;
use App\Models\Category;


function category($id)
{
    $name = "";

    switch($id)
    {
        case  1 :
            return "Male";
        break;

        case  2 :
                return "Female";
        break;

        default : 
            return "Uncategories";
        break;
    }
}

function categories()
{
   return Category::all();   
}

function variants()
{
   return Variant::all();
}

function cartSubtotal($carts)
{
    $subtotal = 0;

    foreach($carts as $cart)
    {
        
        $subtotal += $cart->total();
    }
     return $subtotal;
}

function newQuantity($currentQty, $newQty)
{  
 
   if($newQty < 0) return $currentQty;  

   return $newQty;

}

function shippingFee(){
    return 150;
}


function checkoutProgress()
{
    if ( request()->is('checkout/information') ) return 25;

    if ( request()->is('checkout/shipping') ) return 50;

    if ( request()->is('checkout/payment') ) return 70;

}

function checkoutProgressPassed()
{
    if ( request()->is('checkout/information') ) return 'active';

    if ( request()->is('checkout/shipping') ) return 'active';

    if ( request()->is('checkout/payment') ) return 'active';

}


