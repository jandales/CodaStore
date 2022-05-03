@extends('layout.admin')
@section('content')

<div class="container">
    <div class="page-title">
        <h1>Add Products</h1>
    </div>
    <div id="notify-message" class="m-t-2">
        
    </div>


    <form id="form" method="post"  action="{{route('admin.products.store')}}" enctype="multipart/form-data">
        @csrf
        <div class="page-container m-t-2">  
            
            <div class="left-column">
                <div class="panel">
                <div class="panel-body">
                        <div class="form-block">
                            <label for="name">Name</label>
                            <input type="text" name="name" value="">                 
                        </div>
                        <div class="form-block">
                            <label for="email">Short Description</label>
                            <textarea id="short_description" name="short_description"  cols="30" rows="3"></textarea>                
                        </div>
                        <div class="form-block">
                            <label for="email">Long Description</label>
                            <textarea id="long_description" name="long_description"  cols="30" rows="10"></textarea>                
                        </div>
                </div>
                </div>
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Pricing</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-inline gap30">
                            <div class="form-block col-6">
                                <label for="name">Price</label>
                                <input type="number"  name="sale_price" value="">                 
                            </div> 
                            <div class="form-block col-6">
                                <label for="name">Regular Price</label>
                                <input type="number" name="regular_price" value="">                 
                            </div>                 
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="taxable"><label for="">Charge tax on this product</label>
                        </div>
                    </div>
                </div>
                <!-- invetory --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Inventory</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-inline gap30">
                            <div class="form-block col-6">
                                <label for="name">SKU</label>
                                <input type="text"  name="sku" value="">                 
                            </div> 
                            <div class="form-block col-6">
                                <label for="name">Barcode</label>
                                <input type="text" name="barcode" value="">                 
                            </div>                 
                        </div>
                        <div class="form-block">
                            <label for="name">Quantity</label>
                            <input type="number" name="qty" value="">                 
                        </div> 
                    </div>
                </div>
                <!-- /inventory --->
                <!-- Variants --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Variants</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-checkbox">
                            <input type="checkbox" class="has-variant" name="" id=""><label for="">This product has multiple options.</label>
                        </div>
                    </div>
                    <div class="options-container">
                        <div class="panel-line"></div>
                        <div class="panel-body">
                            <label class="panel-sub-title">Options</label>
                            <br>
                            <div class="variant-selector">
                                <select id="selectInput" class="capitalized">
                                </select>
                                <span id="btn-add-variant" class="btn btn-default ml20">Add</span>
                            </div>   
                            <div class="options-wrapper">
                            
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- /variants --->
            </div>
        
            <div class="right-column">
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title"></label>
                    </div>          
                    <div class="panel-body"> 
                        <div class="form-block">                     
                            <select id="status" name="status" >
                                <option value="0">Draft</option>
                                <option value="1">Published</option>                            
                            </select>     
                        </div>
                        <div class="form-inline">
                            <input type="checkbox" name="featured" value="1">
                            <label for="" class="ml10">Featured Product</label>
                        </div>
                        <div class="form-block w-12">
                            <button id="btnsave" type="create" class="btn btn-primary align-self-end">Save</button>
                        </div>                  
                    </div>
                </div>
                <!-- categories --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Category</label>
                    </div>          
                    <div class="panel-body">                
                    <div class="form-inline">
                            <div class="form-block w-12">                     
                                <select id="categories" class="capitalized" name="categories" > 
                                        <option value="0">Uncategories</option> 
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option> 
                                    @endforeach                                                        
                                </select>     
                            </div>                    
                    </div>
                    </div>
                </div>
                <!-- /categories --->
                <!-- Tags --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Tags</label>
                    </div>          
                    <div class="panel-body"> 
                            <div class="form-block">                     
                                <input type="text" name="tags" value="{{ old('name') }}">  
                            </div>
                            <span>Separate tags with commas</span>
                    </div>
                </div>
                <!-- /Tags --->
        
                    <!-- product images --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Image Product</label>
                    </div>          
                    <div class="panel-body">   
                        <div class="form-block">
                            <div class="image-product">
                                <div class="image">
                                 
                                </div>

                                <div class="progress-bar-wrapper progress-image">  
                                    <div class="progress-bar" style="--width:0" data-value="Loading...."></div>                                  
                                </div>
                                
                            
                            </div> 
                            <div class="file btn btn-primary m-t-1">
                                <label>
                                    Choose 
                                    <input  type="file" id="file-product-image" name="images[]" style="display:none;">
                                </label>

                            </div>  

                        
                            
                            
                        </div>  
                    </div>
                </div>
                <!-- /product images --->
        
                <!-- product-images gallery --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Image gallery</label>
                    </div>          
                    <div class="panel-body">                        
                            <div class="image-gallery-wrapper">
                                <div class="image-gallery">                                
                                                                     
                                </div>
                                <div class="progress-bar-wrapper progress-images">  
                                    <div class="progress-bar" style="--width:0" data-value="Loading...."></div>                                  
                                </div> 
                            </div>
                           

                            <div class="file btn btn-primary m-t-1 w-12">
                                <label>
                                    Choose 
                                    <input  type="file" id="file-image-gallery" name="images[]" style="display:none;" multiple >
                                </label>
                            </div>
                        
                            
                    
                    </div>
                </div>
                <!-- /product-images gallery  --->
        
        
            
    
        
        
        
            </div>
            
        </div>
    </form>
    <form id="formUpload" method="post"   enctype="multipart/form-data">
        @csrf
    </form>
        
</div>

{{-- <script  src="/js/admin/productAddupdate.js"></script> --}}

@endsection