@extends('checkout')
@section('content')    

        
        @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
        @endif
        @if (session('message'))
                <div class="alert alert-warning mt-1">{{ session('message')}}</div>
        @endif  

        <div class="flex space-between gap50 mt-5">
            <div class="w-6">

                <div class="checkout-information">
                    <div class="panel border-b-0">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Contact</span>
                                <span class="span-email"></span>
                            </div>
                            <a href="{{route('checkout.information')}}" class="change">Change</a>
                        </div>
                    </div> 
                    <div class="panel">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Ship to</span>
                                <span class="span-ship-to">Jesusandales@gmail.com</span>
                            </div>
                            <a href="{{route('checkout.information')}}" class="change">Change</a>
                        </div>
                    </div> 
                </div>

                
                <div class="checkout-shipping-method mt-4">
                    <h2 class="uppercase">Shipping Method</h2>
                    <div class="panel border-b-0 mt-1">
                        <div class="flex  space-between">
                            <div class="flex gap10">
                                <input type="radio" checked style="margin-top: 5px" name="" id="">
                                <div class="flex flex-column">
                                    <span class="method">Free Shipping</span> 
                                    <span>5-7 days business days</span>  
                                </div>                             
                            </div>
                            <a href="">0.00</a>
                        </div>
                    </div>
                    
                    <div class="panel">
                        <div class="flex space-between">
                            <div class="flex gap10">
                                <input style="margin-top: 5px" type="radio" name="" id="">
                                <div class="flex flex-column">
                                    <span class="method">Express Delivery</span> 
                                    <span>3 days business days</span>  
                                </div>                             
                            </div>
                            <a href="">150.00</a>
                        </div>
                    </div> 
                </div>

                <div class="form-block mt-2">
                    <button id="btn-to-payment" class="btn btn-dark w-3 p-10 mr-1">Continue Payment</button>
                    <a href="{{route('checkout.information')}}" class="change">Return to Information</a>
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
            })          

            function setMethod()
            {          
                const element = document.querySelector('input[type="radio"]:checked');                                          
                const method = element.parentElement.querySelector('.method').innerHTML;
                data.shippingMethod = method
                localStorage.setItem("checkoutInfo", JSON.stringify(data));
            }

            const radios = document.querySelectorAll('input[type="radio"]');            
            radios.forEach(element => {                   
                    element.onclick = function() {
                        radios.forEach(element => {
                            element.checked = false;
                        });
                        element.checked = true;
                    }
            });

            document.getElementById('btn-to-payment').onclick = function(){   
                setMethod()                          
                window.location.href = "/checkout/payment"       
            }

            

         
            
        </script>

   
       

        

@endsection


   
    
     