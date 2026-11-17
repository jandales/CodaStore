@component('mail::message')
# Your order has been cancelled

Hi {{ optional($order->shipping)->firstname ?: 'there' }}, order **{{ $order->order_number }}** was cancelled on {{ optional($order->cancelled_at)->format('F j, Y') }}.

@php $payment = $order->payment; @endphp
@if ($payment && $payment->status === \App\Models\OrderPayment::STATUS_REFUNDED)
We've refunded **₱{{ number_format((float) $payment->amount, 2) }}** to your PayPal account. Refunds usually appear within a few days, depending on your bank.
@elseif ($payment && $payment->status === \App\Models\OrderPayment::STATUS_REFUND_FAILED)
We couldn't refund your payment automatically. Our team has been notified and will refund **₱{{ number_format((float) $payment->amount, 2) }}** to you within 2 business days.
@else
You have not been charged for this order.
@endif

@include('emails.orders._items')

If you didn't ask to cancel this order, please reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
