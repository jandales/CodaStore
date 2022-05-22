<?php

use App\Models\Variant;
use App\Models\Category;
use App\Models\SocialSite;
use App\Models\GeneralSetting;
use App\Models\UserShippingAddress;

function siteSettings(){
    return GeneralSetting::find(1);
}
function socialList() {
   return SocialSite::get();
}

function shippingAddress() {
    return  auth()->user()->shippingAddress;
}

function paymentOptions() {
    return auth()->user()->payment_options;
}

function timezone_list() {
    $return = array();
    $timezone_identifiers_list = timezone_identifiers_list();
    foreach($timezone_identifiers_list as $timezone_identifier){
        $date_time_zone = new DateTimeZone($timezone_identifier);
        $date_time = new DateTime('now', $date_time_zone);
        $hours = floor($date_time_zone->getOffset($date_time) / 3600);
        $mins = floor(($date_time_zone->getOffset($date_time) - ($hours*3600)) / 60);
        $hours = 'GMT' . ($hours < 0 ? $hours : '+'.$hours);
        $mins = ($mins > 0 ? $mins : '0'.$mins);
        $text = str_replace("_"," ",$timezone_identifier);
        
        //$dateTime = new DateTime(); 
        //$dateTime->setTimeZone(new DateTimeZone($timezone_identifier)); 
        //$short_timezone = $dateTime->format('T'); 

        $array=array();
        $array['display']= $text.' ('.$hours.':'.$mins.')';
        $array['value']=$timezone_identifier;
        //$array['short_timezone']=$short_timezone;
        $return[] = $array; 
    }
    return json_decode(json_encode($return));
}



function list_date_format(){
    $lists = [
        [
            'id' => 0, 
            'format' => 'April 22, 2022',
            'code' =>  'F j, Y',
        ],
        [
            'id' => 1, 
            'format' => '2022-04-21',
            'code' =>  'Y-m-d',
        ],
        [
            'id' => 2, 
            'format' => '04/21/2022',
            'code' =>  'm/d/y',
        ],
        [
            'id' => 3, 
            'format' => '21/04/2022',
            'code' =>  'd/m/y',
        ],
    ];

    return json_decode(json_encode($lists));
}

function list_time_format()
{
    $lists = [
        [
            'id' => 0, 
            'format' => '2:37 pm',
            'code' =>  'g:i a',
        ],
        [
            'id' => 1, 
            'format' => '2:37 PM',
            'code' =>  'g:i A',
        ],
        [
            'id' => 2, 
            'format' => '14:37',
            'code' =>  'H:i',
        ],
       
    ];

    return json_decode(json_encode($lists));
}


function getShippingOptions($id)
{  
    foreach(shippingOptions() as $option)
    {
        if($option->id == $id) 
            return json_encode($option);
    }

}

function tax($amount)
{
    $tax = 12 / 100;
    $total = $amount * $tax;
    return $total;    
}






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



function shippingFee(){
    return 150;
}


function checkoutProgress()
{
    if ( request()->is('checkout/information') ) return 0;

    if ( request()->is('checkout/shipping') ) return 30;

    if ( request()->is('checkout/payment') ) return 60;

    if ( request()->is('checkout/completed') ) return 100;

}

function checkoutProgressPassed()
{
    if ( request()->is('checkout/information') ) return 'active';

    if ( request()->is('checkout/shipping') ) return 'active';

    if ( request()->is('checkout/payment') ) return 'active';

    if ( request()->is('checkout/completed') ) return 'active';

}


