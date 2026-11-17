@component('mail::message')
# About your return request

Hi {{ optional($order->shipping)->firstname ?: 'there' }}, we've reviewed your return request for order **{{ $order->order_number }}** and can't accept it.

@if ($return && $return->admin_note)
> {{ $return->admin_note }}
@endif

If you have questions, just reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
