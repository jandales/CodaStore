<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AttributeTableComponent extends Component
{
    public $list;
    public $keyword;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($list, $keyword = null)
    {
        $this->list = $list;
        $this->keyword = $keyword;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.attribute-table-component');
    }
}
