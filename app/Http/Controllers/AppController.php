<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;     
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class AppController extends Controller
{
    public function index()
    {       
        return view('index');
    }

    public function setCartCookie()
    {        
      
        if(Cookie::has('cart-id')) return;

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
        $products = Product::PublishedSearch($request->keyword)->paginate(16);
        return view('search')->with(['products' => $products, 'keyword' => $request->keyword]);
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }




}
