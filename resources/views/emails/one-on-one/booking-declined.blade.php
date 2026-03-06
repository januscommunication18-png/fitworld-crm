<x-mail::message>
# Booking Request Declined

Hi {{ $booking->guest_first_name }},

Unfortunately, your booking request with **{{ $instructorName }}** could not be accepted.

## Original Request

**Date:** {{ $meetingDate }}
**Time:** {{ $meetingTime }}
**Duration:** {{ $duration }}
**Type:** {{ $meetingType }}

@if($declineReason)
**Reason:** {{ $declineReason }}
@endif

---

We apologize for any inconvenience. You can try booking a different time slot using the button below.

<x-mail::button :url="$bookingUrl">
Book Another Time
</x-mail::button>

If you have any questions, please contact **{{ $studioName }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
