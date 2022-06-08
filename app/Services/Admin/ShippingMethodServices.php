<?php 
namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;

class ShippingMethodServices
{
    public function store(Request $request)
    {
       return  ShippingMethod::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'status' => $request->status,
        ]);
    }

    public function update(Request $request, ShippingMethod $method)
    {
       
        $method->name = $request->name;
        $method->description = $request->description;
        $method->amount =$request->amount;
        $method->status =$request->status;
        $method->save();        
          
    }
    public function selected_destroy(Request $request)
    {
        foreach($request->selected as $method_id)
        {         
            $method = ShippingMethod::find($method_id);
            $method->delete();
        }
      
        
    }

    public function update_status(ShippingMethod $method, $status)
    {        
        $method->status = $status;
        $method->save();
     
    }

    

    public function selected_update_status(Request $request)
    {     
        foreach($request->selected as $id)
        {         
            $method = ShippingMethod::find(decrypt($id));
            $method->status = $request->status;
            $method->save();
        }
        
    }

    public function search($keyword)
    {
        return  ShippingMethod::query()->where('name', 'like', '%'  . $keyword .  '%')
                                       ->orWhere('amount', 'like', '%'  . $keyword .  '%')
                                       ->orWhere('description', 'like', '%'  . $keyword .  '%')
                                       ->get();
    }

    
}