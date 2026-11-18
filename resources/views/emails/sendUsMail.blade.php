@component('mail::message')
# New message from the contact page

**From:** {{ $senderEmail }}

{{ $content }}

Reply to this email to answer them.
@endcomponent
