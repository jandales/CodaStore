<?php

namespace App\Services;


use App\Models\Attribute;



class AttributeServices 
{
    public function store($request)
    {
        $result = Attribute::Exist($request->name)->first();
        if($result) return response()->json(['status' => 'error', 'message' => 'Attribute already exist']);      
        return Attribute::Create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => makeSlug($request->name, $request->slug)
        ]);
        
    }

    public function update(Attribute $attribute, $request)
    {
        $attribute->name = $request->name;
        $attribute->description =  $request->description;
        $attribute->slug =  $request->slug;
        $attribute->save();
        return $attribute;
    }

    
}