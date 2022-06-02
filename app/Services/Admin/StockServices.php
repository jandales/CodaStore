<?php 

namespace App\Services\Admin;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;

class StockServices
{
   

    public function list()
    {
        return Product::with('category','stock')->paginate(app_per_page());

    }

    public function update(Request $request, Stock $stock)
    {
        $stock->qty += $request->qty;
        $stock->remarks = $request->remarks;         
        $stock->save();     
           
    }

    public function updateQuantity(Request $request, Stock $stock)
    {     
        $qty = (int)$request->qty;

        if ($request->action == 1)
            $stock->qty += $qty;
        else  
            $stock->qty = $stock->qty < $qty ? 0 : $stock->qty - $qty;    

        $stock->save();

        return $stock;     
    }



  
}