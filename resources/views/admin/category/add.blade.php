@extends('layout.admin')

@section('content') 

<h1>Categories</h1>

  
    <div class="panel m-t-2 w-12">
        <div class="panel-header">           
            <p>Create Category</p>          
        </div>
        @if (session('error'))
            <div class="alert alert-danger">{{session('error')}}</div>
        @endif
        <div class="panel-body">
              
                <form id="addCategories" action="{{ route('categories.store')}}" method="POST">
                    @csrf
                    <div class="form-block">
                        <label for="email">Name</label>
                        <input type="text" name="name" value="">                 
                    </div>
                    <button class="btn-primary">Save</button>
                </form>

               
           
                                     
        
        </div>
    </div>


   
   





@endsection