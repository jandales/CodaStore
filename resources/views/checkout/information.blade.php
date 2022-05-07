@extends('checkout')
@section('content')        
        <form id="form" action="{{ route('checkout.information.store') }}" method="post">
            @csrf
            <input type="hidden" name="is_form_empty" value="{{ $address ? 1 : 0}}">
            <input type="hidden" name="is_email_change" value="{{ $email ? 1 : 0 }}">
            <div class="flex space-between gap50 mt-5">
                <div class="w-6">
                    <div class="flex space-between  border-b-solid py-1">
                        <h2 class="uppercase">Contact Information</h2>
                        @guest
                            <span>Already have an account?  <a href="" class="link link-primary"> Log in</a></span> 
                        @endguest    
                                                  
                    </div>            
                            
                        <div id="email-input"  class="form-block mt-1">
                            <label for="contact" class="text-sm" >Email</label>
                            <input type="email" validator-input="email" class="" name="email" value=" @auth {{$email }} @endauth"/>
                        </div>
            
                        <div class="flex space-between mt-2 border-b-solid py-1">                
                            <h2 class="uppercase">Shipping Details</h2>    
                            @auth
                                <span id="open-modal-shipping" class="change">Change</span>
                            @endauth                       
                        </div> 

                        <x-form-shipping :address="$address"></x-form-shipping>  

                    <div class="form-block mt-2">
                        <button id="btn-continue" class="btn btn-dark w-3 p-10 mr-1">Continue Shipping</button>
                        <a href="{{route('cart')}}" class="change">Return to Cart</a>
                    </div> 
                </div>            
                <div class="w-6">           
                <x-small-cart-component :cart="$cart"  :shippingcharge="0"/>
                </div>
            </div>
        </form>


@endsection


   
    
     