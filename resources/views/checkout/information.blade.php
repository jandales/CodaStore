@extends('checkout')
@section('content')        
        <form id="form" action="{{ route('checkout.information.store') }}" method="post">
            
            @csrf
            <input type="hidden" name="is_form_empty" value="{{ $address ? 1 : 0}}">
            <input type="hidden" name="is_email_change" value="{{ $email ? 1 : 0 }}">
            <div class="flex space-between gap50 mt-5 sm-flex-column md-mt-2">
                <div class="w-6 sm-w-12">
                   
                    <div class="flex  space-between  border-b-solid py-1">
                        <h2 class="checkout-section-title">Contact</h2>
                        @guest
                            <span class="checkout-hint">Have an account? <a href="{{ route('login') }}" class="link-underline">Sign in</a></span> 
                        @endguest                
                    </div>            
                            
                        <div id="email-input"  class="form-block mt-1">
                            <label for="checkout-email" class="text-sm">Email</label>
                            <input id="checkout-email" type="email" autocomplete="email" validator-input="email" name="email" value="{{ auth()->check() ? trim($email) : '' }}" placeholder="you@example.com"/>
                        </div>
            
                        <div class="flex space-between mt-2 border-b-solid py-1">                
                            <h2 class="checkout-section-title">Shipping address</h2>    
                            @auth
                                <button type="button" id="open-modal-shipping" class="change">Use a saved address</button>
                            @endauth                       
                        </div> 

                        <x-form-shipping :address="$address"></x-form-shipping>  

                    <div class="form-block mt-2">
                        <button id="btn-continue" class="btn btn-dark mr-1 md-mr-0">Continue to shipping</button>
                        <a href="{{route('cart')}}" class="change sm-text-center"><i class="fas fa-arrow-left" aria-hidden="true"></i> Return to bag</a>
                    </div> 
                </div>            
                <div class="w-6 sm-w-12">           
                        <x-small-cart-component :cart="$cart"  :shippingcharge="0"/>
                </div>
            </div>
        </form>


@endsection


   
    
     