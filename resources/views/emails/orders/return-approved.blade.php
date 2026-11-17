@component('mail::message')
# Your return is approved

Hi {{ optional($order->shipping)->firstname ?: 'there' }}, we've approved the return for order **{{ $order->order_number }}**.

@php $payment = $order->payment; @endphp
@if ($payment && $payment->status === \App\Models\OrderPayment::STATUS_REFUNDED)
We've refunded **₱{{ number_format((float) $payment->amount, 2) }}** to your PayPal account. Refunds usually appear within a few days, depending on your bank.
@elseif ($payment && $payment->status === \App\Models\OrderPayment::STATUS_REFUND_FAILED)
We couldn't send the refund automatically. Our team has been notified and will refund **₱{{ number_format((float) $payment->amount, 2) }}** to you within 2 business days.
@endif

@include('emails.orders._items')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
