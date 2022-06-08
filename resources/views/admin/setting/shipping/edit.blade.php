@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Shipping Methods</h1>
</div>

<div class="setting-content m-t-2">
    
        
        <div class="panel">
            <div class="panel-heading">           
                <label class="panel-title">Edit Shipping Method</label>          
            </div>
            <div class="panel-body">
                <form id="form" method="POST"  action="{{route('admin.shipping.method.update', [$shipping_method->encryptedId() ])}}">
                    @csrf 
                    @method('put')
                <div class="form-block">
                    <label for="name">Name</label>
                    <input type="text" name="name" value="{{ $shipping_method->name }}">
                    @error('name')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror                 
                </div>

                <div class="form-block">
                    <label for="name">Description</label>
                    <input type="text" name="description" value="{{ $shipping_method->description  }}"> 
                    @error('description')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror                 
                </div>

                <div class="form-block">
                    <label for="name">Amount</label>
                    <input type="text" name="amount" value="{{ $shipping_method->amount }}">     
                    @error('amount')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror             
                </div>

                <div class="form-block">
                    <label for="name">Status</label>
                    <select name="status" id="">
                        <option value="0" {{ $shipping_method->status == 0 ? 'selected' : '' }}>Inactive</option>
                        <option value="1" {{ $shipping_method->status == 1 ? 'selected' : '' }}>Active</option>
                    </select>  
                    @error('status')
                        <div class="alert alert-danger mt10 ">{{ $message }}</div>
                    @enderror          
                </div>

                <div class="flex justify-content-end gap10">
                    <button class="btn btn-primary">Save Changes</button>                  
                </div>
                
            </form>
            </div>
        </div>

 

</div>







@endsection