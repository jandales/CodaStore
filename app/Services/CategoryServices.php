<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Traits\ImageUploadTrait;

class CategoryServices 
{
    use ImageUploadTrait;
    private $location = '/img/collection/';

    public function store($request)
    {     
        $path = $this->upload($request, $this->location);

        $category = Category::Create([
                'name' => $request->name,
                'description' => $request->description,
                'slug' => slug($request->name),
                'image' => $path,
        ]);

        return $category;
    }

    public function update(Category $category, $request)
    {
      
        $path = $this->upload($request, $this->location);  
     
        if ($path) $this->unlinkImage($category->image);            
           
        $category->name = $request->name;
        $category->description = $request->description;
        $category->slug = slug($request->name);
        $category->image = $path ?? $category->image;
        $category->save();

        return $category;
    }
    
    public function destroy(Category $category)
    {
        $this->unlinkImage($category->image);
        $category->delete();
    }

    public function selectedDestroy($list)
    {
        foreach($list as $id)
        {
            $category = Category::find($id);
            $this->destroy($category);
        }
    }


 

    
}