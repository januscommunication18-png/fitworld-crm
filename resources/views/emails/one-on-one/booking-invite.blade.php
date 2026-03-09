<x-mail::message>
# You're Invited to Book a 1:1 Meeting

Hi{{ $clientName ? ' ' . $clientName : '' }},

You've been invited to schedule a 1:1 meeting with **{{ $instructorName }}**@if($instructorTitle) ({{ $instructorTitle }})@endif at **{{ $studioName }}**.

@if($scheduledAt)
<x-mail::panel>
**Suggested Time:**
{{ $scheduledAt->format('l, F j, Y') }} at {{ $scheduledAt->format('g:i A') }}
@if($duration)
({{ $duration }} minutes)
@endif
</x-mail::panel>
@elseif($duration)
**Meeting Duration:** {{ $duration }} minutes
@endif

@if($instructorBio)
**About {{ $instructorName }}:**
{{ $instructorBio }}
@endif

---

@if($scheduledAt)
Click the button below to confirm this time or choose a different slot.
@else
Click the button below to view available times and book your session.
@endif

<x-mail::button :url="$bookingUrl">
Book Your Meeting
</x-mail::button>

If you have any questions, please contact **{{ $studioName }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
