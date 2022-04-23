@extends('layout.admin')
@section('content') 
    <div class="page-title">
        <h1>Orders</h1>
        <a href="{{ url()->previous() }}" class="btn btn-danger">back</a>
    </div>

    <div class="flex flex-gap">
        <div class="m-t-2 w-9">
            <div class="panel w-12">
                <div class="panel-heading">
                    <h4>Shipping Address</h4>
                </div>
                <div class="panel-body">
                    {{ $order->shipping->address() }}
                </div>
            </div>
            <div class="panel w-12 mt20">
                <div class="panel-heading">
                    <h4>Order Details</h4>
                </div>
                <div class="panel-body">
                     <table class="table table-orders">
                        <thead>
                            <tr>                              
                                <th>Items</th>   
                                <th>Price</th>                                       
                                <th>Quantity</th>                           
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                    <tr class="vertical-top">                           
                                        <td>
                                            <div class="prod-desc-wrapper flex flex-start">
                                                <div class="image-70">
                                                    <img class="img" src="/{{ $item->product->imagePath }}" alt="">                                        
                                                </div>
                                                <div class="prod-description ml10">
                                                    <label for="name">{{ $item->product->name }}</label>                                          
                                                     @if ($item->properties != null)
                                                        <ul class="prod-variant mt5">
                                                            @foreach ($item->properties as $index =>  $variant)  
                                                                    <li><p class="capitalized">{{ $variant['name'] }}: {{ $variant['value'] }} @if ($index != count($item->properties) -1) , @endif</p></li> 
                                                            @endforeach
                                                        </ul> 
                                                    @endif
                                                </div>
                                               
                                            </div>
                                            
                                        </td>
        
                                      
                                        <td>
                                            <p>@money($item->price)</p>
                                        </td>
                                        <td class="text-centered">
                                            <p>{{ $item->qty }}</p>
                                        </td>
                                        <td>
                                            <p>@money($item->total())</p>
                                        </td>
                                    </tr>
                             @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="panel m-t-2 w-3">
            <div class="panel-body">
                <div class="row items-center mt10">
                    <div><h4>Status</h4></div>
                    <span class="status capitalized {{$order->statusColor() }}">{{ $order->status }}</span>
                </div>

                <div class="row items-center mt10">
                    <h4>Order Number</h4>
                    <span class="link-primary">{{ $order->orderNumber() }}</span>
                </div>
               
                <br>
                <div class="separator"></div>      
            </div>

            <div class="panel-heading">
                <h4>Billing Details</h4>
            </div>
            <div class="panel-body">
                <div class="row mt10">
                    <div>Payment Method</div>                  
                    <span>Card</span>  
                </div>
                <div class="row mt10">
                    <div>Name</div>                    
                    <span>{{ $order->billing->personName() }}</span>
                </div>
                <div class="row mt10">
                    <div>Email</div>                    
                    <span>{{ $order->billing->email}}</span>
                </div>
                <div class="row mt10">
                    <div>Contact</div>                    
                    <span>{{ $order->billing->phone}}</span>
                </div>
                <br>
                <div class="separator"></div>      
            </div>

           

            <div class="panel-heading">
                <h4>Summary</h4>
            </div>
            <div class="panel-body">
                <div class="row mt10">
                    <div>Subtotal <span>{{ "(".$order->totalItems() . " items)"}}</span></div>
                    <span>@money($order->subtotal())</span>
                </div>
                <div class="row mt10">
                    <div>Shipping Fee</div>
                    <span>@money($order->shipping_charge)</span>
                </div>
                <div class="row mt10">
                    <div>Tax</div>
                    <span>@money($order->tax_total)</span>
                </div>
                <div class="row mt10">
                    <div>Coupon <span> @if($order->coiponUsed)({{ $order->couponUsed->coupon->name }}) @endif</span></div>
                    <span>@if($order->coiponUsed) @money($order->couponUsed->amount) @else @money(0) @endif<span>
                </div> 
                <br>
                <div class="separator"></div>    
                <div class="row mt10">
                    <div>Total</div>
                    <span>@money($order->gross_total)</span>
                </div>             
            </div>
                <div class="panel-body">
                   <div class="flex flex-end mt-2">
                       @if(!$order->isDelivered())                       
                            <form action="{{ route('admin.orders.shipped',[$order])}}" method="post">
                                @csrf
                                @method('put')
                                <button class="btn btn-primary">Ship</button>
                            </form>
                       @endif                                              
                   </div>                   
                </div>
        </div>
      
    </div>

 
   
  
@endsection