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

                @if(empty($cart) || $cart->items->count() == 0)
                    <div class="align-items-content-center flex-column m-t-100">
                        <h1 class="txt-bg">Your cart is empty</h1>    
                        <div class="continue-shooping mt-2">
                            <a href="{{ route('shop')}}" class="btn btn-dark">Continue shopping</a>                            
                        </div>              
                    </div>   
                @else 
                           
                    <div class="cart-1 mb-5">   
                      
                            <table class="cart-table-1">
                                <thead>  
                                    <tr>                                       
                                        <th class="column-1">PRODUCT</th>                                       
                                        <th>QUANTITY</th>
                                        <th class="text-align-right">TOTAL</th>                                       
                                    </tr>
                                </thead> 
                                <tbody>                                                                  
                                    @foreach ($cart->items as $item)
                                    <tr>                                       
                                        <td>                                           
                                                <div class="cart-item">
                                                    <div class="pr-image-wrapper">                                                   
                                                         <img src="{{ $item->product->imagePath }}" alt="">
                                                         <div class="pr-image-overlay flex-vert-center">
                                                             <span class="cart-item-remove text-danger" data-id="{{ $item->id }}"><i class="fas fa-times"></i></span>
                                                         </div>
                                                    </div>
                                                    <div class="flex flex-column m-l-10 gap10">
                                                        <a class="link"  href="{{ route('shop.product',[ $item->product ] )}}">{{ $item->product->name }} </a>
                                                        <div class="product-info">@money($item->product->regular_price)</div>   
                                                        @if ($item->properties != null)
                                                            @foreach ($item->properties as $variant)  
                                                                <div class="product-info">{{ $variant['name'] }} :{{ $variant['value'] }} </div> 
                                                            @endforeach
                                                         @endif                      
                                                    </div>
                                                </div>
                                            
                                        </td>                                      
                                        <td>  
                                                <div class="cart-form-group">
                                                    <div class="btn-num-product-down flex-vert-center add-less-quantity" type="less"><i class="fas fa-minus"></i></div>
                                                    <input class="cart-qty num-product bg-grey"  item="{{ $item->id }}"  type="number" value="{{ $item->qty }}"  {!! $item->product->stock->qty == 0 ? "disabled" : "" !!} >
                                                    <div   class="btn-num-product-up flex-vert-center add-less-quantity" type="add"><i class="fas fa-plus"></i></div>
                                                </div>                                                
                                        </td>
                                        <td class="text-align-right">
                                            <span class="cart-total block m-t-15">@money($item->subtotal())</span>
                                        </td>
                                      
                                       
                                        
                                    </tr>                                    
                                    @endforeach
                             
                                </tbody>                                 
                            </table>
                    
                            <div class="cart-summary">
                                <a href="{{ route('shop')}}" class="link"> Continue shopping </a>
                               <div class="flex flex-column gap30">
                                    <div class="flex justify-content-flex-end gap20">
                                        <span class="bold">Subtotal</span>
                                        <span>@money($cart->total)</span>
                                    </div>
                                    <span>Taxes and shipping calculated at checkout</span>
                                    <a href="{{ route('checkout.information') }}"><button class="btn btn-dark button-py10">CHECK OUT</button></a>
                               </div>

                              
                            </div>

                           
                   
    
              
               
                    </div>
                @endif
            
               
        </div>

 
  
    
    
      
 
       
 

        {{-- <script src="/js/front/cart/cart.js"></script>
        <script src="/js/front/cart/updateQuantity.js"></script>
        <script type="module" src="/js/front/cart/coupon.js"></script> --}}
        @endsection


   
    
     