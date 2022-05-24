<?php

namespace App\Http\Controllers\Admin;


use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;
use App\Services\Admin\StockServices;


class StockController extends Controller
{  
    private $services;
    
    public function __construct(StockServices $services)
    {
        $this->services = $services;
    }

    public function edit(Stock $stock)
    {   
        return view('admin.stock.edit')->with('stock', $stock);
    }

    public function update(StockRequest $request, Stock $stock)
    {
       $this->services->update($request, $stock);   
       return back()->with('success', 'Quantity Successfully Updated');       
    }

    public function updateQuantity(StockRequest $request, Stock $stock)
    {   
        $stock = $this->services->updateQuantity($request, $stock);
        return response()->json(['status' => 200, 'stock' => $stock]);        
    }

    public function inventory()
    {             
        $products = Product::with('category','stock')->paginate(10);
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => '' ]);
    }

    public function filter($filter)
    {          
        $products = Product::FilterByCategory($filter)->paginate(10);           
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => $filter, 'keyword' => '']);
    }

    public function search(Request $request)
    {  
        $products = Product::with('stock')->where('name',$request->keyword)->orWhere('sku', $request->keyword)->paginate(10);              
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => $request->keyword ]); 
    }


   


  

}
