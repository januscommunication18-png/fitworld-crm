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

## Add to Your Calendar

<x-mail::button :url="$googleCalendarUrl" color="primary">
Add to Google Calendar
</x-mail::button>

<x-mail::table>
| Calendar | |
|:---------|:--|
| [Add to Outlook]({{ $outlookCalendarUrl }}) | [Add to Yahoo]({{ $yahooCalendarUrl }}) |
| [Download .ics file]({{ $icsUrl }}) | |
</x-mail::table>

---

## Need to Make Changes?

You can reschedule or cancel your meeting using the link below:

<x-mail::button :url="$manageUrl" color="secondary">
Manage My Booking
</x-mail::button>

---

We look forward to speaking with you!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
