<x-mail::message>
# Meeting Rescheduled

Hi {{ $newBooking->guest_first_name }},

Your meeting with **{{ $hostName }}** has been rescheduled.

## New Meeting Details

**Date:** {{ $newDate }}

**Time:** {{ $newTime }}

---

## Previous Meeting (Cancelled)

**Date:** {{ $oldDate }}

**Time:** {{ $oldTime }}

---

## Need to Make Changes?

You can reschedule or cancel your meeting using the link below:

<x-mail::button :url="$manageUrl">
Manage My Booking
</x-mail::button>

---

We look forward to speaking with you!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
