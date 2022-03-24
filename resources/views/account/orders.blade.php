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
                            <li class="{{(request()->is('account/orders/confirm')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['confirm'])}}">To Ship</a>
                            </li>
                            <li class="{{(request()->is('account/orders/shipped')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['shipped'])}}">To Recieve</a>
                            </li>
                            <li class="{{(request()->is('account/orders/delivered')) ? 'active' : ''}}" >
                                <a href="{{ route('account.orders',['delivered'])}}">Delivered</a>
                            </li>
                            <li class="{{(request()->is('account/orders/cancelled')) ? 'active' : ''}}">
                                <a  href="{{ route('account.orders',['cancelled'])}}">Cancelled</a>
                            </li>
                        </ul> 

                    <div class="orders">
                        @foreach ($orders as $order)
                            <div class="order">
                                <div class="order-header">
                                    <div class="align-items-content-center">
                                        <span>OR#:</span><a href="{{ route('orders.details',[$order])}}" for="order">{{ $order->ordernumber() }}</a></div>
                                        <label class="capitalize order-status" for="status">{{$order->status }}</label>
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
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection