<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class TabNavigation extends Component
{
    public $product;
    public $active;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($product, $active)
    {
        $this->product = $product;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.tab-navigation');
    }
}
