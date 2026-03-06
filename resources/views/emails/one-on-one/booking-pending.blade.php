<x-mail::message>
# Booking Request Submitted

Hi {{ $booking->guest_first_name }},

Your booking request has been submitted and is awaiting confirmation from **{{ $instructorName }}**.

## Meeting Details

**Date:** {{ $meetingDate }}
**Time:** {{ $meetingTime }}
**Duration:** {{ $duration }}
**Type:** {{ $meetingType }}

---

You will receive another email once your booking is confirmed. If you need to make any changes, you can manage your booking using the button below.

<x-mail::button :url="$manageUrl">
View Booking Details
</x-mail::button>

If you have any questions, please contact **{{ $studioName }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
