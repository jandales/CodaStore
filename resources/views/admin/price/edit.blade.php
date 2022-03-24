@extends('layout.admin')

@section('content') 

<h1>Products</h1>

      

        <div class="tabs m-t-2">
            <div class="form-header">
                <ul class="form-navigation">
                    <li><a href="{{ route('product.edit',[$product]) }}" >Item Information</a></li>
                    <li><a href="{{ route('product.variants',[$product]) }}" >Variants</a></li>
                    <li><a href="{{ route('stock.edit',[$product])}}">Quantity</a></li>
                    <li><a href="{{ route('price.edit',[$product])}}" class="active">Pricing</a></li>
                    <li><a href="{{ route('product.download',[$product]) }}">Image Upload</a></li>  
                </ul>
            </div>

       
            <div class="form-content"> 
                @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
           @endif
            
            <form id="form" action="{{ route('price.update',[$product])}}" method="post">
                   
                @csrf 

                <div class="form-block">
                    <label for="email">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ $product->cost_price }}">                 
                </div>

                @error('regular_price')
                    <div class="alert alert-danger">{{ $message }}</div>                
                @enderror 

                <div class="form-block">
                    <label for="email">Selling Price</label>
                    <input type="number" name="price" value="{{ $product->price }}">                 
                </div>

                @error('selling_price')
                    <div class="alert alert-danger">{{ $message }}</div>                
                @enderror 



               <div class="form-block">
                    <label for="email">Comments</label>
                    <textarea name="remarks" cols="30" rows="10">
                        {{ $product->price_comments}}
                    </textarea>
              </div>                                 
          
              
                <div class="form-button m-t-2">
                    <button class="btn btn-primary mr-2">Save</button>
                    <button class="btn btn-danger">Cancel</button>
            
                </div>

            </form>
        
            </div>           

          
        


      
        </div>


        <script>

            function submit(){
                const form =  document.getElementById('form')
                form.submit(); 
            }

        </script>
        



@endsection