<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormBillingComponent extends Component
{
    public $address;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($address = null)
    {
        $this->address = $address;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form-billing-component');
    }
}
