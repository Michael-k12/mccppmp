@component('mail::message')
# Your Login OTP

Your one-time password (OTP) is:

@component('mail::panel')
{{ $otp }}
@endcomponent

It is valid for **5 minutes**. Do not share it with anyone.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
