@extends('layout.admin')

@section('content') 

<h1>Attributes</h1>

    <div class="flex gap30 justify-content-space-between m-t-2">
        <div class="forms-category">   
            <form action="{{ route('admin.attributes.store') }}" method="post">
                @csrf
                  
            <div class="form-title">
                <label for="email" class="title">Add new Attributes</label>                                 
            </div>   
                
            <div class="form-block">
                <label for="email">Name</label>
                <input type="text" name="name" value="">                 
            </div>
            @error('name')
                <div class="alert alert-danger">{{$message}}</div>
            @enderror 

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
           
            <div class="flex">
                <button id="btnFormCreate" class="btn btn-primary w-3">SAVE</button>               
            </div>

        </form>  
        </div>
        <div class="panel-table w-9"> 
           
                <x-attribute-table-component :list="$attributes" :keyword="$keyword"/>
            
        </div>
    </div>
    



   
    




@endsection