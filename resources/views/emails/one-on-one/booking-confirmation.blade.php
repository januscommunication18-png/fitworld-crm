<x-mail::message>
# Meeting Confirmed!

Hi {{ $booking->guest_first_name }},

Your meeting with **{{ $hostName }}** has been confirmed.

## Meeting Details

**Date:** {{ $meetingDate }}

**Time:** {{ $meetingTime }}

**Duration:** {{ $duration }}

**Type:** {{ $meetingType }}

@if($booking->meeting_type === 'in_person' && $profile->in_person_location)
**Location:** {{ $profile->in_person_location }}
@elseif($booking->meeting_type === 'video' && $profile->video_link)
**Video Link:** {{ $profile->video_link }}
@elseif($booking->meeting_type === 'phone' && $profile->phone_number)
**Phone:** {{ $profile->phone_number }}
@endif

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
