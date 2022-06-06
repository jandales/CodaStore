@extends('layout.admin')

@section('content') 


    <div class="page-title">
        <h1>Dashbaord</h1>   
    </div>
    
    <div class="flex space-between m-t-2 gap30">
        <div class="card">
            <div class="card-body">
                <div class="title">
                    <label for="orders" class="title">Orders</label>
                </div>           
                <div class="flex space-between items-center mt5">                
                    <span class="number">{{ $orders->count() }}</span>
                    <span class="icon">
                        <i class="fas fa-box"> </i>                   
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <label for="view-more" class="link link-primary">View more</label>
            </div>
        </div>
    
        <div class="card">
            <div class="card-body">
                <div class="title">
                    <label for="orders" class="title">Completed</label>
                </div>           
                <div class="flex space-between items-center mt5">                
                    <span class="number">{{ $completed_count }}</span>
                    <span class="icon">
                        <i class="fas fa-box"> </i>                   
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <label for="view-more" class="link link-primary">View more</label>
            </div>
        </div>
    
        <div class="card">
            <div class="card-body">
                <div class="title">
                    <label for="orders" class="title">Customers</label>
                </div>           
                <div class="flex space-between items-center mt5">                
                    <span class="number">{{ $customer_count }}</span>
                    <span class="icon">
                        <i class="fas fa-users"> </i>                   
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <label for="view-more" class="link link-primary">View more</label>
            </div>
        </div>
    
        <div class="card">
            <div class="card-body">
                <div class="title">
                    <label for="orders" class="title">Cancelled</label>
                </div>           
                <div class="flex space-between items-center mt5">                
                    <span class="number">{{ $cancelled_count }}</span>
                    <span class="icon">
                        <i class="fas fa-box"></i>                   
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <label for="view-more" class="link link-primary">View more</label>
            </div>
        </div>
    
    
       
    </div>

    <br>
    <h4>Recent Order</h4>
    <br>
    <div class="panel-table">       
        <table class="table">
            <thead class="bg-default">
                <tr>                   
                    <th class="py-30">Order Number</th>                                                       
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>                  
                </tr>
            </thead>
            <tbody>

                @forEach($orders as $order)
                    <tr>  
                        <td class="py-30">
                            <a class="link link-primary" href="{{ route('admin.orders.show',[$order->encryptedId()])}}">
                                <div class="flex items-center gap10">
                                    <div class="avatar-sm">
                                        <img src="{{ $order->user->imagePath }}" alt="" srcset="">
                                    </div>
                                    <span>{{ $order->user->name }}</span>
                                 </div>
                            </a>
                        </td>                       
                        <td>{{ $order->totalItems() }}</td>
                        <td>@money($order->total())</td>
                        <td>{{ $order->created_at->format('M-d-Y')}}</td>
                        <td><span class="status capitalized {{ $order->statusColor() }}">{{ $order->status }}</span></td>                       
                    </tr>
                @endforeach  
              
            </tbody>
        </table>
    </div>








@endsection