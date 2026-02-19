<x-mail::message>
# Reset Your Password

Hi {{ $client->first_name }},

You requested to reset your password for your {{ $studioName }} member portal.

Click the button below to create a new password:

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

This link will expire in **{{ $expiryMinutes }} minutes**.

If you didn't request a password reset, you can safely ignore this email. Your password will not be changed.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
