@extends('layout.front.app')

@section('content')
    

    
        <div class="container">

            <div class="flex account mt-3 mb-3">          
                @include('layout.front.sidebar')

               

                <div class="card no-border pad-2 bg-grey  w-1000  ml-2">
                    <div class="card-heading">
                        <h2>Orders</h2>
                    </div>

                    <div class="orders-wrapper">

                                <ul class="mt-2 mb-2">
                                    <li><a href="#">All</a></li>
                                    <li><a href="#">To Pay</a></li>
                                    <li><a href="#">To Ship</a></li>
                                    <li><a href="#">To Recieve</a></li>
                                </ul>
                    
                            <div class="form-inline mb-2">
                                <label for="">Status</label>
                                <select>
                                    <option>Cancel</option>
                                    <option>Delivered</option>
                                    <option>Return</option>
                                </select>
                            </div>

                            <div class="table-responsive">
                                <table class="table bg-white">
                                    <thead>
                                        <tr>
                                            <th>Order#</th>
                                            <th>Place On</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                        <tr>
                                            <td><a href="{{ route('orders.details',[$order])}}" class="link-primary"><span>{{ $order->ordernumber() }}</span></a></td>
                                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                            <td class="order-images">
                                                <div class="flex wrap">                                                   
                                                    @foreach ($order->orderProducts as $orderitem)
                                                        <div class="order-item-image m-l-5 mb">
                                                            <img src="/{{ $orderitem->product->imagePath }}" alt="">
                                                        </div>
                                                    @endforeach
                                                </div>
                                               
                                            </td>
                                            <td>₱ {{  number_format($order->total(),2) }}</td>
                                            <td>{{ $order->status }}</td>
                                            <td> <a href="{{ route('orders.details',[$order])}}" class="link-primary">Manage</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    
   @endsection