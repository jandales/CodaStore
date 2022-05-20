@extends('checkout')
@section('content')    

<div class="flex space-between gap50 mt-5 sm-flex-column md-mt-2">
    <div class="w-6 sm-w-12">           

        <x-checkout-information-component/>

        <form action="{{ route('checkout.shipping.update')}}" method="post">
            @csrf
            @method('put')               

            <x-shipping-method-component :shippingmethods="$shipping_methods" :active="session('shipping_method')['id']"/>

            <div class="form-block mt-2">
                <button id="btn-to-payment" class="btn btn-dark mr-1 md-mr-0">Continue Payment</button>
                <a href="{{route('checkout.information')}}" class="change  sm-text-center">Return to Information</a>
            </div> 
        </form>
    </div>            
    <div class="w-6 sm-w-12">
        <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>               
    </div>
</div>
@endsection


   
    
     