@extends('checkout')
@section('content') 
        @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
        @endif
        @if (session('message'))
                <div class="alert alert-warning mt-1">{{ session('message')}}</div>
        @endif 
        <div action="{{ route('checkout.shipping') }}" method="get">
            @csrf
        
            <div class="flex space-between gap50 mt-5">
                <div class="w-6">
                    <div class="flex space-between  border-b-solid py-1">
                        <h2 class="uppercase">Contact Information</h2>
                        @guest
                            <span>Already have an account? <a href="" class="link link-primary"> Log in</a></span> 
                        @endguest    
                        @auth                                        
                            <span id="btn-change-email" class="change pointer">Change</span>
                        @endauth                                  
                    </div> 
                    @auth
                        <span id="email-span" class="block mt-1">{{ auth()->user()->email }}</span>
                    @endauth     
                            
                        <div id="email-input"  class="form-block mt-1 @auth hidden @endauth">
                            <label for="contact" class="text-sm" >Email</label>
                            <input type="email" class="" name="email" value="{{ auth()->user()->email }}"/>
                        </div>
            
                        <div class="flex space-between mt-2 border-b-solid py-1">                
                            <h2 class="uppercase">Shipping Details</h2>
                            @auth
                                @if(auth()->user()->shippingDefaultAddress())
                                <span  id="change-address" class="change pointer">Change</span>
                                @endif
                            @endauth
                        </div>
                    @auth
                        <div id="address-auth" class="flex flex-column gap5 mt-1 mb-1">
                            @if(auth()->user()->shippingDefaultAddress())
                                <div class="flex space-between">
                                    <span>{{ auth()->user()->shippingDefaultAddress()->name() }}</span>
                                    <span>{{ auth()->user()->email }}</span>                                  
                                </div> 
                                <span>{{ auth()->user()->shippingDefaultAddress()->street }}</span>
                                <span>{{ auth()->user()->shippingDefaultAddress()->city . " " . auth()->user()->shippingDefaultAddress()->region }}</span>
                                <span>{{ auth()->user()->shippingDefaultAddress()->country }}</span> 
                            @endif    
                                                   
                        </div>
                        <x-form-shipping :address="auth()->user()->shippingDefaultAddress()"></x-form-shipping>  
                    @endauth
                    @guest                
                        <x-form-shipping></x-form-shipping>
                    @endguest       
                    <div class="form-block mt-2">
                        <button id="btn-continue" class="btn btn-dark w-3 p-10 mr-1">Continue Shipping</button>
                        <a href="{{route('cart')}}" class="change">Return to Cart</a>
                    </div> 
                </div>            
                <div class="w-6">           
                    <x-small-cart-component :carts="$carts"/>
                </div>
            </div>
        </div>

<script>
const btnChangeEmail = document.getElementById('btn-change-email')

btnChangeEmail.onclick = function() {
        this.classList.add('hidden');
        const emailInput = document.getElementById('email-input');
        const emailspan = document.getElementById('email-span');

        emailspan.classList.add('hidden');
        emailInput.classList.remove('hidden'); 
        // emailInput.querySelector('input[name="email"]').value = emailspan.innerHTML;
       
}

document.getElementById('change-address').onclick = function(){
    this.classList.add('hidden');
    document.getElementById('address-auth').classList.add('hidden');
    document.getElementById('form-shipping').classList.remove('hidden');
}

document.getElementById('btn-continue').onclick = function(e) {
    e.preventDefault();
    


    userInfo = {
        contact : document.querySelector('input[name="email"]').value,
        shippingMethod : '',
        shippingaddress :  {
            firstname : document.querySelector('input[name="firstname"]').value,
            lastname: document.querySelector('input[name="lastname"]').value,
            street : document.querySelector('input[name="street"]').value,
            city : document.querySelector('input[name="city"]').value,
            phone :document.querySelector('input[name="phone').value,
            country : document.querySelector('input[name="country"]').value,
            region : document.querySelector('input[name="region"]').value,
            zipcode :document.querySelector('input[name="zipcode"]').value    
        }
    }

    localStorage.setItem("checkoutInfo", JSON.stringify(userInfo));
    window.location.href = "/checkout/shipping";

}

</script>
    
@endsection


   
    
     