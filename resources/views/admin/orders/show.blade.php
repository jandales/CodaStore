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
                                                    <img class="img" src="{{ $item->product->imagePath }}" alt="">                                        
                                                </div>
                                                <div class="prod-description ml10">
                                                    <label for="name">{{ $item->product->name }}</label>                                          
                                                     @if ($item->properties != null)
                                                        <ul class="prod-variant mt5">
                                                            @foreach ($item->properties as $key =>  $variant) 
                                                                    <li><p class="capitalized">{{ $key }} : {{ $variant}}</p></li> 
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
                    <span class="link-primary">{{ $order->order_number }}</span>
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
                   {{-- <div class="flex flex-end mt-2"> --}}
                       {{-- @if(!$order->isDelivered() && !$order->isToRecieved())                       
                            <form action="{{ route('admin.orders.shipped',[ $order->encryptedId() ])}}" method="post">
                                @csrf
                                @method('put')
                                <button class="btn btn-primary">Ship</button>
                            </form>
                       @endif   --}}
                       @if ($order->tracking_number)
                            <div class="ship-info">
                                <h4>Shipment</h4>
                                <p><span>Courier</span> {{ $order->courierName() }}</p>
                                <p><span>Tracking number</span> {{ $order->tracking_number }}</p>
                                @if ($order->trackingUrl())
                                    <p><a href="{{ $order->trackingUrl() }}" target="_blank" rel="noopener" class="dash-link">Track parcel</a></p>
                                @endif
                            </div>
                       @endif
                       @if ($order->payment && in_array($order->payment->status, ['refunded', 'refund_failed']))
                            <div class="alert {{ $order->payment->status === 'refunded' ? 'alert-success' : 'alert-danger' }} mt-1">
                                {{ $order->payment->status === 'refunded' ? 'Payment refunded to the customer through PayPal.' : 'Automatic refund failed. Refund this payment manually in PayPal (capture ' . $order->payment->reference_number . ').' }}
                            </div>
                       @endif

                       @if (in_array($order->status, ['confirmed', 'shipped']))
                            <h4 class="mt-2">Update order status</h4>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-1" role="alert">
                                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                                </div>
                            @endif
                            <form class="w-12 mt-1 order-status-form" action="{{ route('admin.orders.shipped1', [$order->encryptedId()]) }}" method="post">
                                @csrf
                                @method('put')
                                <div class="form-block">
                                    <label for="order-status">New status</label>
                                    <select name="status" id="order-status">
                                        @if ($order->status == 'confirmed')
                                            <option value="shipped" {{ old('status', 'shipped') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled (restores stock and refunds)</option>
                                        @endif
                                        @if ($order->status == 'shipped')
                                            <option value="completed">Delivered</option>
                                        @endif
                                    </select>
                                </div>
                                @if ($order->status == 'confirmed')
                                    <div class="ship-fields">
                                        <div class="form-block">
                                            <label for="courier">Courier</label>
                                            <select name="courier" id="courier">
                                                <option value="">Choose a courier</option>
                                                @foreach (config('couriers') as $key => $courier)
                                                    <option value="{{ $key }}" {{ old('courier') == $key ? 'selected' : '' }}>{{ $courier['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-block">
                                            <label for="tracking_number">Tracking number</label>
                                            <input type="text" name="tracking_number" id="tracking_number" maxlength="64" value="{{ old('tracking_number') }}" placeholder="From the courier receipt">
                                        </div>
                                    </div>
                                @endif
                                <button class="btn btn-primary">Update status</button>
                                <p class="ship-note">The customer is emailed when the order ships or is cancelled.</p>
                            </form>
                            <script>
                                (function () {
                                    var status = document.getElementById('order-status');
                                    var fields = document.querySelector('.ship-fields');
                                    if (!status || !fields) return;
                                    function toggle() { fields.style.display = status.value === 'shipped' ? '' : 'none'; }
                                    status.addEventListener('change', toggle);
                                    toggle();
                                })();
                            </script>
                       @endif
                                                           
                   {{-- </div>                    --}}
                </div>
        </div>
      
    </div>

 
   
  
@endsection