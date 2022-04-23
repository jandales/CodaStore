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

                @if(empty($cart))
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
                                    @foreach ($cart->items as $item)
                                    <tr class="cart-row">                                       
                                    <td class="column-1">                                           
                                                <div class="cartitem">
                                                    <div class="pr-image-wrapper">                                                   
                                                         <img src="{{ $item->product->imagePath }}" alt="">
                                                         <div class="pr-image-overlay flex-vert-center">
                                                             <span class="cart-item-remove text-danger" data-id="{{ $item->id }}"><i class="fas fa-times"></i></span>
                                                         </div>
                                                    </div>
                                                    <div class="flex flex-column m-l-10">
                                                        <a  href="{{ route('shop.product',[ $item->product ] )}}">
                                                         <p class="p-name">{{ $item->product->name }}</p> 
                                                        </a>
                                                            @if ($item->properties != null)
                                                                <ul class="cart-variant-ul">
                                                                    @foreach ($item->properties as $index =>  $variant)                                                           
                                                                            <li>
                                                                                <p>{{ $variant['name'] }}:
                                                                                    <p>{{ $variant['value'] }}</p>
                                                                                    <p> @if ( $index != count($item->properties) -1 ) , @endif</p>
                                                                                </p>
                                                                            </li>
                                                                    @endforeach
                                                                </ul> 
                                                            @endif  
                                                            <p class="cart-price m-t-10">@money($item->product->regular_price)</p>                                                      
                                                        <span class="m-t-10">{{ $item->product->minQty() }}</span>                                                       
                                                    </div>
                                                </div>
                                            
                                        </td>                                      
                                        <td class="text-centered">  
                                                <div class="cart-form-group center">
                                                    <div class="btn-num-product-down flex-vert-center add-less-quantity" type="less"><i class="fas fa-minus"></i></div>
                                                    <input class="cart-qty num-product bg-grey"  item="{{ $item->id }}"  type="number" value="{{ $item->qty }}"  {!! $item->product->stock->qty == 0 ? "disabled" : "" !!} >
                                                    <div   class="btn-num-product-up flex-vert-center add-less-quantity" type="add"><i class="fas fa-plus"></i></div>
                                                </div>                                                
                                        </td>
                                        <td class="column-3 text-centered">
                                            <p class="cart-total">@money($item->subtotal())</p>
                                        
                                        </td>
                                      
                                       
                                        
                                    </tr>                                    
                                    @endforeach
                             
                                </tbody>                                 
                            </table>
                            <div class="cart-table-footer">                                                           
                                    <a href="{{ route('shop')}}">
                                        <button class="btn btn-dark">  Continue shooping</button>
                                    </a>
                            </div>

                           
                        </div>
              
    
                        <div class="w-450">
                                <div class="panel panel-padding">
                                
                                    <h3>Order Summary</h3>             
                                   
                                    <div class="flex space-between bor12 py-1 mt-2">
                                            <span id="cart-items-count">Items  :  {{ $cart->totalItems() }}</span>
                                            <span id="subtotal" class="text-align-right">@money($cart->total)</span>                                                  
                                    </div>
                                    <div class="flex flex-column bor12 py-1">                                   
                                        <div class="flex  align-items-center space-between">
                                            <span>Shipping :</span>
                                            <select id="select-shipping-method" class="w-9">
                                                @foreach ($shipping_methods as $method)
                                                    <option value="{{ $method->id }}">{{ $method->name }}</option>                                                                                                   
                                                @endforeach
                                            </select>
                                        </div>  
                                        <span id="shipping-method-description" class="align-self-end mt-1"><span>{{ $shipping_methods[0]->description }} </span></span>
                                        <span id="shipping-method-amount" class="align-self-end mt-1"><span>@money($shipping_methods[0]->amount)</span></span>
                                    </div> 
                                    
                                    
                                    
                                    <div id="has-coupon" class="flex flex-column bor12 py-1 {{ $cart->coupon_id == null ? 'hidden' : ''}}">   

                                        <div class="flex space-between">
                                            <span>Coupon :  <small id="coupon-code">{{ $cart->couponName('name') }}</small></span>
                                            <span id="coupon-amount"  class="right">@money($cart->discount)</span>
                                        </div>
                                        <div class="flex space-between mt10">
                                            <span id="coupon-description">{{ $cart->couponName('description') }}</span>
                                            <span id="btn-coupon-remove"  class="right text-danger pointer">Remove</span>
                                        </div>                                    

                                    </div> 
                                    
                                    <div id="coupon-form" class="coupon bor12 {{ $cart->coupon_id != null ? 'hidden' : ''}}">
                                        <input type="text" id="coupon_code" validator-input="coupon_code" name="coupon_code" placeholder="Coupon Code">
                                        <button id="btn-coupon-apply"  class="btn btn-dark">Apply</button>                                                     
                                    </div>
                                   
                                    <div class="flex space-between p-b-13 mt-2">
                                        <span>Total Cost :</span> 
                                        {{-- <span class="right">₱ <span id="total"></span></span> --}}
                                        <span id="grand-total" class="right"> @money($cart->grandTotal()) </span>
                                       
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
                Carts = @json($cart)
              
            })

         
        </script>

        <script src="/js/front/cart/cart.js"></script>
        <script src="/js/front/cart/updateQuantity.js"></script>
        <script type="module" src="/js/front/cart/coupon.js"></script>
        @endsection


   
    
     