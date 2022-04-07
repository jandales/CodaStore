@extends('layout.front.app')

@section('content')    
    
        <div class="container">
    
            <div class="pagetitle">
                <h3>Shopping Cart</h3>
            </div>
           
                @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
                @endif

                @if (session('message'))
                    <div class="alert alert-warning mt-1">{{ session('message')}}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mt-1">{{ session('error')}}</div>
                 @endif


                 @if( $carts->count()  == 0)
                 <div class="align-items-content-center flex-column m-t-100">
                     <h1 class="txt-bg">No items in your cart</h1>    
                     <div class="continue-shooping mt-2">
                         <a href="{{ route('shop')}}" class="btn btn-success">Shop Now</a>                            
                     </div>              
                 </div>
              
                 
                 @else    

           
          
                                 
                    <div class="cart">   
                        <div class="cart-container">    
                            <table class="cart-table">
                                <thead>  
                                    <tr>                                       
                                        <th class="column-1">PRODUCT</th>                                       
                                        <th class="text-centered">QUANTITY</th>
                                        <th class="column-3 text-centered">TOTAL</th>                                       
                                    </tr>
                                </thead> 
                                <tbody>
                                    @if ($carts->count() == 0)
                                    <tr><td colspan="6" class="text-align-center">No Item</td></tr>
                                    @else
                                    @foreach ($carts as $cart)
                                    <tr class="cart-row">                                       
                                    <td class="column-1">                                           
                                                <div class="cartitem">
                                                    <div class="pr-image-wrapper">                                                   
                                                         <img src="{{ $cart->product->imagePath }}" alt="">
                                                         <div class="pr-image-overlay flex-vert-center">
                                                             <span onclick="remove({{$cart->id}})"><i class="fas fa-times"></i></span>
                                                         </div>
                                                    </div>
                                                    <div class="flex flex-column m-l-10">
                                                        <a  href="{{ route('shop.product',[ $cart->product ] )}}">
                                                         <p class="p-name">{{ $cart->product->name }}</p> 
                                                        </a>
                                                            @if ($cart->properties != null)
                                                                <ul class="cart-variant-ul">
                                                                    @foreach ($cart->properties as $index =>  $variant)                                                           
                                                                            <li>
                                                                                <p>{{ $variant['name'] }}:
                                                                                    <p>{{ $variant['value'] }}</p>
                                                                                    <p> @if ( $index != count($cart->properties) -1 ) , @endif</p>
                                                                                </p>
                                                                            </li>
                                                                    @endforeach
                                                                </ul> 
                                                            @endif  
                                                            <p class="cart-price m-t-10">@money($cart->price)</p>                                                      
                                                        <span class="m-t-10">{{ $cart->product->minQty() }}</span>                                                       
                                                    </div>
                                                </div>
                                            
                                        </td>                                      
                                        <td class="text-centered">  
                                                <div class="cart-form-group center">
                                                    <div class="btn-num-product-down flex-vert-center add-minus-quantity" type="minus"> <i class="fas fa-minus"></i></div>
                                                    <input class="cart-qty num-product bg-grey"  item="{{ $cart->id }}"  type="number" value="{{ $cart->qty }}"  {!! $cart->product->stock->qty == 0 ? "disabled" : "" !!} >
                                                    <div class="btn-num-product-up flex-vert-center add-minus-quantity" type="add"> <i class="fas fa-plus"></i></div>
                                                </div>
                                                 @if ($cart->discount != 0)
                                                     <p class="cart-discount">Discounted</p>
                                                 @endif
                                        </td>
                                        <td class="column-3 text-centered">
                                            <p class="cart-total">@money($cart->total())</p>
                                        
                                        </td>
                                      
                                       
                                        
                                    </tr>
                                    
                                    @endforeach
                                    @endif
                                </tbody>                                 
                            </table>
                            <div class="cart-table-footer">
                                                           
                                    <a href="{{ route('shop')}}">
                                        <button class="btn btn-dark">  Continue shooping</button>
                                    </a>
                                                                
                      
                                <div class="coupon">
                                    <input type="text" id="input_coupon" placeholder="Coupon Code">
                                    <button id="btnCoupon" remove="false" class="btn btn-dark w-0">Apply</button>                                                     
                                </div>
                            </div>

                           
                        </div>
              
    
                        <div class="w-450">
                                <div class="panel panel-padding">
                                
                                    <h3>Order Summary</h3>               
                                    <div class="bor12 mt-1  p-b-13">   </div>

                                    <div class="flex space-between p-b-13 mt-2">
                                            <span>Items  :  {{ $carts->sum('qty') }}</span>
                                            <span id="subtotal" class="text-align-right" data-subtotal="{{ cartSubtotal($carts) }}">@money(cartSubtotal($carts))</span>                                                  
                                    </div>
                                    <div class="flex space-between bor12 p-b-13">                                   
                                        <span>Shipping Fee :</span>
                                        <span id="shippingfee"  data-fee="{{ shippingFee() }}" class="right"><span>@money(shippingFee())</span></span>
                                    </div>                                
                                   
                                    <div class="flex space-between p-b-13 mt-2">
                                        <span>Total Cost :</span> 
                                        {{-- <span class="right">₱ <span id="total"></span></span> --}}
                                        <span class="right"> @money(cartSubtotal($carts) + shippingFee()) </span>
                                       
                                    </div>

                                    <br>                        
                                    <a href="{{ route('checkout.information')}}">
                                        <button class="button w-12 p-15 dark">Proceed to Checkout</button>
                                    </a>
                                    
                                    
                                
                                </div>
                
                        </div>
               
                    </div>
                    @endif
            
               
        </div>

 
  
    
    
      
 
        <script>
            
          
            document.addEventListener('DOMContentLoaded', function(){                
                Carts = @json($carts)
              
            })

         
        </script>

        <script src="/js/front/cart/cart.js"></script>
        <script src="/js/front/cart/updateQuantity.js"></script>
        <script src="/js/front/cart/coupon.js"></script>
        @endsection


   
    
     