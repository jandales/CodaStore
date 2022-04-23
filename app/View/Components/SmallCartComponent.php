<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SmallCartComponent extends Component
{
    public $cart;   
    public $shipping_charge; 
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($cart,$shippingcharge)
    {
        $this->cart = $cart;
        $this->shipping_charge = $shippingcharge;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.small-cart-component');
    }
}
