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

    public function inventory()
    {
        $products = $this->services->list();
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => null ]);
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

    /** Stock in / out for one variant (same request and response as updateQuantity). */
    public function updateVariantQuantity(\App\Http\Requests\StockRequest $request, \App\Models\ProductSku $sku)
    {
        $qty = (int) $request->qty;
        $sku->qty = (int) $request->action === 1 ? $sku->qty + $qty : max(0, $sku->qty - $qty);
        $sku->save();
        \App\Services\Products\ProductSkus::refreshTotals($sku->product);

        return response()->json(['status' => 200, 'stock' => ['qty' => $sku->qty]]);
    }

    /** "Stock checked" for a product whose stock was split evenly across sizes. */
    public function markReviewed(Product $product)
    {
        $product->forceFill(['variant_stock_reviewed' => true])->saveQuietly();

        return back()->with('success', 'Marked ' . $product->name . ' as checked.');
    }

    public function productsFilterListLink()
    {

    }



    public function filter($filter)
    {
        if ($filter === 'low-stock') {
            $products = Product::with(['category', 'stock', 'skus'])->lowStock(\App\Models\GeneralSetting::lowStockThreshold())->paginate(10);
        } elseif ($filter === 'needs-review') {
            $products = Product::with(['category', 'stock', 'skus'])->where('variant_stock_reviewed', false)->paginate(10);
        } else {
            $products = Product::FilterByCategory($filter)->with('skus')->paginate(10);
        }
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => $filter, 'keyword' => '']);
    }

    public function search(Request $request)
    {
        $products = Product::with('stock', 'skus', 'category')->where('name',$request->keyword)->orWhere('sku', $request->keyword)->paginate(10);
        return view('admin.products.inventory')->with(['products' => $products, 'filter' => 0, 'keyword' => $request->keyword ]);
    }







}
