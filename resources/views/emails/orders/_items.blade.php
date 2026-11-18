@component('mail::table')
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ ucfirst(optional($item->product)->name ?? 'Item') }}@if (!empty($item->properties)) ({{ collect($item->properties)->map(fn ($value, $key) => ucfirst($key) . ': ' . $value)->implode(', ') }})@endif | {{ $item->qty }} | ₱{{ number_format($item->price * $item->qty, 2) }} |
@endforeach
| **Shipping** | | ₱{{ number_format($order->shipping_charge, 2) }} |
@if ($order->coupon_amount > 0)
| **Discount** | | −₱{{ number_format($order->coupon_amount, 2) }} |
@endif
@if ((float) $order->payment_fee > 0)
| **COD fee** | | ₱{{ number_format($order->payment_fee, 2) }} |
@endif
| **Total** | | **₱{{ number_format($order->gross_total, 2) }}** |
@endcomponent
