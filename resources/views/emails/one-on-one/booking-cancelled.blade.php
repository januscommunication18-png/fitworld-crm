<x-mail::message>
# Meeting Cancelled

Hi {{ $booking->guest_first_name }},

Your meeting with **{{ $hostName }}** has been cancelled by {{ $cancelledBy }}.

## Original Meeting Details

**Date:** {{ $meetingDate }}

**Time:** {{ $meetingTime }}

---

## Want to Book Again?

If you'd like to schedule another meeting, you can do so using the link below:

<x-mail::button :url="$rebookUrl">
Book a New Meeting
</x-mail::button>

---

If you have any questions, please contact us at **{{ $supportEmail ?? 'support' }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
