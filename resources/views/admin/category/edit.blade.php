@extends('layout.admin')

@section('content') 

<h1>Categories</h1>

    <div class="flex justify-content-space-between m-t-2">
        <div class="forms-category">
            <form action="{{ route('admin.categories.update',[$category->slug]) }}" method="post" enctype="multipart/form-data"> 
                @csrf  
                @method('put')         
                <div class="form-title">
                    <label for="email" class="title">Edit Category</label>                                 
                </div>           
                <div class="form-block">
                    <label for="email">Name</label>
                    <input type="text" name="name" value="{{$category->name}}">                 
                </div>
                <div class="alert-m  alert-danger">
                    <div class="flex justify-content-space-between">
                        <label class="message">This is an alert box.</label>
                        <span class="closebtn " onclick="alertClose(this)"><i class="fas fa-times"></i></span>
                    </div> 
                </div>
                <div class="form-block">
                    <label for="email">Descriptions</label>
                    <input type="text" name="description" value="{{ $category->description }}">                 
                </div>

                <div class="form-block">                   
                    <div class="image-holder">
                        <img id="image-to-upload" class="{{ $category->image == null ? hidden : '' }}" src="{{$category->image}}" alt="">   
                        <span id="remove-image"   class="{{ $category->image == null ? hidden : '' }}" ><i class="fa fa-times"></i></span>
                    </div>
                    <label class="file-uploader mt-1">                        
                        Choose 
                        <input  type="file" id="file-input" name="image" style="display:none;">
                    </label>
                </div>
              
                <div class="flex gap20">
                    <button id="btncategoryCreate" class="btn btn-primary w-3">SAVE</button>
                    <a href="{{ route('admin.categories') }}" class="btn btn-danger w-3">CANCEL</a> 
                </div>
            </form>
        </div>
        
        <div class="panel-table w-9">       
            <x-category-table-component :categories="$categories"/>
        </div>
    </div>

@endsection