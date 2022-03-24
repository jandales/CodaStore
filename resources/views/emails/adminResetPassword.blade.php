@component('mail::message')
# Introduction

The body of your message.
{{ $url }}
@component('mail::button', ['url' =>  $url ])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
