<?php

namespace App\View\Components;

use App\Models\Category;
use Illuminate\View\Component;

class CollectionComponent extends Component
{
    public $collection;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->collection =  Category::get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.collection-component');
    }
}
