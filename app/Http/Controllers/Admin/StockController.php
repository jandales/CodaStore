<?php

namespace App\Http\Controllers\Admin;


use App\Models\Stock;
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
    public function inventory()
    {
        return view('admin.products.inventory');
    }


   


  

}
