<?php

namespace App\Http\Controllers\Admin;


use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;


class StockController extends Controller
{  

    public function edit(Stock $stock)
    {   
        return view('admin.stock.edit')->with('stock', $stock);
    }

    public function update(StockRequest $request, Stock $stock)
    {
        $stock->qty += $request->qty;
        $stock->remarks = $request->remarks;         
        $stock->save();     
        return back()->with('success', 'Quantity Successfully Updated');       
    }

    public function updateQuantity(StockRequest $request, Stock $stock)
    {     
        $qty = (int)$request->qty;

        if ($request->action == 1)
            $stock->qty += $qty;
        else  
            $stock->qty = $stock->qty < $qty ? 0 : $stock->qty - $qty;    

        $stock->save();

        return response()->json(['status' => 200, 'stock' => $stock]);        
    }

    public function inventory()
    {             
        $products = Product::with('category','stock')->paginate(10);
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => '' ]);
    }

    public function filter($filter)
    {  
        if ($filter == 'all')
            $products = Product::with('category','stock')->paginate(10);  
        else
            $products = Product::FilterByCategory($filter)->paginate(10);
            
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => $filter, 'keyword' => '']);
    }

    public function search(Request $request)
    {  
        $products = Product::with('stock')->where('name',$request->keyword)->orWhere('sku', $request->keyword)->paginate(10);              
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => $request->keyword ]); 
    }


   


  

}
