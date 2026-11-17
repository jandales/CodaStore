<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductListComponent extends Component
{
    public $products;
    public $filterable;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($products, $filterable = true)
    {
        $this->products = $products;
        $this->filterable = (bool) $filterable;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-list-component');
    }
}
