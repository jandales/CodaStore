@component('mail::message')
# Thanks for joining

You'll hear from us when new pieces arrive and when something goes on sale. No more than a few emails a month.

@component('mail::button', ['url' => route('shop')])
Shop new arrivals
@endcomponent

Didn't sign up, or changed your mind? [Unsubscribe]({{ $subscriber->unsubscribeUrl() }}).

{{ config('app.name') }}
@endcomponent
