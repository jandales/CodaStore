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

                <div class="checkout-information">
                    <div class="panel border-b-0">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Contact</span>
                                <span class="span-email">Jesusandales@gmail.com</span>
                            </div>
                            <a href="{{route('checkout.information')}}" class="change">Change</a>
                        </div>
                    </div> 
                    <div class="panel border-b-0">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Ship to</span>
                                <span class="span-ship-to">Jesusandales@gmail.com</span>
                            </div>
                            <a href="{{route('checkout.information')}}" class="change">Change</a>
                        </div>
                    </div> 
                    <div class="panel">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Method</span>
                                <span class="span-method">Free Shipping</span>
                            </div>
                            <a href="{{route('checkout.shipping')}}" class="change">Change</a>
                        </div>
                    </div> 
                </div>

                
                <div class="checkout-shipping-method mt-2">
                    <h2 class="uppercase">Payment</h2>
                    <span class="block mt10">All Transactiion are secure and encrypted</span>
                    <div class="panel mt-1">
                        <div class="form-block">
                            <label for="contact" class="text-sm">Card Number</label>
                            <input type="text" name="card_number" value=""/>
                        </div>
                        <div class="form-block">
                            <label for="contact" class="text-sm">Card Name</label>
                            <input type="text" name="card_name" value=""/>
                        </div>
                        <div class="flex gap20">
                            <div class="form-block w-6">
                                <label for="contact" class="text-sm">Expiration Date (MM/YY)</label>
                                <input type="text" name="card_expire_data" value=""/>
                            </div>
                            <div class="form-block w-6">
                                <label for="contact" class="text-sm">Security Code</label>
                                <input type="text" name="card_cvc" value=""/>
                            </div>
                        </div>
             
                    </div>
                    
                    
                </div>

                <div class="checkout-shipping-method mt-2">
                    <h2 class="uppercase">Billing Address</h2>
                    <span class="block mt10">Select the address that mactch your card or payment method</span>
                    <div class="panel border-b-0 mt-1">
                        <div class="flex gap10">
                            <input type="radio" id="use-same" checked  style="margin-top: 5px" name="" id="">   
                            <label for="">Same as Shipping Address</label>  
                        </div>
                    </div>                    
                    <div class="panel">
                        <div class="flex gap10">
                            <input type="radio" id="use-differ" style="margin-top: 5px" name="" id="">   
                            <label for="">Use a different Address</label>  
                        </div>
                        <x-form-shipping/>
                    </div> 
                </div>

                <div class="flex align-items-center gap10 mt-2">
                    <input type="checkbox" name="" id="">
                    <label for="">Save my Information for fastest checkout</label>
                </div>             

                <div class="form-block mt-2">
                    <button class="btn btn-dark w-3 p-10 mr-1">Pay Now</button> 
                    <a href="{{route('checkout.shipping')}}" class="change">Return to Shipping</a>
                </div> 
            </div>
            
            <div class="w-6">
                <x-small-cart-component :carts="$carts"/>
               
            </div>
        </div>


   
        <script>
            let data;
            document.addEventListener('DOMContentLoaded', function() {
                data = JSON.parse(localStorage.getItem("checkoutInfo")); 
                document.querySelector('.span-email').innerHTML = data.contact;       
                const address = `${data.shippingaddress.street} ${data.shippingaddress.city} ${data.shippingaddress.region} ${data.shippingaddress.country}`;
                document.querySelector('.span-ship-to').innerHTML = address;  
                document.querySelector('.span-method').innerHTML = data.shippingMethod
            })  
            
            

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
        </script>

        

@endsection


   
    
     