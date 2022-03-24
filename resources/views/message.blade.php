@extends('layout.front.app')

@section('content')
    

    <!--Featured Product-->
        {{-- <div class="container">    

            <div class="thank-you flex flex-column justify-content-center align-items-center">
                <h1 class="text-50 mb-2">                    
                    Thank you  :) for Purchasing our products                   
                </h1>
                <a href="{{ route('shop')}}" class="btn btn-success">Continue Shopping</a>
            </div>
         
        </div> --}}

        <div class="container">
            <div class="align-items-content-center">
              
                <div class="w-1000 mb-3">
                    <div class="pagetitle">
                        <h3>Order Details</h3>
                    </div>
                   
                     
                    <div class="card no-border">                                          
                        <div class="orders-wrapper"> 
                            <div class="orders">
                                <div class="order">
                                    <div class="order-header">
                                      <div class="align-items-content-center">
                                          <span>OR#: </span> <a href="{{ route('orders.details',[$order])}}" for="order">{{ $order->ordernumber() }}</a></div>
                                       <label class="order-status capitalize" for="status">{{$order->status }}</label>
                                    </div>
                                    <div class="order-body">
                                        @foreach ($order->orderProducts as $orderitem)
                                          <div class="order-wrapper">
                                               <div class="order-item">
                                                   <div class="order-item-image">
                                                       <img src="/{{ $orderitem->product->imagePath }}" alt="">
                                                   </div>                                                      
                                                   <div class="order-item-name ml-1">
                                                       <label for="name">{{ $orderitem->product->name }}</label>
                                                   </div>
                                                   <div class="order-item-price ">
                                                       <div class="flex">
                                                           <span>₱</span>
                                                           <label  for="price">{{ $orderitem->price }}</label>
                                                       </div>
                                                   
                                                   </div>
                                                   <div class="order-item-quantity">
                                                       <div class="flex">
                                                           <span>Qty:</span>
                                                           <label  class="ml5"for="qty">{{ $orderitem->qty }}</label>
                                                       </div>
                                                    </div> 
                                                                                                      
                                           </div>
                                          </div>                                           
                                        @endforeach
                                    </div>
                                </div> 
                            </div>
                           <div class="flex justify-content-flex-start w-12">
    
                                <div class="order-detail-wrapper align-self-start bordered">   
                                    <div class="order-title">
                                        <p>Shipping</p>
                                    </div>  
                                    <div class="separator mt-1 mb-1"></div>                          
                                   <div class="shipping-detail mt-1">
                                      <span><i class="fas fa-user"></i><span class="m-l-10">{{ $order->shipping->reciept_name }}</span></span>
                                      <span><i class="fas fa-address-book"></i><span class="m-l-10">{{ $order->shipping->address() }}</span></span>
                                      <span><i class="fas fa-phone"></i><span class="m-l-10">{{ $order->shipping->reciept_number }}</span><span>
                                   </div>
                                </div>
    
                                <div class="order-detail-wrapper align-self-strech ml-1 bordered">   
                                    <div class="order-title">
                                        <p>Order Summary</p>
                                    </div>  
                                    <div class="separator mt-1 mb-1"></div>                          
                                   <div class="order-summary mt-1">
                                        <div class="flex space-between">
                                            <span>Subtotal</span>
                                            <span>@money($order->subtotal())</span>
                                        </div>
                                        <div class="flex space-between">
                                            <span>Qty</span>
                                            <span>{{ $order->totalItems() }}</span>
                                        </div>
                                        <div class="flex space-between">
                                            <span>Shipping Fee</span>
                                            <span>@money($order->shipping_fee)</span>
                                        </div>
                                        <div class="flex space-between">
                                            <span>Coupon</span>
                                            <span>@if ($order->couponUsed) 
                                                    @money($order->couponUsed->amount)
                                                @else
                                                    @money(0)
                                            @endif</span>
                                        </div>
                                        <div class="separator mt-1 mb-1"></div>
                                        <div class="flex space-between">
                                            <span>Total ( VAT Incl. )</span>
                                            <span>@money($order->total())</span>
                                        </div>
                                      
                                   </div>
                                </div>

                              
    
                           </div>
                          
                           <div class="w-12 mt-2 flex space-between">
                                <div class="continue-shooping">
                                    <a href="{{ route('shop')}}" class="btn btn-success">Continue shooping</a>                            
                                </div>                              
                                @if($order->status == "pending")
                                    <form action="{{ route('orders.cancel',[$order])}}" method="post">
                                        @csrf
                                        @method('Put')
                                        <button class="btn btn-danger">Cancel Order</button>
                                    </form>
                                @endif
                              
                               
                           </div>
                          
                        </div>
                    </div> 
                                     
              
                            
            </div>
            </div>
           
        </div>
        
   
@endsection