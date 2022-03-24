<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Http\Controllers\Controller;
use App\Http\Requests\VariantRequest;

class VariantController extends Controller
{ 
    public function store(Request $request)
    {     
        $result = Variant::Exist($request->name)->first();  
             
        if($result) return response()->json(['status' => 'error', 'message' => 'Attribute already exist']);
        
        $variant = Variant::Create([
            'attribute_id' => $request->attribute_id,
            'value' => $request->name,           
        ]);
        return response()->json(['status' => 'success', 'variant' => $variant]);
    }
    public function edit(Variant $variant)
    {   
        return response()->json(['attributes' => $variant]);
    }
    
    public function destroy(Variant $variant)
    {     
        $variant->delete();        
        return response()->json(['status' => 'success','message' => 'Varaint Successfully Deleted']);
    }
    
}
