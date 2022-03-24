@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Settings</h1>
</div>


<form id="form" method="POST"  action="{{route('admin.users.store')}}">
    @csrf
    <div class="mt10">
        @if ($errors->any())  
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger mt10">
                    {{ $error }}
                </div>
            @endforeach
        @endif
    </div>      
    <div class="page-container  m-t-1"> 
                
        <div class="panel">
           
            <div class="panel-body">
                <div class="form-block">
                    <label for="name">Company Name</label>
                    <input type="text" name="username" value="{{ old('username') }}">                 
                </div>

                <div class="form-block">
                    <label for="name">Email</label>
                    <input type="text" name="email" value="{{ old('email') }}">                 
                </div>

                <div class="form-block">
                    <label for="name">Phone Number</label>
                    <input type="text" name="firstname" value="{{ old('firstname') }}">                 
                </div>

        

         

             

        <div class="flex justify-content-end gap10">
                    <button id="btnsave" type="create" class="btn btn-primary">Save Changes</button>
                  
                </div>
            </div>
        </div>
    </div>
</form>






@endsection