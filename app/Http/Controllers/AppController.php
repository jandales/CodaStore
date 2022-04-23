<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class AppController extends Controller
{
    public function index()
    {
        
        $products = Product::paginate(8); 
        return view('index')->with('products', $products);
    }

    public function setCartCookie()
    {        
        if(Cookie::has('cart-id')) return;
            
        $minutes = (60 * 24) * 7;  
        $value = Carbon::now()->timestamp;          
        $response = new Response('Hello World');
        $response->withCookie(cookie('cart-id', $value, $minutes));
        return $response;
    }

    
    public function search(Request $request)
    {        
        $products = Product::PublishedSearch($request->keyword)->paginate(16);
        return view('search')->with('products',$products);
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'content' => 'required'
        ]);
        return $request->all();
    }
}
