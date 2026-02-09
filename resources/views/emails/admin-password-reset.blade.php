<x-mail::message>
# Password Reset

Hi {{ $firstName }},

Your FitCRM Admin password has been reset. Here is your new temporary password:

<x-mail::panel>
<div style="text-align: center; font-family: monospace; font-size: 18px; font-weight: bold;">
{{ $password }}
</div>
</x-mail::panel>

**Important:** You will be required to change this password when you log in.

<x-mail::button :url="$loginUrl">
Log In Now
</x-mail::button>

If you didn't request a password reset, please contact support immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
