@extends('layout.admin')
@section('content')
    <div class="page-title order-page-title">
        <h1>Order {{ $order->order_number }}</h1>
        <div class="order-doc-actions">
            <a href="{{ route('admin.orders.invoice', [$order->encryptedId()]) }}" class="btn btn-light" target="_blank" rel="noopener"><i class="fas fa-file-invoice" aria-hidden="true"></i> Invoice</a>
            <a href="{{ route('admin.orders.packing-slip', [$order->encryptedId()]) }}" class="btn btn-light" target="_blank" rel="noopener"><i class="fas fa-box" aria-hidden="true"></i> Packing slip</a>
            <a href="{{ route('admin.orders') }}" class="btn btn-light">Back to orders</a>
        </div>
    </div>
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="flex flex-gap order-layout">
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
            <div class="panel w-12 mt20 order-notes" id="notes">
                <div class="panel-heading">
                    <h4>Notes and activity</h4>
                </div>
                <div class="panel-body">
                    <form method="post" action="{{ route('admin.orders.notes.store', [$order->encryptedId()]) }}" class="note-form">
                        @csrf
                        <label for="note-body" class="sr-only">Add a note</label>
                        <textarea id="note-body" name="body" rows="2" maxlength="2000" placeholder="Add a note for the team. Customers never see these." required>{{ old('body') }}</textarea>
                        @error('body')<div class="alert alert-danger">{{ $message }}</div>@enderror
                        <button class="btn btn-primary">Add note</button>
                    </form>

                    @if ($timeline->isEmpty())
                        <p class="timeline-empty">No notes or changes yet.</p>
                    @else
                        <ol class="timeline" role="list">
                            @foreach ($timeline as $entry)
                                @php $item = $entry['item']; @endphp
                                <li class="timeline__item timeline__item--{{ $entry['type'] }}">
                                    <span class="timeline__dot" aria-hidden="true"><i class="fas {{ $entry['type'] === 'note' ? 'fa-note-sticky' : 'fa-clock-rotate-left' }}"></i></span>
                                    <div class="timeline__body">
                                        <p class="timeline__meta">
                                            <strong>{{ $entry['type'] === 'note' ? $item->authorName() : $item->actorName() }}</strong>
                                            <time datetime="{{ $entry['at']->toIso8601String() }}">{{ $entry['at']->format('M j, Y g:i a') }}</time>
                                        </p>
                                        @if ($entry['type'] === 'note')
                                            <p class="timeline__note">{!! nl2br(e($item->body)) !!}</p>
                                            @if ($item->admin_id === auth('admin')->id() || auth('admin')->user()->is_admin())
                                                <form method="post" action="{{ route('admin.orders.notes.destroy', [$order->encryptedId(), $item->id]) }}" onsubmit="return confirm('Delete this note?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="link-button">Delete</button>
                                                </form>
                                            @endif
                                        @else
                                            <p>{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
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
                    <span>{{ $order->payment ? $order->payment->methodLabel() : '—' }}</span>
                </div>
                @if ($order->payment)
                <div class="row mt10">
                    <div>Payment</div>
                    <span class="status {{ $order->payment->statusColor() }}">{{ $order->payment->statusLabel() }}</span>
                </div>
                @endif
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
                @if ((float) $order->payment_fee > 0)
                <div class="row mt10">
                    <div>COD fee</div>
                    <span>@money($order->payment_fee)</span>
                </div>
                @endif
                <div class="row mt10">
                    <div>Coupon</div>
                    <span>@money($order->coupon_amount)</span>
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
                       @if ($order->payment && !$order->isCashOnDelivery() && in_array($order->payment->status, ['refunded', 'refund_failed']))
                            <div class="alert {{ $order->payment->status === 'refunded' ? 'alert-success' : 'alert-danger' }} mt-1">
                                {{ $order->payment->status === 'refunded' ? 'Payment refunded to the customer through PayPal.' : 'Automatic refund failed. Refund this payment manually in PayPal (capture ' . $order->payment->reference_number . ').' }}
                            </div>
                       @endif

                       @if ($order->isCashOnDelivery())
                            @php $codPayment = $order->payment; @endphp
                            <section class="cod-panel" aria-labelledby="cod-title">
                                <h4 id="cod-title"><i class="fas fa-money-bill-wave" aria-hidden="true"></i> Cash on delivery</h4>
                                @if ($codPayment->status === 'pending')
                                    <p class="cod-panel__due">Cash to collect: <strong>@money($codPayment->amount)</strong></p>
                                    <p class="ship-note">Marking the order as delivered records the cash as collected.</p>
                                    @if ($order->status === 'shipped')
                                        <form method="post" action="{{ route('admin.orders.cod.not-collected', [$order->encryptedId()]) }}" onsubmit="return confirm('Mark as not collected? The order becomes returned and the stock goes back.')">
                                            @csrf
                                            @method('put')
                                            <button class="btn btn-light">Not collected / returned to us</button>
                                        </form>
                                    @endif
                                @elseif ($codPayment->status === 'paid')
                                    <p>Collected @money($codPayment->amount){{ $codPayment->collected_at ? ' on ' . $codPayment->collected_at->format('M j, Y') : '' }}.</p>
                                @elseif ($codPayment->status === 'refund_pending')
                                    <p class="cod-panel__due">Refund to send: <strong>@money($codPayment->amount)</strong></p>
                                    <p class="ship-note">Send it to the customer by bank or GCash, then record it here.</p>
                                    @if (auth('admin')->user()->hasPermission('refunds'))
                                        <form method="post" action="{{ route('admin.orders.cod.refund-sent', [$order->encryptedId()]) }}">
                                            @csrf
                                            @method('put')
                                            <button class="btn btn-primary">Refund sent</button>
                                        </form>
                                    @endif
                                @elseif ($codPayment->status === 'refunded')
                                    <p>Refund of @money($codPayment->amount) sent{{ $codPayment->refunded_at ? ' on ' . $codPayment->refunded_at->format('M j, Y') : '' }}.</p>
                                @else
                                    <p>{{ $codPayment->statusLabel() }}.</p>
                                @endif
                            </section>
                       @endif

                       @php $return = $order->returnRequest; @endphp
                       @if ($return)
                            <section class="return-panel" aria-labelledby="return-title">
                                <h4 id="return-title">Return request <span class="status {{ $return->isOpen() ? 'bg-warning' : ($return->status === 'refunded' ? 'bg-success' : 'bg-danger') }}">{{ $return->isOpen() ? 'Waiting for you' : ($return->status === 'refunded' ? 'Approved' : 'Declined') }}</span></h4>
                                <p><span>Reason</span> {{ $return->reasonLabel() }}</p>
                                @if ($return->details)<p><span>Customer note</span> {{ $return->details }}</p>@endif
                                <p><span>Requested</span> {{ $return->created_at->format('M j, Y g:i a') }}</p>
                                @if ($return->status === 'refunded')
                                    <p><span>Refund</span> @money($return->refund_amount)</p>
                                    <p><span>Stock</span> {{ $return->restocked ? 'Put back into inventory' : 'Not restocked' }}</p>
                                @elseif ($return->status === 'rejected')
                                    <p><span>Your note</span> {{ $return->admin_note }}</p>
                                @endif

                                @if ($return->isOpen() && !auth('admin')->user()->hasPermission('refunds'))
                                    <p class="ship-note">An administrator or a staff member with the Refunds permission needs to decide this return.</p>
                                @elseif ($return->isOpen())
                                    <form method="post" action="{{ route('admin.orders.return.approve', [$order->encryptedId()]) }}" class="return-form">
                                        @csrf
                                        @method('put')
                                        <label class="check-line"><input type="checkbox" name="restock" value="1" checked> Put the items back into stock</label>
                                        <button class="btn btn-primary">Approve and refund @money(optional($order->payment)->amount ?? $order->gross_total)</button>
                                    </form>
                                    <details class="return-reject" {{ $errors->has('admin_note') ? 'open' : '' }}>
                                        <summary>Decline the return</summary>
                                        <form method="post" action="{{ route('admin.orders.return.reject', [$order->encryptedId()]) }}" class="return-form">
                                            @csrf
                                            @method('put')
                                            <label for="admin_note">Note to the customer</label>
                                            <textarea name="admin_note" id="admin_note" rows="3" maxlength="1000" required placeholder="For example: the item has been worn and can't be resold.">{{ old('admin_note') }}</textarea>
                                            @error('admin_note')<div class="alert alert-danger">{{ $message }}</div>@enderror
                                            <button class="btn btn-danger">Decline return</button>
                                        </form>
                                    </details>
                                @endif
                            </section>
                       @endif

                       @if (in_array($order->status, ['confirmed', 'shipped']))
                            <h4 class="mt-2">Update order status</h4>
                            @if ($errors->hasAny(['status', 'courier', 'tracking_number']))
                                <div class="alert alert-danger mt-1" role="alert">
                                    @foreach ($errors->only(['status', 'courier', 'tracking_number']) as $error)<div>{{ $error }}</div>@endforeach
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
                                            @if (auth('admin')->user()->hasPermission('refunds'))
                                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled (restores stock and refunds)</option>
                                            @endif
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
                                <p class="ship-note">The customer is emailed when the order ships or is cancelled.@if ($order->isCashOnDelivery() && $order->status === 'shipped') Marking it delivered records the cash as collected.@endif</p>
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