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
                                <span>Jesusandales@gmail.com</span>
                            </div>
                            <a href="">Change</a>
                        </div>
                    </div> 
                    <div class="panel">
                        <div class="flex space-between">
                            <div class="flex">
                                <span for="" style="width: 100px">Ship to</span>
                                <span>Jesusandales@gmail.com</span>
                            </div>
                            <a href="">Change</a>
                        </div>
                    </div> 
                </div>

                
                <div class="checkout-shipping-method mt-4">
                    <h2 class="uppercase">Shipping Details</h2>
                    <div class="panel border-b-0 mt-1">
                        <div class="flex  space-between">
                            <div class="flex gap10">
                                <input type="radio"  style="margin-top: 5px" name="" id="">
                                <div class="flex flex-column">
                                    <span>Free Shipping</span> 
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
                                    <span>Express Delivery</span> 
                                    <span>3 days business days</span>  
                                </div>                             
                            </div>
                            <a href="">150.00</a>
                        </div>
                    </div> 
                </div>

                <div class="form-block mt-2">
                    <a href="{{ route('checkout.payment')}}"><button class="btn btn-dark w-3 p-10">Continue Payment</button></a>
                </div> 
            </div>
            
            <div class="w-6">
                <x-small-cart-component :carts="$carts"/>
               
            </div>
        </div>



   
       

        

@endsection


   
    
     