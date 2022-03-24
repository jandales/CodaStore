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

                <form action="{{ route('cart.selected') }}" method="POST">   
                    @csrf   
                                 
                    <div class="cart">   
                        <div class="cart-container">    
                            <table class="cart-table">
                                <thead>
                                    <tr class="tr-action hidden">
                                         <th colspan="4">
                                            <div class="flex space-between">
                                               <label for="" class="txt-sm"><span class="txt-sm item-selected">2</span> items selected</label>
                                                <div class="flex">                                                   
                                                       <span class="delete" onclick="removeAll()">
                                                            <i class="fas fa-trash cwhite"></i> 
                                                            <span class="link-danger ml5 cwhite txt-sm" cwhite>Delete</span>
                                                       </span>
                                                       <span style="padding: 10px;">|</span>
                                                 
                                                        <span  class="text-primary cwhite txt-sm cancel">Cancel</span>                                               
                                                   
                                                </div>
                                                
                                            </div>
                                         </th>                                 
                                    </tr>
                                 
                                    <tr class="tr-default show">
                                        <th><input  type="checkbox" id="parentCheckbox" name="checkbox"></th>
                                        <th>PRODUCT</th>                                       
                                        <th class="text-centered">QUANTITY</th>
                                        <th class="text-centered">TOTAL</th>                                       
                                    </tr>
                                  
                                </thead> 
                                <tbody>
                                    @if ($carts->count() == 0)
                                    <tr><td colspan="6" class="text-align-center">No Item</td></tr>
                                    @else
                                    @foreach ($carts as $cart)
                                    <tr class="cart-row">
                                        <td class="tblCheckbox">                                        
                                            <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $cart->id }}" {!! $cart->product->stock->qty == 0 ? "disabled" : "" !!} >                                         
                                        </td>
                                        <td>                                           
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
                                        <td class="text-centered">
                                            <p class="cart-total">@money($cart->total())</p>
                                        
                                        </td>
                                      
                                       
                                        
                                    </tr>
                                    
                                    @endforeach
                                    @endif
                                </tbody> 
                            </table>

                            <div class="continue-shooping mt-2">
                                <a href="{{ route('shop')}}" class="btn btn-success">Continue shooping</a>                            
                            </div>
                        </div>
              
    
                        <div class="shipping">
                                <div class="panel panel-padding">
                                
                                    <h3>Shipping & Billing</h3>
                
                                    <div class="flex space-between bor12 p-b-13 mt-1">                                      
                                            <span>Shipping :</span>       
                                            <div class="flex">
                                                <p> @if (auth()->user()->defaultAddress()){{  auth()->user()->defaultAddress()->fullAddress() }} @endif</p>
                                                <span id="addressbook" data-modal-target="modal-addressbook" class="left-auto"><i class="fas fa-edit"></i></span> 
                                            </div>
                                     
                                    </div>

                                    <div class="flex space-between bor12 p-b-13 mt-2">
                                            <span>Subtotal  :</span>
                                            <div class="right">
                                                <div class="flex flex-column">
                                                    <p id="subtotal" class="text-align-right" data-subtotal="{{ cartSubtotal($carts) }}">₱ {{ cartSubtotal($carts) }}</p>
                                                    <p class="text-align-right m-t-5">items : {{ $carts->sum('qty') }}</p>
                                                </div>
                                            </div>     
                                    </div>
                                    <div class="flex space-between bor12 p-b-13 mt-2">                                   
                                        <span>Shipping Fee :</span>
                                        <span id="shippingfee"  data-fee="{{ shippingFee() }}" class="right"><span>@money(shippingFee())</span></span>
                                    </div>                                
                                    <div class="bor12 mt-1  p-b-13">                                 
                                        <div class="coupon">
                                            <input type="text" id="input_coupon" placehodler="Coupon Code">
                                            <button id="btnCoupon" remove="false" class="btn btn-light w-0">APPLY COUPON</button>                                                     
                                        </div>
                                    </div>
                                    <div class="flex space-between bor12 p-b-13 mt-2">
                                        <span>Total :</span> 
                                        <span class="right">₱ <span id="total"></span></span>
                                    </div>

                                    <br>
                        
                                
                                    <button class="button w-12 p-15 dark">Proceed to Checkout</button>
                                    
                                
                                </div>
                
                        </div>
               
                    </div>
                    @endif
                </form>
               
        </div>

 
         <div id="modal-addressbook" class="modal">
            <div class="modal-content">
                <div class="modal-heading">                   
                    <h1>Address Book</h1>
                    <span  class="modal-close">&times</span>
                </div>
                <div class="modal-body">                 
                    
                        <table class="table table-address mt-2">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Reciept Name</th>
                                    <th>Address</th>
                                    <th>Phone Number</th>                                  
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id = "table-body">                              
                                
                            </tbody>
                        </table>                  
                            
                            <div class="flex justify-content-flex-end mt-2 mb-1 ">
                                <button onclick="submit()" class="btn btn-dark w-100">Submit</button>
                                <button class="btn btn-light m-l-10 w-100 modal-close">Close</button>
                            </div>
                   
                    
                </div>
            </div>
        </div>
    
    
      
 
        <script>
            
          
            document.addEventListener('DOMContentLoaded', function(){                
                Carts = @json($carts)
              
            })

         
        </script>

        <script src="/js/front/cart/cart.js"></script>
        <script src="/js/front/cart/addressbook.js"></script>
        <script src="/js/front/cart/updateQuantity.js"></script>
        <script src="/js/front/cart/coupon.js"></script>
        @endsection


   
    
     