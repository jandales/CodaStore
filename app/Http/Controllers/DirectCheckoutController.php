<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class DirectCheckoutController extends Controller
{
    public function index(Product $product, Request $request)
    {    
        $properties = json_decode($request->properties, TRUE); 
        return view('checkoutDirect')->with(['product' =>  $product, 
                                             'qty' => $request->qty,
                                             'properties'=> $properties
                                            ]);
    }
}
