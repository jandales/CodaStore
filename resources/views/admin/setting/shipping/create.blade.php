@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Shipping Methods</h1>
</div>





    <div class="setting-content  m-t-2">
    
        
        <div class="panel">
            <div class="panel-heading">           
                <label class="panel-title">Create Shipping Method</label>          
            </div>
            <div class="panel-body">
                <form id="form" method="POST"  action="{{route('admin.shipping.method.store')}}">
                    @csrf 
                <div class="form-block">
                    <label for="name">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror                 
                </div>

                <div class="form-block">
                    <label for="name">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"> 
                    @error('description')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror                 
                </div>

                <div class="form-block">
                    <label for="name">Amount</label>
                    <input type="text" name="amount" value="{{ old('amount') }}">     
                    @error('amount')
                        <div class="alert alert-danger mt10">{{ $message }}</div>
                    @enderror             
                </div>

                <div class="form-block">
                    <label for="name">Status</label>
                    <select name="status" id="">
                        <option value="0" {{old('status') == 0 ? 'selected' : ''}}>Inactive</option>
                        <option value="1" {{old('status') == 1 ? 'selected' : ''}}>Active</option>
                    </select>  
                    @error('status')
                        <div class="alert alert-danger mt10 ">{{ $message }}</div>
                    @enderror          
                </div>

                <div class="flex justify-content-end gap10">
                    <button id="btnsave" type="create" class="btn btn-primary">Save</button>                  
                </div>
                
            </form>
            </div>
        </div>

 

    </div>







@endsection