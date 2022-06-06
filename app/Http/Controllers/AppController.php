<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\App\AppServices;

class AppController extends Controller
{
    private $services;

    public function __construct(AppServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {       
        return view('index');
    }

    public function setCartCookie()
    {   
        return $this->services->setCartCookie();
    }

    
    public function search(Request $request)
    {        
        $products = $this->services->search($request);
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
