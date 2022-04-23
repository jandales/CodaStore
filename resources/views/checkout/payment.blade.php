@extends('checkout')
@section('content')    

        @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
        @endif
        @if (session('message'))
                <div class="alert alert-warning mt-1">{{ session('message')}}</div>
        @endif  

        <div class="flex space-between gap50 mt-5 mb-5">
            <div class="w-6">

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

                    <div class="flex align-items-center gap10 mt-2">
                        <input type="checkbox" name="" id="">
                        <label for="">Save my Information for fastest checkout</label>
                    </div>             


                        <div class="form-block mt-2">
                            <button id="paynow" class="btn btn-dark w-3 p-10 mr-1">Pay Now</button> 
                            <a href="{{route('checkout.shipping')}}" class="change">Return to Shipping</a>
                        </div> 
                </form>
              
               
            </div>            
            <div class="w-6">
                <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>               
            </div>
        </div>


   
        <script type="module">

            import { errorReponse, errorRemove } from '/js/validator.js';           
            

            document.getElementById('use-differ').onclick = function() {

                document.getElementById('use-same').checked = false;
                this.parentElement.classList.add('hidden');    
                document.getElementById('form-shipping').classList.remove('hidden');           
            }

            document.getElementById('use-same').onclick = function() {
                this.checked = true;
                const usediffer = document.getElementById('use-differ');
                usediffer.checked = false;
                usediffer.parentElement.classList.remove('hidden')    
                document.getElementById('form-shipping').classList.add('hidden');           
            }

            document.getElementById('paynow').onclick = function(e) {
                e.preventDefault();
      
                const form = document.getElementById('form');
                const url = form.getAttribute('action');
                let formData =  new FormData(form);         
               
    

                $.ajax({
                    url : url,
                    type : 'POST',
                    data : formData,
                    datatype:"json", 
                    processData: false,
                    contentType: false,
                    cache: false,
                    error : errorReponse,
                    success : function(res){                      
                        errorRemove();
                        if (res.status === 200)
                        {                          
                           window.location.href = res.route;
                        }
                    }
                })
            }
        </script>

        

@endsection


   
    
     