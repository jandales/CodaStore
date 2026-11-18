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
                    @if (session('error'))
                         <div class="alert alert-danger alert-bordered mt-1 mb-1" role="alert">{{ session('error') }}</div>
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
                                          <span>Order </span> <a href="{{ route('orders.details',[$order->encryptedId()])}}" for="order">{{ $order->order_number }}</a></div>
                                       <label class="order-status capitalize {{ in_array($order->status, ['cancelled', 'returned']) ? 'bg-danger' : '' }}" for="status">{{ $order->status === 'completed' ? 'delivered' : $order->status }}</label>
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
                                                         <label  for="price">@money($item->price)</label>
                                                   </div>
                                                   <div class="order-item-quantity">
                                                       <div class="flex">
                                                           <span>Qty:</span>
                                                           <label  class="ml5"for="qty">{{ $item->qty }}</label>
                                                       </div>
                                                    </div>
                                                    <div class="order-item-write-review">
                                                        <a href="{{ route('orders.review', [ $item->product->encryptedId() ])}}" class="link-primary">Write a review</a>
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
                                        <p>Order summary</p>
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
                                            <span>Shipping fee</span>
                                            <span>@money($order->shipping_charge)</span>
                                        </div>
                                        <div class="flex space-between">
                                            <span>Coupon</span>
                                            <span>@money($order->coupon_amount)</span>
                                        </div>
                                        @if ((float) $order->payment_fee > 0)
                                        <div class="flex space-between">
                                            <span>COD fee</span>
                                            <span>@money($order->payment_fee)</span>
                                        </div>
                                        @endif
                                        <div class="separator mt-1 mb-1"></div>
                                        <div class="flex space-between">
                                            <span>Total (VAT incl.)</span>
                                            <span>@money($order->gross_total)</span>
                                        </div>
                                        @if ($order->payment)
                                        <div class="flex space-between order-payment-line">
                                            <span>Payment</span>
                                            <span>{{ $order->payment->methodLabel() }}@if ($order->isCashOnDelivery()) · {{ $order->payment->statusLabel() }}@endif</span>
                                        </div>
                                        @endif

                                   </div>
                                </div>



                           </div>
                           @if ($order->tracking_number)
                                <div class="order-detail-wrapper order-tracking mt-2">
                                    <div class="order-title"><p>Shipment</p></div>
                                    <div class="separator mt-1 mb-1"></div>
                                    <div class="order-summary">
                                        <div class="flex space-between"><span>Courier</span><span>{{ $order->courierName() }}</span></div>
                                        <div class="flex space-between"><span>Tracking number</span><span>{{ $order->tracking_number }}</span></div>
                                        @if ($order->shipped_at)
                                            <div class="flex space-between"><span>Shipped</span><span>{{ $order->shipped_at->format('M j, Y') }}</span></div>
                                        @endif
                                    </div>
                                    @if ($order->trackingUrl())
                                        <a href="{{ $order->trackingUrl() }}" target="_blank" rel="noopener" class="btn btn-default mt-1">Track parcel</a>
                                    @endif
                                </div>
                           @endif
                           @if ($order->status === 'cancelled' && $order->payment)
                                <div class="review-note {{ $order->payment->status === 'refund_failed' ? '' : 'review-note--success' }} mt-2" role="status">
                                    <span>
                                        @if ($order->isCashOnDelivery())
                                            This order was cancelled. You haven't been charged.
                                        @elseif ($order->payment->status === 'refunded')
                                            Refunded @money($order->payment->amount) to your PayPal account.
                                        @elseif ($order->payment->status === 'refund_failed')
                                            We couldn't refund this payment automatically. Our team will refund you within 2 business days.
                                        @else
                                            This order was cancelled.
                                        @endif
                                    </span>
                                </div>
                           @endif
                           @php $return = $order->returnRequest; @endphp
                           @if ($return)
                                <div class="review-note {{ $return->status === 'rejected' ? '' : 'review-note--success' }} mt-2" role="status">
                                    <span>
                                        @if ($return->isOpen())
                                            Return requested on {{ $return->created_at->format('M j, Y') }}. We'll email you once it's reviewed.
                                        @elseif ($return->status === 'refunded')
                                            Return approved.
                                            @if (optional($order->payment)->status === 'refund_pending') We'll send your refund of @money($order->payment->amount) by bank transfer or GCash. Reply to the email we sent with the account to use.
                                            @elseif (optional($order->payment)->status === 'refunded' && $order->isCashOnDelivery()) Your refund of @money($order->payment->amount) has been sent.
                                            @elseif (optional($order->payment)->status === 'refunded') Refunded @money($order->payment->amount) to your PayPal account.
                                            @elseif (optional($order->payment)->status === 'refund_failed') Our team will refund you within 2 business days.
                                            @endif
                                        @else
                                            We couldn't accept this return.@if ($return->admin_note) {{ $return->admin_note }}@endif
                                        @endif
                                    </span>
                                </div>
                           @elseif ($order->canRequestReturn())
                                <details class="return-request mt-2" {{ $errors->hasAny(['reason', 'details']) ? 'open' : '' }}>
                                    <summary>Return this order</summary>
                                    <p class="return-request__help">You can ask for a return until {{ $order->returnWindowEndsAt()->format('F j, Y') }}. Once we approve it, we refund your PayPal payment in full.</p>
                                    <form method="post" action="{{ route('orders.return', [$order->encryptedId()]) }}">
                                        @csrf
                                        <fieldset>
                                            <legend>Why are you returning it?</legend>
                                            @foreach (\App\Models\OrderReturn::REASONS as $key => $label)
                                                <label class="check-line"><input type="radio" name="reason" value="{{ $key }}" {{ old('reason') === $key ? 'checked' : '' }} required> {{ $label }}</label>
                                            @endforeach
                                        </fieldset>
                                        @error('reason')<div class="alert alert-danger mt-1">{{ $message }}</div>@enderror
                                        <label for="return-details" class="mt-1">Anything else we should know? <span class="muted">(optional)</span></label>
                                        <textarea id="return-details" name="details" rows="3" maxlength="1000">{{ old('details') }}</textarea>
                                        @error('details')<div class="alert alert-danger mt-1">{{ $message }}</div>@enderror
                                        <button class="btn btn-dark mt-1">Request return</button>
                                    </form>
                                </details>
                           @endif
                           <div class="mt-2 order-actions">
                                @if ($order->payment)
                                    <a href="{{ route('orders.invoice', [$order->encryptedId()]) }}" class="btn btn-default" target="_blank" rel="noopener">View invoice</a>
                                @endif

                                @if($order->status == "confirmed")
                                    <form action="{{ route('orders.cancel',[$order->encryptedId()])}}" method="post">
                                        @csrf
                                        @method('Put')
                                        <button class="btn btn-danger right uppercase">Cancel Order</button>
                                    </form>
                                @endif


                           </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
@endsection