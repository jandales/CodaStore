@extends('layout.admin')
@section('content') 

<div class="page-title">
    <h1>Emailer</h1>
</div>
    
<div class="setting-content m-t-2">    
    <div class="panel">            
        <div class="panel-body">
            <form id="form" method="POST"  action="{{route('admin.shipping.method.store')}}">
                @csrf 

            <div class="form-inline">
                <label for="name">Mailer</label>
                <input type="text" name="name" value="{{ old('name') }}">                             
            </div>
            @error('name')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror  
            <div class="form-inline">
                <label for="name">Host</label>
                <input type="text" name="name" value="{{ old('name') }}">                             
            </div>
            @error('name')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror  

            <div class="form-inline">
                <label for="name">Port</label>
                <input type="text" name="description" value="{{ old('description') }}">                              
            </div>
            @error('description')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   
          
            <div class="form-inline">
                <label for="name">Username</label>
                <input type="text" name="amount" value="{{ old('amount') }}">    
            </div>
            @error('amount')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   

            <div class="form-inline">
                <label for="name">Password</label>
                <input type="text" name="amount" value="{{ old('amount') }}">    
            </div>
            @error('amount')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

            <div class="form-inline">
                <label for="name">From Address</label>
                <input type="text" name="amount" value="{{ old('amount') }}">    
            </div>
            @error('amount')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

            <div class="form-inline">
                <label for="name">From Name</label>
                <input type="text" name="amount" value="{{ old('amount') }}">    
            </div>
            @error('amount')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

         



            <div class="flex justify-content-end gap10">
                <button id="btnsave" type="create" class="btn btn-primary">Save Changes</button>                  
            </div>
            
        </form>
        </div>
    </div>



</div>







@endsection