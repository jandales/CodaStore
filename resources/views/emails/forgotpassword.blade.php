@component('mail::message')

CodaStore password reset
 
We heard that you lost your  password. Sorry about that!

But don’t worry! You can use the following button to reset your password:

@component('mail::button', ['url' => $token ])
Reset your password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
