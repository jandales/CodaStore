<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\View\Component;

class FeaturedProductComponent extends Component
{
    public $products;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->products = Product::where('featured', 1)->take(10)->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.featured-product-component');
    }
}
