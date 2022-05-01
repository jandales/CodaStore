<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\updateCategoryRequest;

class CategoryController extends Controller
{
    
    private $services;

    public  function __construct(CategoryServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {            
        return view('admin.category.index')->with('keyword', null);
    }      
    public function store(CategoryRequest $request)
    {   
        $this->services->store($request);
        return redirect()->back()->with(['status' => 'success', 'message' => 'Successfully Created']);  
    
    }
    public function edit(Category $category)
    {        
        return view('admin.category.edit')->with('category', $category);
    }

    public function update(updateCategoryRequest $request, Category $category)
    {             
        $this->services->update($category, $request);
        return redirect()->route('admin.categories')->with(['status' => 'success', 'message' => 'Successfully Updated']); 
    }
    public function destroy(Category $category)
    {   
        $this->services->destroy($category);
        return redirect()->back()->with(['status' => 'success', 'message' => 'Successfully Deleted']);       
    }

    public function destroySelected(Request $request)
    {
        $this->services->selectedDestroy($request->selectedId); 
        return route('admin.categories');    
    }
    public function search(Request $request)
    {       
      
        $categories = Category::search($request->keyword)->get();      
        return view('admin.category.index')->with(['categories' => $categories, 'keyword' => $request->keyword]);   
    }   
   
}
