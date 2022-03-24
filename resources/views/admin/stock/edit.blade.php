@extends('layout.admin')

@section('content') 

<h1>Products</h1>

   

        <div class="tabs m-t-2">

            <div class="form-header">
                <ul class="form-navigation">
                    <li><a href="{{ route('product.edit',[$stock->product_id]) }}" >Item Information</a></li>
                    <li><a href="{{ route('product.variants',[$stock->product_id]) }}" >Variants</a></li>
                    <li><a href="{{ route('stock.edit',[$stock->product_id])}}" class="active">Quantity</a></li>
                    <li><a href="{{ route('price.edit',[$stock->product_id])}}">Pricing</a></li>
                    <li><a href="{{ route('product.download',[$stock->product_id]) }}">Image Upload</a></li>  
                </ul>
            </div>

       
            <div class="form-content"> 
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>                
            @endif 
            <form id="form" action="{{ route('stock.update',[$stock])}}" method="post">
                   
                @csrf 

                <div class="form-block">
                    <label for="email">Enter Quantity</label>
                    <input id="current-qty" type="number" name="qty" value="{{ $stock->qty}}">                 
                </div>
                @error('qty')
                    <div class="alert alert-danger">{{ $message }}</div>                
                @enderror 

               <div class="form-block">
                    <label for="email">Comments</label>
                    <textarea name="remarks" cols="30" rows="10">  
                        {{ $stock->remarks }}                     
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
    const buttons = document.querySelectorAll("[data-value]");
    const details = document.getElementById('dropdown-details')
    const summary = document.getElementById('summary');
    let currentQuantity = document.getElementById('current-qty')
    let enterQuantity = document.getElementById('enter-qty')
    let value = "add";


    buttons.forEach(button => {
        button.addEventListener('click', function() {

                value =  button.getAttribute('data-value');           
                details.removeAttribute('open');
                summary.innerText = button.innerText
              
              
        })
    });

    function action()
    {
        let qauntity = 0; 
        let currentQty = parseInt(currentQuantity.value)
        let enterQty =  parseInt(enterQuantity.value)

        switch(value)
        {
            case 'add' :    
                    currentQuantity.value =  currentQty + enterQty;                                
            break;

            case 'minus' :

                if(currentQty < enterQty) return  currentQuantity.value = 0;
                             
                currentQuantity.value = currentQty -  enterQty;

            break;

            default : 
                alert('Please Select action to apply')
            break;
        }

     
       
    }



</script> 


@endsection