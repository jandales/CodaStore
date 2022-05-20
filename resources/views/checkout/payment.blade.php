@extends('checkout')
@section('content')    


        <div class="flex  space-between gap50 mt-5 sm-flex-column md-mt-2">
            <div class="w-6 sm-w-12">

               <x-checkout-information-component/>

                <form id="form" action="{{ route('checkout.paynow') }}" method="post">
                                    
                    @csrf
                <div class="checkout-shipping-method mt-2">
               
                <x-card-component :card="$user_payment_option"/>
                    
                </div>
             
                    <div class="checkout-shipping-method mt-2">
                        <h2 class="uppercase">Billing Address</h2>
                        <span class="block mt10">Select the address that mactch your card or payment method</span>
                        <div class="panel border-b-0 mt-1">
                            <div class="flex gap10">
                                <input type="radio" id="use-same" name="same_as_shipping" checked  style="margin-top: 5px" value="0">   
                                <label for="">Same as Shipping Address</label>  
                            </div>
                        </div>                    
                        <div class="panel">
                            <div class="flex gap10">
                            <input type="radio" id="use-differ" name="is_new_billing" style="margin-top: 5px" value="1">   
                                <label for="">Use a different Address</label>  
                            </div>
                            <x-form-billing-component/>
                        </div> 
                    </div>
                    @guest
                        <div class="flex align-items-center gap10 mt-2">
                            <input type="checkbox" names="save-details" id="">
                            <label for="">Save my Information for fastest checkout</label>
                        </div>             
                    @endguest


                        <div class="form-block mt-2">
                            <button id="paynow" class="btn btn-dark mr-1 md-mr-0">Pay Now</button> 
                            <a href="{{route('checkout.shipping')}}" class="change sm-text-center">Return to Shipping</a>
                        </div> 
                </form>
              
               
            </div>            
            <div class="w-6 sm-w-12">
                <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>               
            </div>
        </div>


   


        

@endsection


   
    
     