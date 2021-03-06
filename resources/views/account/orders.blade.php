@extends('layout.front.app')
@section('content')
    <div class="container">
        <div class="flex account mt-3 mb-3">
            <div class="col1">
                @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card parent-card no-border pad-2 bg-grey">
                    <div class="card-heading">
                        <h2>Orders</h2>
                    </div>    
                    <div class="orders-wrapper">    
                        <ul class="order-navigation mb-2">
                            <li class="{{(request()->is('account/orders/all')) ? 'active' : ''}}">
                                <a href="{{ route('account.orders',['all'])}}">All</a>
                            </li>                   
                            <li class="{{(request()->is('account/orders/confirmed')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['confirmed'])}}">To Ship</a>
                            </li>
                            <li class="{{(request()->is('account/orders/shipped')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['shipped'])}}">To Recieve</a>
                            </li>
                            <li class="{{(request()->is('account/orders/completed')) ? 'active' : ''}}" >
                                <a href="{{ route('account.orders',['completed'])}}">Delivered</a>
                            </li>
                            <li class="{{(request()->is('account/orders/cancelled')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['cancelled'])}}">Cancelled</a>
                            </li>
                        </ul> 
                        <div class="orders">
                            @if($orders->count() == 0)
                               <div class="block mt-2">
                                   <h1 for="" class="block text-center">No Record Found</h1> 
                               </div>
                            @endif
                            @foreach ($orders as $order)
                                <div class="order">
                                    <div class="order-header">
                                        <div class="align-items-content-center">
                                            <span>OR#:</span><a href="{{ route('orders.details',[$order->encryptedId()])}}" for="order">{{ $order->order_number }}</a>
                                        </div>
                                            <label class="capitalize order-status" for="status">{{$order->status }}</label>
                                    </div>                              
                                    <div class="order-body">
                                        @foreach ($order->items as $item)
                                            <div class="order-wrapper">
                                                <div class="order-item">
                                                    <div class="order-item-image">
                                                        <img src="{{ $item->product->imagePath }}" alt="">
                                                    </div>                                                      
                                                    <div class="order-item-name ml-1">
                                                        <label for="name">{{ $item->product->name }}</label>
                                                    </div>
                                                    <div class="order-item-price ">
                                                        <div class="flex">
                                                            <span>???</span>
                                                            <label  for="price">{{ $item->price }}</label>
                                                        </div>                                                            
                                                    </div>
                                                    <div class="order-item-quantity">
                                                        <div class="flex">
                                                            <span>Qty:</span>
                                                            <label  class="ml5"for="qty">{{ $item->qty }}</label>
                                                        </div>
                                                    </div>                                                       
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>                                    
                                </div>       
                            @endforeach
                        </div>                    
                    </div>
                    <div class="pagination-wrapper">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection