<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Services\AttributeServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;

class AttributeController extends Controller
{
    private $services;

    public function  __construct(AttributeServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {      
        return view('admin.attribute.index')->with('keyword', null);
    }
     
    public function store(StoreAttributeRequest $request)
    {       
        $this->services->store($request);  

        return back()->with(['status' => 'success','message' => 'Attribute Successfully Created']);
    }
    public function edit(Attribute $attribute)
    {  
        return view('admin.attribute.edit')->with('attribute', $attribute);        
    }

    public function update(UpdateAttributeRequest $request,Attribute $attribute)
    {    
        $this->services->update($attribute, $request);

        return redirect()->route('admin.attributes')->with(['status' => 'success','message' => 'Attribute Successfully update']);
    }
    public function destroy(Attribute $attribute)
    {     
        $this->authorize('delete', $attribute);

        $attribute->delete();    

        return redirect()->route('admin.attributes')->with(['status' => 'success','message' => 'Attribute Successfully Deleted']);
    }

    public function destroySelected(Request $request)
    {     
        $attribute = Attribute::find($request->_selected[0]);

        $this->authorize('delete', $attribute);

        $this->services->destroySelected($request->_selected); 

        return route('admin.attributes');
    }

    public function search(Request $request)
    {
        $attributes = Attribute::Search($request->keyword)->get();

        return view('admin.attribute.index')->with(['attributes' =>  $attributes, 'keyword' => $request->keyword]);
    }

    public function getAll()
    {
        $attributes = Attribute::get();

        return response()->json(['attributes' => $attributes]);    
    }


 
}
