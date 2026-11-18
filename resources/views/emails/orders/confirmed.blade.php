@component('mail::message')
# Thank you for your order

@if (optional($order->payment)->status === 'awaiting')
Hi {{ optional($order->shipping)->firstname ?: 'there' }}, your order is confirmed. The total is **₱{{ number_format((float) $order->gross_total, 2) }}**. We'll ship it once your payment is received.
@elseif (optional($order->payment)->provider === 'manual')
Hi {{ optional($order->shipping)->firstname ?: 'there' }}, thank you, we've received your payment ({{ $order->payment->methodLabel() }}) and your order is confirmed. We'll email you again when it ships.
@elseif ($order->isCashOnDelivery())
Hi {{ optional($order->shipping)->firstname ?: 'there' }}, your order is confirmed. You chose **cash on delivery**: please have **₱{{ number_format((float) $order->gross_total, 2) }}** ready when it arrives. We'll email you again when it ships.
@else
Hi {{ optional($order->shipping)->firstname ?: 'there' }}, we've received your payment and your order is confirmed. We'll email you again when it ships.
@endif

**Order number:** {{ $order->order_number }}<br>
**Order date:** {{ $order->created_at->format('F j, Y') }}

@include('emails.orders._items')

**Shipping to**<br>
{{ optional($order->shipping)->personName() }}<br>
{{ optional($order->shipping)->address() }}<br>
{{ optional($order->shipping)->phone }}

@if ($order->user_id)
@component('mail::button', ['url' => route('orders.details', [$order->encryptedId()])])
View your order
@endcomponent
@endif

You can cancel from your account until the order ships.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
