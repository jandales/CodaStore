@extends('layout.front.app')
@section('content')    
    <div class="container">    
        <div class="pagetitle">
            <h3>Checkout</h3>
        </div>  
        
        @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
        @endif
        @if (session('message'))
                <div class="alert alert-warning mt-1">{{ session('message')}}</div>
        @endif  

        <div class="flex space-between  mt-2 mb-5">
            <div class="w-6">
                <div class="flex space-between  border-b-solid py-1">
                    <h2 class="uppercase">Contact Information</h2>
                    @guest
                        <span>Already have an account? <a href="" class="link link-primary"> Log in</a></span> 
                    @endguest    
                    @auth
                        <span class="link-primary"><i class="fas fa-pen"></i></span>
                    @endauth                                  
                </div> 
                @auth
                    <span class="block mt-1">{{ auth()->user()->email }}</span>
                @endauth     
                @guest          
                    <div class="form-block mt-1">
                        <label for="contact" class="text-sm">Email</label>
                        <input type="text" name="email" value=""/>
                    </div>
                @endguest

                <div class="flex space-between mt-2 border-b-solid py-1">                
                    <h2 class="uppercase">Shipping Details</h2>
                    @auth
                        @if(auth()->user()->shippingDefaultAddress())
                          <span class="link-primary"><i class="fas fa-pen"></i></span>
                        @endif
                    @endauth
                </div>
                @auth
                    <div class="flex flex-column gap5 mt-1 mb-1">
                        @if(auth()->user()->shippingDefaultAddress())
                            <div class="flex space-between">
                                <span>{{ auth()->user()->shippingDefaultAddress()->name() }}</span>
                                <span>{{ auth()->user()->email }}</span>                                  
                            </div> 
                            <span>{{ auth()->user()->shippingDefaultAddress()->street }}</span>
                            <span>{{ auth()->user()->shippingDefaultAddress()->city . " " . auth()->user()->shippingDefaultAddress()->region }}</span>
                            <span>{{ auth()->user()->shippingDefaultAddress()->country }}</span> 
                        @else
                            <x-form-shipping></x-form-shipping>
                        @endif                              
                    </div>    
                @endauth
                @guest                
                    <x-form-shipping></x-form-shipping>
                @endguest




                <div class="flex space-between mt-2 border-b-solid py-1">                
                    <h2 class="uppercase">Payment Details</h2>
                    @auth
                        @if (auth()->user()->defaultPayment())  
                            <span class="link-primary"><i class="fas fa-pen"></i></span>
                        @endif 
                    @endauth
                </div>
                
                @auth
                    <div class="flex flex-column gap5 mt-1 mb-1">  
                        @if (auth()->user()->defaultPayment())                     
                            <span>{{ auth()->user()->defaultPayment()->card_name }}</span>
                            <span>{{ auth()->user()->defaultPayment()->card_number  }}</span>
                        @else
                            <x-form-payment-option></x-form-payment-option>
                        @endif            
                    </div>    
                @endauth


                @guest
                    <x-form-payment-option></x-form-payment-option>
                @endguest            
               


                <div class="form-block mt-2">
                    <button class="btn btn-dark w-3 p-10">Contenue Shipping</button>
                </div> 
            </div>
            
            <div class="w-3">
                <div class="bg-grey p-20">
                    <div class="flex flex-column gap20">
                        @foreach ($carts as $cart)
                        <div class="cart-sm gap10">                    
                            <div class="cart-image">
                                <img class="img" src="/{{ $cart->product->imagePath }}" alt="" srcset="">
                                <div class="cart-image-overlay flex-vert-center">
                                    <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
                                </div>
                            </div>
                            <div class="w-12">
                                <div class="flex space-between">
                                    <span class="cart-item-name">{{$cart->product->name}}</span> 
                                    <span class="cart-item-name">@money($cart->price)</span>   
                                </div>                           
                                <span class="cart-item-info">Qty: {{ $cart->qty  }}</span>  
                                <ul class="cart-item-variant">    
                                </ul> 
                            </div>  
                        </div>
                        @endforeach
                    </div>

                    <div class="form-inline gap10 mt-2">
                        <input type="text" placeholder="Promo Code" value="">
                        <div class="btn btn-dark">Apply</div>
                    </div>
                    <div class=" mt-2">
                        <div class="flex space-between">
                            <span>Subtotal</span>
                            <span>@money(cartSubtotal($carts))</span>
                        </div>
        
                        <div class="flex space-between mt-1">
                            <span>Shipping</span>
                            <span>@money(shippingFee())</span>
                        </div>
    
                        <div class="flex space-between mt-1">
                            <span>Total</span>
                            <span>@money(cartSubtotal($carts) + shippingFee() )</span>
                        </div>
                    </div>
                </div>
               

               
            </div>
        </div>


    </div>
   
       

        

@endsection


   
    
     