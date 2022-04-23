@extends('layout.admin')
@section('content') 

<div class="page-title">
    <h1>Campany</h1>
</div>
@if(session('success'))
<div class="alert alert-success m-t-1">{{ session('success') }}</div>                
@endif 
<div class="setting-content m-t-2">    
    <div class="panel">            
        <div class="panel-body">
            <form id="form" method="POST"  action="{{ route('admin.setting.campany.update',[$general_setting])}}">
                @csrf 
                @method('PUT')

            <div class="form-inline">
                <label for="name">Campany Name</label>
                <input type="text" name="name" value="{{ $general_setting->campany_name }}">                             
            </div>
            @error('name')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror  
            <div class="form-inline">
                <label for="name">Address</label>
                <input type="text" name="address" value="{{ $general_setting->campany_address }}">                             
            </div>
            @error('address')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror  

            <div class="form-inline">
                <label for="name">City</label>
                <input type="text" name="city" value="{{ $general_setting->campany_city }}">                              
            </div>
            @error('city')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   
          
            <div class="form-inline">
                <label for="name">Region</label>
                <input type="text" name="region" value="{{ $general_setting->campany_region }}">    
            </div>
            @error('region')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   

            <div class="form-inline">
                <label for="name">Country</label>
                <input type="text" name="country" value="{{ $general_setting->campany_country }}">    
            </div>
            @error('country')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

            <div class="form-inline">
                <label for="name">Zipcode</label>
                <input type="text" name="zipcode" value="{{ $general_setting->campany_zipcode }}">    
            </div>
            @error('zipcode')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

            <div class="form-inline">
                <label for="name">Phone</label>
                <input type="text" name="phone" value="{{ $general_setting->campany_phone }}">    
            </div>
            @error('phone')
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