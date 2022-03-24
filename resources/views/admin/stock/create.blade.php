@extends('layout.admin')

@section('content') 

<h1>Products</h1>



        <div class="tabs m-t-2">

            <div class="form-header">
                <ul class="form-navigation">
                    <li><a href="#" >Item Information</a></li>
                    <li><a href="#" >Variants</a></li>
                    <li><a href="#" class="active">Quantity</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">Image Upload</a></li>  
                </ul>
            </div>

       
            <div class="form-content"> 

            <form id="form" action="{{ route('stock.store',[$product->stock])}}" method="post">
                   
                @csrf 
               
                <div class="form-block">
                    <label for="email">Quantity</label>
                    <input type="number" name="qty" value="0">                 
                </div>
                @error('qty')
                    <div class="alert alert-danger">{{ $message }}</div>                
                @enderror 
               <div class="form-block">
                    <label for="email">Comments</label>
                    <textarea name="remarks" cols="30" rows="10">

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