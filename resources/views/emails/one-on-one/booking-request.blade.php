<x-mail::message>
# New Booking Request

You have a new 1:1 meeting request that requires your approval.

## Guest Information

**Name:** {{ $guestName }}
**Email:** {{ $guestEmail }}
@if($guestPhone)
**Phone:** {{ $guestPhone }}
@endif
@if($guestNotes)
**Notes:** {{ $guestNotes }}
@endif

## Meeting Details

**Date:** {{ $meetingDate }}
**Time:** {{ $meetingTime }}
**Duration:** {{ $duration }}
**Type:** {{ $meetingType }}

---

Please review and accept or decline this booking request.

<x-mail::button :url="$dashboardUrl" color="primary">
View in Dashboard
</x-mail::button>

Or use the quick actions below:

<x-mail::table>
| Accept | Decline |
|:------:|:-------:|
| [Accept Booking]({{ $acceptUrl }}) | [Decline Booking]({{ $declineUrl }}) |
</x-mail::table>

Thanks,<br>
{{ $studioName }}
</x-mail::message>
