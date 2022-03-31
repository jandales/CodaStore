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

        <div class="flex gap50  mt-4 mb-5">
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
                      <span class="link-primary"><i class="fas fa-pen"></i></span>
                    @endauth
                </div>
                @auth
                    <div class="flex flex-column gap5 mt-1 mb-1">
                        <div class="flex space-between">
                            <span>{{ auth()->user()->shippingDefaultAddress()->name() }}</span>
                            <span>{{ auth()->user()->email }}</span>                                  
                        </div> 
                        <span>{{ auth()->user()->shippingDefaultAddress()->street }}</span>
                        <span>{{ auth()->user()->shippingDefaultAddress()->city . " " . auth()->user()->shippingDefaultAddress()->region }}</span>
                        <span>{{ auth()->user()->shippingDefaultAddress()->country }}</span>                                  
                    </div>    
                @endauth
                @guest                
                <div class="form-shipping">
                    <div class="flex gap10 mt-1">
                        <div class="form-block w-6">
                            <label for="contact">First name</label>
                            <input type="text" name="email" value=""/>
                        </div>
                        <div class="form-block w-6">
                            <label for="contact">Last name</label>
                            <input type="text" name="email" value=""/>
                        </div>
                    </div>   
                    <div class="flex gap10 m-t-1">
                        <div class="form-block w-3">
                            <label for="contact">Country</label>
                            <input type="text" name="email" value=""/>
                        </div>
                        <div class="form-block w-3">
                            <label for="contact">Region</label>
                            <input type="text" name="email" value=""/>
                        </div>
    
                        <div class="form-block w-3">
                            <label for="contact">Zip Code</label>
                            <input type="text" name="email" value=""/>
                        </div>
                    </div>
                    <div class="form-block">
                        <label for="contact">Street</label>
                        <input type="text" name="email" value=""/>
                    </div>
                    <div class="form-block">
                        <label for="contact">City</label>
                        <input type="text" name="email" value=""/>
                    </div>
                    <div class="form-block">
                        <label for="contact">Phone Number</label>
                        <input type="text" name="email" value=""/>
                    </div>
                </div>
                @endguest




                <div class="flex space-between mt-2 border-b-solid py-1">                
                    <h2 class="uppercase">Payment Details</h2>
                    @auth
                        <span class="link-primary"><i class="fas fa-pen"></i></span>
                    @endauth
                </div>
                
                @auth
                    <div class="flex flex-column gap5 mt-1 mb-1">                       
                        <span>Jesus Andales</span>
                        <span>8293849283993284932</span>
                        <span>02/19 - 2546</span>                                                     
                    </div>    
                @endauth


                @guest
                    <div class="form-payment">
                        <div class="form-block mt-1">
                            <label for="contact" class="text-sm">NAME</label>
                            <input type="text" name="email" value=""/>
                        </div>

                        <div class="form-block mt-1">
                            <label for="contact" class="text-sm">CARD NUMBER</label>
                            <input type="text" name="email" value=""/>
                        </div>

                        <div class="flex gap20 m-t-1">
                            <div class="form-block w-6">
                                <label for="contact" class="text-sm">EXPIRY DATA</label>
                                <input type="text" name="email" value=""/>
                            </div>
                            <div class="form-block w-6" class="text-sm">
                                <label for="contact" class="text-sm">CVC CODE</label>
                                <input type="text" name="email" value=""/>
                            </div>                   
                        </div>  
                    </div>
                @endguest              
               


                <div class="form-block mt-2">
                    <button class="btn btn-dark w-3 p-10">Contenue Shipping</button>
                </div> 
            </div>








            
            <div class="w-6">
                <div class="w-400 bg-grey p-20">
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
                            <span>$0.00</span>
                        </div>
        
                        <div class="flex space-between mt-1">
                            <span>Shipping</span>
                            <span>cost</span>
                        </div>
    
                        <div class="flex space-between mt-1">
                            <span>Total</span>
                            <span>$0.00</span>
                        </div>
                    </div>
                </div>
               

               
            </div>
        </div>


    </div>
   
       

        

@endsection


   
    
     