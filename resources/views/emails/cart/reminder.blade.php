@component('mail::message')
# Still thinking it over?

Hi {{ $user->name ?: 'there' }}, you left {{ $items->count() === 1 ? 'this' : 'these' }} in your bag. We've kept {{ $items->count() === 1 ? 'it' : 'them' }} for you, but stock is limited.

@component('mail::table')
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($items as $item)
| {{ ucwords($item->product->name) }}@if (!empty($item->attributes)) ({{ collect($item->attributes)->implode(', ') }})@endif | {{ $item->qty }} | ₱{{ number_format($item->product->effectivePrice() * $item->qty, 2) }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => $bagUrl])
Return to your bag
@endcomponent

Prices and availability are confirmed at checkout.

<small>Don't want these reminders? [Turn them off]({{ $optOutUrl }}).</small>

{{ config('app.name') }}
@endcomponent
