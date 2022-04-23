@extends('checkout')
@section('content')    

<div class="flex space-between gap50 mt-5">
    <div class="w-6">           

        <x-checkout-information-component/>

        <form action="{{ route('checkout.shipping.update')}}" method="post">
            @csrf
            @method('put')               

            <x-shipping-method-component :shippingmethods="$shipping_methods" :active="session('shipping_method')['id']"/>

            <div class="form-block mt-2">
                <button id="btn-to-payment" class="btn btn-dark w-3 p-10 mr-1">Continue Payment</button>
                <a href="{{route('checkout.information')}}" class="change">Return to Information</a>
            </div> 
        </form>
    </div>            
    <div class="w-6">
        <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>               
    </div>
</div>
{{-- 
<script>
const radios =  document.querySelectorAll('input[type="radio"]');

radios.forEach(element => {
    element.onchange = function(){
        if(element.checked)
        {
            getShippingMethod(id);
        }
    }
});

function getShippingMethod(id){
    $.ajax({
        url : `/get-shipping-method/${id}`,
        type : 'Get',
        async : false,
        success : successShippingResponse, 
    });
}

function successShippingResponse(response){
    console.log(response);
}
    
</script> --}}
@endsection


   
    
     