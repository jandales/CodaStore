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
       
        <div class="cart">         
            <div class="cart-container">                 
                <div class="shipping-wrapper">
                    <div class="shipping">
                        <div class="shipping-heading">
                            <h3 for="">Shipping Address</h3>
                            <span id="addressbook" data-modal-target="modal-addressbook" class="left-auto cprimary">Edit</span> 
                        </div>
                        <div class="shipping-body">
                            @if (!empty(auth()->user()->defaultAddress()))
                                <label for="">{{  auth()->user()->defaultAddress()->fullAddress() }}</label>
                            @endif
                          
                        </div>
                    </div> 
                </div>    
                <table id="checkout" class="cart-table mt-1">
                    <thead>
                        <tr>
                            <th class="column-1">PRODUCT</th>                         
                            <th class="column-2 text-centered">QUANTITY</th>
                        <th class="column-3 text-centered">TOTAL</th>                               
                        </tr>
                    </thead> 
                    <tbody>
                        @if ($carts->count() == 0)
                            <tr><td>No Item</td></tr>
                        @else
                        @foreach ($carts as $cart)
                            <tr class="cart-row">
                                <td class="column-1">                                 
                                    <div class="cartitem">
                                        <div class="pr-image-wrapper"> 
                                            <img src="/{{ $cart->product->imagePath }}" alt="">
                                            <div class="pr-image-overlay flex-vert-center">
                                                <span class="btn-link btn-cart-delete" onclick="removeItem({{$cart->id}})"><i class="fas fa-times"></i></span>
                                            </div>
                                        </div>                                      
                                        <div class="flex flex-column m-l-10">
                                            <p class="p-name">{{ $cart->product->name }}</p>
                                            @if ($cart->properties != Null)
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
                                <td class="column-2 text-centered">
                                
                                         <p class="qty">Qty: {{ $cart->qty }}</p>
                                                                        
                                </td>
                                <td class="column-3 text-centered"><p class="cart-total">@money($cart->total())</p></td>
                            </tr>                            
                        @endforeach
                            @endif
                        </tbody> 
                     </table>                  
                </div>
              
    
               <div class="billing">
                    <div class="panel panel-padding">
                       
                        <h3>Payment Method</h3>

                        <div class="flex space-between flex-row bor12 p-b-13 mt-1">
                        
                                <div class="payment-option" method="cash">                                                             
                                    <label for="">Cash on Deliver</label>                                                                 
                                </div>

                                <div class="payment-option" method="card">
                                    <label for="">Debit / Credit Card </label>                
                                </div>
                           
                           
                        </div> 

                        <form id="placeorderForm" action="{{ route('checkout.placeOrder')}}" method="post">

                            @csrf
                            <input type="hidden" name="shipping_fee" value="{{ shippingFee() }}">
                            <input type="hidden" name="payment_method" value="">
                            <input type="hidden" name="reference_number" value="">
                            <input type="hidden" name="amount" value="">
                        
                        <div class="flex bor12 p-b-13 mt-2">
                            <div class="w-100">
                                <span>Subtotal  :</span>
                            </div>
                            <div class="w-300">  
                                <div class="right">
                                    <div class="flex flex-column">
                                        <p id="subtotal" class="text-align-right" data-subtotal="{{ cartSubtotal($carts) }}">₱ {{ cartSubtotal($carts) }}</p>
                                        <p class="text-align-right m-t-5">items : {{ $carts->sum('qty')}}</p>
                                    </div>
                                </div>                              
                                
                            </div>
                        </div>

                      

                        <div class="flex bor12 p-b-13 mt-2">
                            <div class="w-100">
                                <span>Shipping Fee :</span>
                            </div>
                            <div class="w-300">
                                <span id="shippingfee"  data-fee="{{ shippingFee() }}" class="right"> <span>@money(shippingFee())</span></span>
                            </div>
                        </div>

                       
                        <div class="bor12 mt-1  p-b-13">
                            <div class="coupon">
                                <input type="text" id="input_coupon" name="coupon" placehodler="Coupon Code">
                                <button id="btnCoupon" remove="false" class="btn btn-light">APPLY COUPON</button>                        
                             </div>
                        </div>
                        

                         <div class="flex bor12 p-b-13 mt-2">
                            <div class="w-100">
                                <span>Total :</span>
                            </div>
                            <div class="w-300">
                                <span class="right">₱ <span id="total"></span></span>
                            </div>
                        </div>

                        <br>
                            <button id="placeOrder" class="button w-12 p-15 dark">Place Order</button>
                        </form>
                       
                    
                    </div>
    
               </div>
               
            </div>
                     
        </div>

        <div id="modal-addressbook" class="modal">
            <div class="modal-content">
                <div class="modal-heading">                   
                    <h1>Address Book</h1>
                    <span  class="modal-close">&times</span>
                </div>
                <div class="modal-body">
                  
                    
                        <table class="table  table-address mt-2">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Reciept Name</th>
                                    <th>Address</th>
                                    <th>Phone Number</th>                                 
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody  id = "table-body">                              
                                
                            </tbody>
                        </table>                  
                            
                            <div class="flex justify-content-flex-end mt-2 mb-1">
                                <button onclick="submit()" class="btn btn-dark w-100">Submit</button>
                                <span class="btn btn-light m-l-10 modal-close ">Close</span>
                            </div>
                   
                    
                </div>
            </div>
        </div>

   
        <script src="/js/front/cart/addressbook.js"></script>
        <script src="/js/front/cart/checkout.js"></script>
        <script src="/js/front/cart/coupon.js"></script>


        

        @endsection


   
    
     