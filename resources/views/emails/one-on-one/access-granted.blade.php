<x-mail::message>
# You've Been Granted 1:1 Booking Access!

Hi {{ $instructor->name }},

Great news! You've been granted access to offer 1:1 booking appointments at **{{ $studioName }}**.

## What This Means

Clients can now book private sessions directly with you through your personal booking page. You'll receive notifications when new meetings are booked.

## Set Up Your Booking Profile

To start accepting bookings, please set up your availability and preferences:

<x-mail::button :url="$setupUrl">
Set Up My Booking Profile
</x-mail::button>

Once your profile is complete, clients will see a "Book 1:1 Meeting" button on your instructor profile.

---

If you have any questions, please contact us at **{{ $supportEmail ?? 'support' }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
