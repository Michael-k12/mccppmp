@component('mail::message')
# Your OTP Code

Your One-Time Password for login is:

@component('mail::panel')
{{ $otp }}
@endcomponent

This OTP is valid for **5 minutes**. Do not share it with anyone.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
