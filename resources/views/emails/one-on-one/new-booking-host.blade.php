<x-mail::message>
# New 1:1 Meeting Booked

Hi,

You have a new meeting booked with **{{ $guestName }}**.

## Meeting Details

**Date:** {{ $meetingDate }}

**Time:** {{ $meetingTime }}

**Duration:** {{ $duration }}

**Type:** {{ $meetingType }}

---

## Guest Information

**Name:** {{ $guestName }}

**Email:** {{ $guestEmail }}

@if($guestPhone)
**Phone:** {{ $guestPhone }}
@endif

@if($guestNotes)
**Notes from Guest:**
> {{ $guestNotes }}
@endif

---

<x-mail::button :url="$dashboardUrl">
View Booking Details
</x-mail::button>

Thanks,<br>
{{ $studioName }}
</x-mail::message>
