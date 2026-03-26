<x-mail::message>
# FitNearYou Verification Code

Hi {{ $firstName }},

Your verification code to view your API Secret is:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px;">
{{ $code }}
</div>
</x-mail::panel>

This code will expire in **10 minutes**.

If you didn't request this code, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
