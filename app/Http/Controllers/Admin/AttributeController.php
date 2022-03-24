<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Http\Requests\NameRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AttributeServices;



class AttributeController extends Controller
{
    private $services;

    public function  __contruct(AttributeServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        return view('admin.attributes');
    }
    public function getAttributes()
    {
        $attributes = Attribute::with('variants')->get();
        return response()->json(['attributes'=> $attributes]);
    }    
    public function store(NameRequest $request)
    {    
        $this->services->store($request);     
        return response()->json(['status' => 'success','message' => 'Attribute Successfully Created']);
    }
    public function edit(Attribute $Attribute)
    {   
        return response()->json(['attributes' => $Attribute]);
    }
    public function update(NameRequest $request,Attribute $attribute)
    {    
        $this->services->update($attribute, $request);
        return response()->json(['status' => 'success','message' => 'Attribute Successfully update']);
    }
    public function destroy(Attribute $Attribute)
    {     
        $Attribute->delete();        
        return response()->json(['status' => 'success','message' => 'Attribute Successfully Deleted']);
    }
    public function Attributes(Product $product)
    {     
        return view('admin.products.Attribute')->with('product',$product);
    }
    public function Attribute($id)
    {
        $Attribute = Attribute::find($id);
        return  response()->json($Attribute->name);
    }  

    public function variants(Attribute $attribute)
    {      
        return  response()->json(['attribute' => $attribute, 'variants' => $attribute->variants]);
    }
 
}
