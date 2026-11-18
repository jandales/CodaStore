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
        return Product::with('category','stock','skus')->paginate(app_per_page());

    }

    public function update(Request $request, Stock $stock)
    {
        \App\Services\Products\StockMovements::track($stock->product_id, (int) $request->qty >= 0 ? 'stock_in' : 'stock_out', function () use ($request, $stock) {
            $stock->qty += $request->qty;
            $stock->remarks = $request->remarks;
            $stock->save();
        }, ['note' => $request->remarks]);

    }

    public function updateQuantity(Request $request, Stock $stock)
    {
        $qty = (int)$request->qty;
        $in = $request->action == 1;

        \App\Services\Products\StockMovements::track($stock->product_id, $in ? 'stock_in' : 'stock_out', function () use ($stock, $qty, $in) {
            $stock->qty = $in ? $stock->qty + $qty : max(0, $stock->qty - $qty);
            $stock->save();
        }, ['note' => $request->remarks]);

        return $stock;
    }




}