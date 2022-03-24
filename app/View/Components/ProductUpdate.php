<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\View\Component;

class ProductUpdate extends Component
{
    public $product; 
    public $wishlist;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($productId,$wishlist)
    {     
        $this->wishlist = $wishlist;
        $this->product  = Product::find($productId);       
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-update');
    }
}
