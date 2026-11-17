@component('mail::message')
# Your order is on its way

Hi {{ optional($order->shipping)->firstname ?: 'there' }}, good news: order **{{ $order->order_number }}** has shipped.

@component('mail::panel')
**Courier:** {{ $order->courierName() ?? 'Courier' }}<br>
**Tracking number:** {{ $order->tracking_number ?? 'Not available yet' }}
@endcomponent

@if ($order->trackingUrl())
@component('mail::button', ['url' => $order->trackingUrl()])
Track your parcel
@endcomponent
@else
Use the tracking number on {{ $order->courierName() ?? 'the courier' }}'s website or app to follow your parcel.
@endif

@include('emails.orders._items')

**Shipping to**<br>
{{ optional($order->shipping)->personName() }}<br>
{{ optional($order->shipping)->address() }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
