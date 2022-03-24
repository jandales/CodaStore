@extends('layout.admin')

@section('content') 

<h1>Categories</h1>

  
    <div class="panel m-t-2 w-12">
        <div class="panel-header">           
            <p>Edit Category</p>          
        </div>

        <div class="panel-body">
              
                <form id="addCategories" action="{{ route('categories.update',[$category->id])}}" method="POST">
                    @csrf
                    <div class="form-block">
                        <label for="email">Name</label>
                        <input type="text" name="name" value="{{ $category->name }}">                 
                    </div>
                    <button class="btn-primary">Save</button>
                </form>

               
           
                                     
        
        </div>
    </div>


   
   





@endsection