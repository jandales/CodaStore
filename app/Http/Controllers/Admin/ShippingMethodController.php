<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingMethodRequest;
use App\Services\Admin\ShippingMethodServices;

class ShippingMethodController extends Controller
{
    private $services;

    public function __construct(ShippingMethodServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        $shipping_methods = ShippingMethod::all();
        return view('admin.setting.shipping.index')->with('shipping_methods', $shipping_methods);
    }

    public function create()
    {
        return view('admin.setting.shipping.create');
    }

    public function store(ShippingMethodRequest $request)
    {
       $this->services->store($request);
       return redirect()->route('admin.shipping.method');    
    }

    public function edit(ShippingMethod $method)
    {
        return view('admin.setting.shipping.edit')->with('shipping_method',$method);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $method)
    {
       
        $this->services->update($request, $method); 
        return redirect()->route('admin.shipping.method');   
    }

    public function destroy(ShippingMethod $method)
    {        
        $method->delete();
        return redirect()->route('admin.shipping.method');  
    }

    public function selected_destroy(Request $request)
    {
        $this->services->selected_destroy($request);
        return redirect()->route('admin.shipping.method');
        
    }

    public function update_status(ShippingMethod $method, $status)
    {        
        $this->services->update_status($method, $status);
        return redirect()->route('admin.shipping.method');  
    }

    

    public function selected_update_status(Request $request)
    {       
        $this->services->selected_update_status($request); 
        return redirect()->route('admin.shipping.method');
        
    }

    public function getShippingMethod(ShippingMethod $shipping_method)
    {
        return response()->json(['shipping_method' => $shipping_method]);
    }

   
}
