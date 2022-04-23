@extends('layout.front.app')
@section('content')
        <div class="container">
            <div class="flex  account mt-3 mb-3"> 
                <div class="col1">
                    @include('layout.front.sidebar')
                </div>   
                <div class="col2">
                    @if (session('success'))
                         <div class="alert alert-success alert-bordered mt-1 mb-1">{{ session('success')}}</div>
                    @endif  
                    <div class="card no-border pad-2 bg-grey">

                        <div class="card-heading">
                            <h2>Order details</h2>                       
                        </div>                       
                        <div class="orders-wrapper mt-2"> 
                            <div class="orders">
                                <div class="order">
                                    <div class="order-header">
                                      <div class="align-items-content-center">
                                          <span>OR#: </span> <a href="{{ route('orders.details',[$order])}}" for="order">{{ $order->ordernumber() }}</a></div>
                                       <label class="order-status capitalize" for="status">{{$order->status }}</label>
                                    </div>
                                    <div class="order-body">
                                        @foreach ($order->items as $item)
                                          <div class="order-wrapper">
                                               <div class="order-item">
                                                   <div class="order-item-image">
                                                       <img src="/{{ $item->product->imagePath }}" alt="">
                                                   </div>                                                      
                                                   <div class="order-item-name ml-1">
                                                       <label for="name">{{ $item->product->name }}</label>
                                                   </div>
                                                   <div class="order-item-price ">
                                                         <label  for="price">@money($item->price)</label>
                                                   </div>
                                                   <div class="order-item-quantity">
                                                       <div class="flex">
                                                           <span>Qty:</span>
                                                           <label  class="ml5"for="qty">{{ $item->qty }}</label>
                                                       </div>
                                                    </div> 
                                                    <div class="order-item-write-review">
                                                        <a href="{{ route('orders.review', [ $item->product ])}}" class="link-primary">Write a review</a>
                                                     </div>                                                      
                                           </div>
                                          </div>                                           
                                        @endforeach
                                    </div>
                                </div> 
                            </div>
                           <div class="flex justify-content-flex-start w-12">
    
                                <div class="order-detail-wrapper align-self-start">   
                                    <div class="order-title">
                                        <p>Shipping</p>
                                    </div>  
                                    <div class="separator mt-1 mb-1"></div>                          
                                   <div class="shipping-detail mt-1">
                                      <span><i class="fas fa-user"></i><span class="m-l-10">{{ $order->shipping->personName() }}</span></span>
                                      <span><i class="fas fa-address-book"></i><span class="m-l-10">{{ $order->shipping->address() }}</span></span>
                                      <span><i class="fas fa-phone"></i><span class="m-l-10">{{ $order->shipping->phone }}</span><span>
                                   </div>
                                </div>
    
                                <div class="order-detail-wrapper align-self-strech ml-1">   
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
                                            <span>@money($order->shipping_charge)</span>
                                        </div>
                                        <div class="flex space-between">
                                            <span>Coupon</span>
                                            <span>@money($order->coupon_amount)</span>
                                        </div>
                                        <div class="separator mt-1 mb-1"></div>
                                        <div class="flex space-between">
                                            <span>Total ( VAT Incl. )</span>
                                            <span>@money($order->gross_total)</span>
                                        </div>
                                      
                                   </div>
                                </div>

                              
    
                           </div>
                           <div class="mt-2">                            
                                <button class="w-2 right btn-danger m-l-10 uppercase"><a class="cwhite" href="{{ url()->previous() }}">Back</a></button>
                                @if($order->status == "pending")
                                    <form action="{{ route('orders.cancel',[$order])}}" method="post">
                                        @csrf
                                        @method('Put')
                                        <button class="w-2 right uppercase">Cancel Order</button>
                                    </form>
                                @endif
                              
                               
                           </div>
                          
                        </div>
                    </div> 
                </div>                      
              
                            
            </div>
        </div>
@endsection