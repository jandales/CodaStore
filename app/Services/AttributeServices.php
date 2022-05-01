<?php

namespace App\Services;

use App\Models\Attribute;

class AttributeServices 
{
    public function store($request)
    {         
        return Attribute::Create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => slug($request->name)
        ]);
        
    }

    public function update(Attribute $attribute, $request)
    {
        $attribute->name = $request->name;
        $attribute->description =  $request->description;
        $attribute->slug = slug($request->name);
        $attribute->save();
        return $attribute;
    }

    public function destroySelected($list)
    {
        foreach($list as $slug) {
            $attribute = Attribute::where('slug',$slug)->first();
            $attribute->delete();
        }
    }

    
}