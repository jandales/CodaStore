<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CategoryTableComponent extends Component
{
    public $categories;
    public $keyword;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($categories, $keyword = null)
    {
        $this->categories =  $categories;
        $this->keyword = $keyword;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.category-table-component');
    }
}
