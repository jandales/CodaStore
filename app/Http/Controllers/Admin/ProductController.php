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
        $products = $this->services->list();   
         
        return view('admin.products.index')->with(

            ['products' => $products, 

            'filter' => 'all',

            'keyword' => null,

        ]);
    }

    public function filter($filterBy, $value)
    {          
        $result  = $this->services->filter($filterBy, $value); 

        return view('admin.products.index')->with([

            'products' => $result["products"],

            'filter' => $result["filter"],

             'keyword' => null,

        ]);
    }

    public function create()
    {         
        return view('admin.products.create');
    }   

    public function store(ProductRequest $request)
    {      
      $product = $this->services->store($request);  
       
       return response()->json(['status' => 200, 'message' =>  'Product succesfully create', 'route' => route('admin.products.edit',[$product->encryptedId()])]);
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
        $this->authorize('delete', $product);
      
        $this->services->destroy($product);

        return redirect()->route('admin.products')->with('success', 'Successfully Deleted');
    } 

    public function destroySelectedItem(Request $request)
    {
       
        $product =  Product::find($request->input('selected')[0]);

        $this->authorize('delete',  [$product]);

        $this->services->destroySelectedItem($request->input('selected')); 

        return redirect()->route('admin.products')->with('success', 'Successfully Deleted');
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
        $products = $this->services->search($request);

        return view('admin.products.index')->with([
            'products' => $products, 
            'keyword' => $request->keyword,
            'filter' => 'all'
        ]);
    }

    public function find(Request $request)
    {        
        $product = Product::with('stock')->where('name',$request->keyword)
                            ->orWhere('sku', $request->keyword)->first();              

        return response()->json(['product' => $product]);    
    }

    public function getProduct(Request $request)
    {        
        $products = Product::with('stock')->where('name',$request->keyword)
                            ->orWhere('sku', $request->keyword)->get(); 
                                         
        return response()->json(['products' => $products]);    
    }


    

  


   

 

  
     
}
