<x-mail::message>
# You've Been Granted 1:1 Booking Access!

Hi {{ $instructor->name }},

Great news! You've been granted access to offer 1:1 booking appointments at **{{ $studioName }}**.

## What This Means

Clients can now book private sessions directly with you through your personal booking page. You'll receive notifications when new meetings are booked.

@if(!$hasUserAccount)
## Create Your Account First

To get started, you'll need to create an account. Click the button below to set up your login credentials and configure your booking profile:

<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>

This link will expire in 7 days. After creating your account, you can set up your availability and meeting preferences.
@else
## Set Up Your Booking Profile

To start accepting bookings, please set up your availability and preferences:

<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>

Once your profile is complete, clients will see a "Book 1:1 Meeting" button on your instructor profile.
@endif

---

If you have any questions, please contact us at **{{ $supportEmail ?? 'support' }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
