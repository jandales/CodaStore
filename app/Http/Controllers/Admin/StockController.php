<?php

namespace App\Http\Controllers\Admin;


use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;
use App\Services\Admin\StockServices;
use App\Services\Admin\InventoryRows;


class StockController extends Controller
{
    private $services;

    public function __construct(StockServices $services)
    {
        $this->services = $services;
    }

    public function inventory()
    {
        $rows = InventoryRows::paginate(Product::query());

        return view('admin.products.inventory')->with(['rows' => $rows, 'filter' => 'all', 'keyword' => null]);
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
        $in = (int) $request->action === 1;
        \App\Services\Products\StockMovements::track($sku->product_id, $in ? 'stock_in' : 'stock_out', function () use ($sku, $qty, $in) {
            $sku->qty = $in ? $sku->qty + $qty : max(0, $sku->qty - $qty);
            $sku->save();
            \App\Services\Products\ProductSkus::refreshTotals($sku->product);
        }, ['note' => $request->remarks]);

        return response()->json(['status' => 200, 'stock' => ['qty' => $sku->qty]]);
    }

    /** Stock details: the history of every stock change, per variant (or for the product). */
    public function history(Request $request, Product $product)
    {
        $product->load(['skus' => fn ($q) => $q->orderBy('position'), 'stock', 'category']);
        $variant = $request->filled('variant') ? $product->skus->firstWhere('id', (int) $request->query('variant')) : null;

        $scope = fn ($q) => $q->where('product_id', $product->id)
            ->when($variant, fn ($q) => $q->where('product_sku_id', $variant->id));

        $movements = \App\Models\StockMovement::with(['order', 'admin'])
            ->where($scope)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $recent = \App\Models\StockMovement::where($scope)->where('created_at', '>=', now()->subDays(30))
            ->where('reason', '!=', 'opening')->get(['change']);

        return view('admin.stock.history', [
            'product' => $product,
            'variant' => $variant,
            'movements' => $movements,
            'inStock' => $variant ? max(0, (int) $variant->qty) : $product->stockQty(),
            'added' => (int) $recent->where('change', '>', 0)->sum('change'),
            'removed' => (int) -$recent->where('change', '<', 0)->sum('change'),
        ]);
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
        $threshold = \App\Models\GeneralSetting::lowStockThreshold();

        if ($filter === 'low-stock') {
            // Only the variants (or products) that are actually low.
            $rows = InventoryRows::paginate(Product::lowStock($threshold), fn ($row) => $row->qty <= $threshold);
        } elseif ($filter === 'needs-review') {
            $rows = InventoryRows::paginate(Product::where('variant_stock_reviewed', false));
        } elseif ($filter === 'all') {
            $rows = InventoryRows::paginate(Product::query());
        } else {
            $rows = InventoryRows::paginate(Product::whereHas('category', fn ($q) => $q->where('slug', $filter)->orWhere('name', $filter)));
        }

        return view('admin.products.inventory')->with(['rows' => $rows, 'filter' => $filter, 'keyword' => '']);
    }

    /** Search by product name, product SKU or variant SKU. */
    public function search(Request $request)
    {
        $keyword = trim((string) $request->keyword);
        $like = '%' . addcslashes($keyword, '%_\\') . '%';

        $needle = mb_strtolower($keyword);
        $rows = InventoryRows::paginate(
            Product::where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('skus', fn ($s) => $s->where('sku', 'like', $like));
            }),
            // A product name / SKU match shows all its rows; a variant SKU match shows just that variant.
            fn ($row) => $needle === ''
                || str_contains(mb_strtolower($row->product->name), $needle)
                || str_contains(mb_strtolower((string) $row->product->sku), $needle)
                || ($row->sku && str_contains(mb_strtolower((string) $row->sku->sku), $needle))
        );

        return view('admin.products.inventory')->with(['rows' => $rows, 'filter' => 0, 'keyword' => $keyword]);
    }







}
