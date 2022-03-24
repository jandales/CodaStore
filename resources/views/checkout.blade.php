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

        <div class="flex gap50 mb-5">
            <div class="w-6">
                <div class="flex space-between">
                    <h2>Contact Information</h2>
                    <span>Already have an account?<a href="" class="link link-primary">Log in</a></span>                   
                </div>                
                <div class="form-block mt-1">
                    <label for="contact">Email</label>
                    <input type="text" name="email" value=""/>
                </div>

                <div class="mt-2">
                    <h2>Shipping Address</h2>
                </div>
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
                    <label for="contact">Address</label>
                    <input type="text" name="email" value=""/>
                </div>

                <div class="form-block">
                    <label for="contact">Apartment</label>
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


                <div class="form-block">
                    <button class="btn btn-dark">Contenue Shipping</button>
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
   
       

        