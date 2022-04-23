<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingMethodRequest;

class ShippingMethodController extends Controller
{
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
        ShippingMethod::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.shipping.method');    
    }

    public function edit(ShippingMethod $method)
    {
        return view('admin.setting.shipping.edit')->with('shipping_method',$method);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $method)
    {
       
        $method->name = $request->name;
        $method->description = $request->description;
        $method->amount =$request->amount;
        $method->status =$request->status;
        $method->save();        
        return redirect()->route('admin.shipping.method');   
    }

    public function destroy(ShippingMethod $method)
    {        
        $method->delete();
        return redirect()->route('admin.shipping.method');  
    }

    public function selected_destroy(Request $request)
    {
        foreach($request->selected as $method_id)
        {         
            $method = ShippingMethod::find($method_id);
            $method->delete();
        }
        return redirect()->route('admin.shipping.method');
        
    }

    public function update_status(ShippingMethod $method, $status)
    {        
        $method->status = $status;
        $method->save();
        return redirect()->route('admin.shipping.method');  
    }

    

    public function selected_update_status(Request $request)
    {       
        $request->validate([
            'status' => 'required',
        ]);
      
        foreach($request->selected as $method_id)
        {         
            $method = ShippingMethod::find($method_id);
            $method->status = $request->status;
            $method->save();
        }

        return redirect()->route('admin.shipping.method');
        
    }

    public function getShippingMethod($id)
    {
        $shipping_method = ShippingMethod::find($id);
        return response()->json(['shipping_method' => $shipping_method]);
    }

   
}
