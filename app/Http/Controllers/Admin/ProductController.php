<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;


class ProductController extends Controller
{
    private $services;

    public function __construct(ProductServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {    
        $products = Product::with('category','stock')->paginate(10);    
        return view('admin.products')->with(['products' => $products, 'filter' => 'all']);
    }

    public function filter($filter)
    {         
        if ($filter == 'published')
            $products = Product::with('category','stock')->where('status', 1)->paginate(10);
        else if ($filter == 'unpublished')
            $products = Product::with('category','stock')->where('status', 0)->paginate(10);
        else if ($filter == 'featured-products')
            $products = Product::with('category','stock')->where('featured', 1)->paginate(10);
        else
            $products = Product::with('category','stock')->paginate(10);   

        return view('admin.products')->with(['products' => $products, 'filter' => $filter]);
    }

    public function create()
    { 
        return view('admin.products.create');
    }   

    public function store(ProductRequest $request)
    {      
       $this->services->store($request);  
       return response()->json(['status' => 200, 'message' =>  'Product succesfully create']);
    }
    
    public function edit(Product $product)
    {          
        return view('admin.products.edit')->with('product',$product);             
    }

    public function update(ProductRequest $request, Product $product)
    {   
        $this->services->update($request, $product);        
        return response()->json(['status' => 200, 'message' =>  'Product succesfully update']);
    }

    public function destroy(Product $product)
    {          
        $this->services->destroy($product);
        return back()->with('success', 'Successfully Deleted');
    } 

    public function destroySelectedItem(Request $request)
    {
        $this->services->destroySelectedItem($request->input('selected'));    
        return back()->with('success', 'Successfully Deleted');
    }

    public function changeStatus(Product $product)
    {    
        $this->services->changeStatus($product);
        return back()->with('success', 'Item successfully updated');
    }

    public function changeSelectedItemStatus(Request $request, $status)
    {
        $this->services->changeSelectedItemStatus($request->input('selected'), $status);
        return back()->with('success', 'Successfully published');
    } 

    public function search(Request $request)
    { 
        $products = Product::search($request->keyword)->with('category','stock')->paginate(10);        
        return view('admin.products.search')->with(['products' => $products, 'search' => $request->keyword]);
    }

    public function find(Request $request)
    {        
        $product = Product::with('stock')->where('name',$request->keyword)->orWhere('sku', $request->keyword)->first();              
        return response()->json(['product' => $product]);    
    }

    

  


   

 

  
     
}
