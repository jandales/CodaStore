@extends('layout.admin')

@section('content') 

<h1>Categories</h1>

    <div class="flex justify-content-space-between m-t-2">
        <div class="forms-category">
            <form action="{{ route('categories.store') }}" method="post" enctype="multipart/form-data"> 
                @csrf           
                <div class="form-title">
                    <label for="email" class="title">Add new Category</label>                                 
                </div>           
                <div class="form-block">
                    <label for="email">Name</label>
                    <input type="text" name="name" value="">                 
                </div>
                <div class="alert-m  alert-danger">
                    <div class="flex justify-content-space-between">
                        <label class="message">This is an alert box.</label>
                        <span class="closebtn " onclick="alertClose(this)"><i class="fas fa-times"></i></span>
                    </div> 
                </div>
                <div class="form-block">
                    <label for="email">Descriptions</label>
                    <input type="text" name="description" value="">                 
                </div>  
                
                <div class="form-block">                   
                    <div class="image-holder">
                        <img id="image-to-upload"  class="hidden" src="" alt="">   
                        <span id="remove-image" class="hidden"><i class="fa fa-times"></i></span>
                    </div>
                    <label class="file-uploader mt-1">                        
                        Choose 
                        <input  type="file" id="file-input" name="image" style="display:none;">
                    </label>
                </div>
                <div class="flex">
                    <button  class="btn btn-primary w-3">SAVE</button>
                </div>
            </form>
        </div>




        <div class="panel-table w-9"> 
            <x-category-table-component :categories="$categories" :keyword="$keyword"/>
        </div>
    </div>

@endsection