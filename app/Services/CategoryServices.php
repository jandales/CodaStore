<?php

namespace App\Services;

use App\Models\Category;

class CategoryServices 
{
    public function store($request)
    {
      if(Category::Exist($request->name)) return response()->json([ 'status' => 500 , 'message' => 'Category already Exist' ]);
        
      return Category::Create([
                'name' => $request->name,
                'description' => $request->description,
                'slug' => makeSlug($name,$request->slug)
            ]);
    }

    public function update(Category $category, $request)
    {
        $category->name = $request->name;
        $category->description = $request->description;
        $category->slug = makeSlug($request->name, $request->slug);
        $category->save();
        return $category;
    }
}