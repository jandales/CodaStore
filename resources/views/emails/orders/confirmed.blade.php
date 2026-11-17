@component('mail::message')
# Thank you for your order

Hi {{ optional($order->shipping)->firstname ?: 'there' }}, we've received your payment and your order is confirmed. We'll email you again when it ships.

**Order number:** {{ $order->order_number }}<br>
**Order date:** {{ $order->created_at->format('F j, Y') }}

@include('emails.orders._items')

**Shipping to**<br>
{{ optional($order->shipping)->personName() }}<br>
{{ optional($order->shipping)->address() }}<br>
{{ optional($order->shipping)->phone }}

@component('mail::button', ['url' => route('orders.details', [$order->encryptedId()])])
View your order
@endcomponent

You can cancel from your account until the order ships.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
