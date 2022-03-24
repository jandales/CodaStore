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
                               <label for="">{{  auth()->user()->defaultAddress()->fullAddress() }}</label>
                            </div>
                        </div> 
                    </div> 
                    <table id="checkout" class="cart-table mt-1">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th>PRICE</th>
                                <th>QUANTITY</th>
                                <th>TOTAL</th>
                            </tr>
                        </thead> 
                        <tbody>                          
                          
                            <tr >
                                <td class="column1">                                 
                                    <div class="cartitem">
                                        <div class="pr-image-wrapper"> 
                                            <img src="/{{ $product->imagePath }}" alt="">
                                            <div class="pr-image-overlay flex-vert-center">
                                                    <a href="/shop" class="btn-link btn-cart-delete"><i class="fas fa-times"></i></a>
                                            </div>
                                        </div>
                                      
                                       <div class="flex flex-column m-l-10">
                                            <p>{{ $product->name }}</p>
                                            @if ($properties != Null)
                                            <ul class="cart-variant-ul">
                                                @foreach ($properties as $index =>  $variant)                                                           
                                                        <li>
                                                            <p>{{ $variant['name'] }}:
                                                                <p>{{ $variant['value'] }}</p>
                                                                <p> @if ( $index != count($properties) -1 ) , @endif</p>
                                                            </p>
                                                            
                                                        </li>
                                                @endforeach
                                            </ul>   
                                            @endif
                                            <span class="m-t-10">{{ $product->minQty() }}</span> 
                                        </div>
                                    </div>
                                    
                                </td>
                                <td>
                                     <p class="cart-price">@money($product->prices->selling)</p>
                                </td>                                
                                <td> 
                                    <div class="flex-row">
                                        <span>Qty: </span> <p class="qty">{{ $qty }}</p></td>
                                    </div>                                                                 
                                <td><p class="cart-total" total="{{ $product->prices->selling * $qty }}">@money($product->prices->selling * $qty)</p></td>  
                            </tr>
                            

                        </tbody> 
                     </table>

                  
                </div>
              
    
               <div class="shipping">
                    <div class="panel panel-padding">
                       
                        <h3>Payment Method</h3>

                        <div class="flex space-between flex-row bor12 p-b-13 mt-1">
                        
                                <div class="option">                                                             
                                    <label for="">Cash on Deliver</label>                                                                 
                                </div>

                                <div class="option">
                                    <label for="">Debit / Credit Card </label>                
                                </div>
                           
                           
                        </div>  

                        <form id="placeorderForm" action="{{ route('checkout.placeOrder.direct', [$product,$qty])}}" method="post">

                            @csrf
                        
                        <div class="flex bor12 p-b-13 mt-2">
                            <div class="w-100">
                                <span>Subtotal  :</span>
                            </div>
                            <div class="w-300">  
                                <div class="right">
                                    <div class="flex flex-column">
                                        <p id="subtotal" class="text-align-right">@money($product->prices->selling * $qty)</p>
                                        <p class="text-align-right m-t-5">items : {{ $qty }}</p>
                                    </div>
                                </div>                              
                                
                            </div>
                        </div>

                      

                        <div class="flex bor12 p-b-13 mt-2">
                            <div class="w-100">
                                <span>Shipping Fee :</span>
                            </div>
                            <div class="w-300">
                                <span id="shippingfee"  data-fee="150" class="right">₱ <span>150</span></span>
                            </div>
                        </div>

                       
                        <div class="bor12 mt-1  p-b-13">
                            <div class="coupon">
                                <input type="text" id="input_coupon" name="coupon" placehodler="Coupon Code">
                                <button id="btnCoupon" remove="false" class="btn btn-default">APPLY COUPON</button>                        
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
        <script src="/js/front/cart/cart.js"></script>
        <script src="/js/front/cart/checkout.js"></script>

        <script>

                
              
                document.addEventListener('DOMContentLoaded', function(){
                    calculateSubtotal();
                })              

                function  calculateSubtotal() {
                    let cartTotal = document.querySelector('.cart-total').getAttribute('total')
                    let total = document.getElementById('total')
                    let shippingFee = document.getElementById('shippingfee').getAttribute('data-fee')

                    total.innerText =  parseInt(cartTotal) + parseInt(shippingFee)
                }

        </script>
        @endsection


   
    
     