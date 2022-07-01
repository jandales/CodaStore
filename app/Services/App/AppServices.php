<?php

namespace App\Services\App;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BaseServices;
use Illuminate\Support\Facades\Cookie;

class AppServices  extends BaseServices
{
    public function setCookie()
    {        
      
        if(Cookie::has('cart-id')) return true;

        $minutes = (60 * 24) * 7;  
        $timestamp = Carbon::now()->timestamp;  
        $value = $timestamp;        
        $response = new Response('Hello World');
        $response->withCookie(cookie('cart-id', $value, $minutes));    

        Cart::create([    
            'cart_id' => $value,
            'expired_at' =>  Carbon::parse($timestamp)->addDays(5),
        ]); 

        return $response;
    }

    public function search(Request $request)
    {        
        return Product::PublishedSearch($request->keyword)->paginate(16);
        
    }

}