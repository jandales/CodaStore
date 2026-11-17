@component('mail::message')
# Return requested

{{ optional($order->billing)->personName() ?: optional($order->user)->name }} asked to return order **{{ $order->order_number }}** (₱{{ number_format((float) $order->gross_total, 2) }}).

**Reason:** {{ $return->reasonLabel() }}
@if ($return->details)

> {{ $return->details }}
@endif

@include('emails.orders._items')

@component('mail::button', ['url' => route('admin.orders.show', [$order->encryptedId()])])
Review the return
@endcomponent

{{ config('app.name') }}
@endcomponent
