@extends('layout.admin')
@section('content')

<div class="container">
    <div class="page-title">
        <h1>Edit Products</h1>
    </div>
    <div id="notify-message" class="m-t-2">
        
    </div>
    <form id="form" method="post"  action="{{route('admin.products.update',[ $product->encryptedId() ])}}" enctype="multipart/form-data">
        @csrf   
        <input type="hidden" name="attributes" value="{{$product->attributes}}">
        <input type="hidden" name="variants" value="{{$product->variants}}">  
        <input type="hidden" name="photos" value="{{$product->photos}}">   

        <div class="page-container m-t-2">         
            <div class="left-column">
                <div class="panel">
                <div class="panel-body">
                        <div class="form-block">
                            <label for="name">Name</label>
                            <input type="text" name="name" value="{{$product->name}}">                 
                        </div>
                        <div class="form-block">
                            <label for="email">Short Description</label>
                        <textarea  name="short_description"   cols="30" rows="3">{{$product->short_description}}</textarea>                
                        </div>
                        <div class="form-block">
                            <label for="email">Long Description</label>
                            <textarea  name="long_description"   cols="30" rows="10">{{$product->long_description}}</textarea>                
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
                                <label for="name">Sale Price</label>
                                <input type="number"  name="sale_price" value="{{ $product->sale_price }}">                 
                            </div> 
                            <div class="form-block col-6">
                                <label for="name">Regular Price</label>
                                <input type="number" name="regular_price" value="{{ $product->regular_price }}">                 
                            </div>                 
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
                                <input type="text"  name="sku" value="{{ $product->sku}}">                 
                            </div> 
                            <div class="form-block col-6">
                                <label for="name">Barcode</label>
                                <input type="text" name="barcode" value="{{ $product->barcode}}">                 
                            </div>                 
                        </div>
                        <div class="form-block">
                            <label for="name">Quantity</label>
                            <input type="number" name="qty" value="{{$product->stock->qty}}">                 
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
                            <input type="checkbox" {{ count($product->attributes) > 0 ? 'checked' : ''}} class="has-variant" name="hasVariant" id=""><label for="">This product has multiple options.</label>
                        </div>
                    </div>
                    <div class="options-container">
                        <div class="panel-line"></div>
                        <div class="panel-body">
                            <label class="panel-sub-title">Options</label>
                            <br>
                            <div class="variant-selector">
                                <select name="atttribute" id="selectInput" class="capitalized">
                                </select>
                                <button id="btn-add-variant" class="btn btn-default ml20">Add</button>
                            </div>   
                            <div class="options-wrapper">
                                @foreach ($product->attributes as $item)
                                    <div class="options">                       
                                        <div class="options-selector m-t-1" >
                                            <div class="option-attribute">
                                                {{$item->attributes->name}}                                     
                                            </div> 
                                        
                                            <div class="variants-wrapper">
                                                <div class="variants-list-wrapper">                                           
                                                    @foreach ($product->variants as  $variant)  
                                                        @if( $item->attribute_id ==  $variant->attribute_id)            
                                                            <div class="variant">  
                                                                {{  $variant->name}}         
                                                                <span name="{{  $variant->name }}" data-id="{{ $item->attribute_id }}" class="remove-variant-item"><i class="fas fa-times"></i></span>
                                                            
                                                            </div>
                                                        @endif
                                                    @endforeach                                  
                                                </div>                
                                                <input data-id="{{ $item->attribute_id }}" class="inputVariant no-border"  placeholder="Enter varaint name and hit enter" name="variant_name[]" type="text" value=""> 
                                            </div>                                 
                                        </div>
                                        <span class="option-remove" data-id="{{ $item->attribute_id }}">remove</span>
                                    </div>
                                @endforeach                            
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
                                <option {{$product->status == 0 ? 'selected' : '' }} value="0">Draft</option>
                                <option {{$product->status == 1 ? 'selected' : '' }}  value="1">Published</option>                            
                            </select>     
                        </div>
                        <div class="form-inline">
                            <input type="checkbox" {{ $product->featured == 1 ? 'checked' : ''}} name="featured" value="1">
                            <label for="" class="ml10">Featured Product</label>
                        </div>
                        <div class="form-block w-12">
                            <button id="btn-update" class="btn btn-primary align-self-end">Save</button>
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
                                <select id="categories" class="capitalized" name="category_id"> 
                                    <option value="0">Uncategories</option> 
                                    @foreach ($categories as $category)                              
                                        <option {{ $product->category_id == $category->id ? 'selected' : ''}} value="{{ $category->id }}" >{{ $category->name }}</option> 
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
                                <input type="text" name="tags" value="{{ $product->tags }}">  
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
                                        <img src="{{ $product->imagePath }}" class="product-image" alt=""> 
                                        <span onclick="removeProductImage(this, 1 )" class="remove"><i class="fas fa-times"></i></span>                                   
                                </div>
                                <div class="progress-bar-wrapper progress-image">  
                                    <div class="progress-bar" style="--width:0" data-value="Loading...."></div>                                  
                                </div>
                            
                            </div> 
                            <div class="file btn btn-primary m-t-1">
                                <label>
                                    Choose 
                                    <input  type="file" id="file-product-image"  style="display:none;">
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
    <form id="formUpload" method="post"  enctype="multipart/form-data">
        @csrf
    </form>
</div>



@endsection