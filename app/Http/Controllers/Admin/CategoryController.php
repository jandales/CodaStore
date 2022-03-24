<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    private $services;
    public  function __construct(CategoryServices $services)
    {
        $this->services = $services;
    }
    public function lists()
    {    
        return response()->json(['categories' => Category::all()]);      
    }
    public function index()
    {            
        return view('admin.category.index');
    }      
    public function store(CategoryRequest $request)
    { 
        $services->store($request);
        return response()->json([ 'status'=> 200 , 'message' => 'Category Successfully Create' ]);
    }
    public function edit(Category $category)
    {
        return response()->json([ 'category'=> $category]);
    }

    public function update(CategoryRequest $request, Category $category)
    {        
        $services->update($category, $request);
        return response()->json([ 'status'=> 200 , 'message' => 'Category '. $category->name .'  successfully update' ]);
    }
    public function destroy(Category $category)
    {
        $category->delete();    
        return response()->json(['status' => 'success', 'message' => $category->name . 'Successfully Deleted']);        
    }
    public function search(Request $request)
    {       
        $categories = Category::search($request->search)->get();      
        return response()->json(['categories' => $categories]);    
    }
   
}
