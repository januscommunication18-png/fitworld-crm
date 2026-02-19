<x-mail::message>
# Your Verification Code

Hi {{ $client->first_name }},

You requested to sign in to your {{ $studioName }} member portal. Use the code below to verify your identity:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px;">
{{ $code }}
</div>
</x-mail::panel>

This code will expire in **{{ $expiryMinutes }} minutes**.

If you didn't request this code, you can safely ignore this email.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
