@extends('layout.admin')

@section('content') 

<h1>Attributes</h1>

    <div class="flex justify-content-space-between m-t-2">
        <div class="forms-category">   
            <form action="{{ route('admin.attributes.update',[$attribute->slug]) }}" method="post">
                @csrf
                @method('put')
            <div class="form-title">
                <label for="email" class="title">Edit Attributes</label>                                 
            </div>   
                
            <div class="form-block">
                <label for="email">Name</label>
                <input type="text" name="name" value="{{$attribute->name}}">                 
            </div>
            @error('name')
                <div class="alert alert-danger">{{$message}}</div>
            @enderror             
            <div class="form-block">
                <label for="email">Descriptions</label>
                <input type="text" name="description" value="{{$attribute->description}}">                 
            </div>
           
            <div class="flex">
                <button id="btnFormCreate" class="btn btn-primary w-3">SAVE</button>               
            </div>

        </form>  
        </div>
        <div class="panel m-t-2 w-9"> 
            <div class="panel-body">
                <x-attribute-table-component :list="$attributes"/>            
            </div>
        </div>
    </div>
    



   
    




@endsection