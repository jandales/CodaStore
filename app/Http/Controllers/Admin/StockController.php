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

    /** Adjust stock: Stock In, Stock Out, mark as damaged, repaired, write off. */
    public function adjust(Request $request, Product $product, \App\Services\Products\StockAdjuster $adjuster)
    {
        $data = $request->validate([
            'action' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Services\Products\StockAdjuster::ACTIONS))],
            'qty' => ['required', 'integer', 'min:1', 'max:100000'],
            'sku_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'action.required' => 'Choose what happened to the stock.',
            'qty.min' => 'Enter a quantity of at least 1.',
        ]);

        $stock = $adjuster->adjust($product, $data['sku_id'] ?? null, $data['action'], (int) $data['qty'], $data['note'] ?? null);

        return response()->json(['status' => 200, 'stock' => $stock, 'message' => \App\Services\Products\StockAdjuster::ACTIONS[$data['action']][0] . ': saved.']);
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
            ->where('reason', '!=', 'opening')->get(['change', 'damaged_change', 'reason']);
        $available = $variant ? max(0, (int) $variant->qty) : $product->stockQty();
        $damaged = $variant ? max(0, (int) $variant->damaged_qty) : $product->damagedQty();

        return view('admin.stock.history', [
            'product' => $product,
            'variant' => $variant,
            'movements' => $movements,
            'inStock' => $available,
            'damaged' => $damaged,
            'onHand' => $available + $damaged,
            'damagedRecently' => (int) $recent->where('reason', 'damaged')->sum('damaged_change'),
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
        } elseif ($filter === 'damaged') {
            // Only the variants (or products) with damaged units.
            $damaged = Product::where(fn ($q) => $q->whereHas('skus', fn ($s) => $s->where('damaged_qty', '>', 0))
                ->orWhereHas('stock', fn ($s) => $s->where('damaged_qty', '>', 0)));
            $rows = InventoryRows::paginate($damaged, fn ($row) => $row->damaged > 0);
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
