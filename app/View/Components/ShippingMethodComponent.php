<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ShippingMethodComponent extends Component
{
    public $shippingmethods;
    public $active;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($shippingmethods, $active)
    {
       $this->shippingmethods =  $shippingmethods;
       $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.shipping-method-component');
    }
}
