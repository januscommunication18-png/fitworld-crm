<x-mail::message>
# You're Invited!

Hi there,

**{{ $inviterName }}** has invited you to join **{{ $studioName }}** as a **{{ $role }}**.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

This invitation will expire on **{{ $expiresAt }}**.

If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
