<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Services\ShopServices;
use App\Models\ProductAttribute;

class ShopController extends Controller
{
    private $services;
    private $perpage;

    public function __construct(ShopServices $services)
    {
        $this->services = $services;
        $this->perpage = config('setting.shop.perpage');
    }

    public function index(Request $request)
    {
        $products = $this->services->filter($request->all());
        return view('shop')->with(['products' => $products, 'category' => 'All Collection']);
    }

    public function category(Request $request, Category $category)
    {
        $products = $this->services->filter($request->all(), $category);
        return view('shop')->with(['products' => $products, 'category' => $category->name]);
    }

    public function view(Product $product)
    {
        // Drafts are not visible in the shop.
        abort_unless((int) $product->status === 1, 404);

        return view('product')->with('product', $product);
    }

    /** Old sort links (/shop/sort-by/{value}) now redirect to the filterable shop. */
    public function sortBy($value)
    {
        $sort = ShopServices::LEGACY_SORTS[$value] ?? 'newest';

        return redirect()->route('shop', $sort === 'newest' ? [] : ['sort' => $sort], 301);
    }

    public function hasVariants(Product $product)
    {
        return $this->services->hasVariants($product);
    }

}
