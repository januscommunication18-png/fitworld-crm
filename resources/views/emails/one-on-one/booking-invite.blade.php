<x-mail::message>
# You're Invited to Book a 1:1 Meeting

Hi{{ $clientName ? ' ' . $clientName : '' }},

You've been invited to schedule a 1:1 meeting with **{{ $instructorName }}**@if($instructorTitle) ({{ $instructorTitle }})@endif at **{{ $studioName }}**.

@if(!empty($formattedSlots))
<x-mail::panel>
**Suggested Time{{ count($formattedSlots) > 1 || count($formattedSlots[0]['times'] ?? []) > 1 ? 's' : '' }}:**

@foreach($formattedSlots as $slot)
**{{ $slot['date'] }}**
@foreach($slot['times'] as $time)
- {{ $time }}
@endforeach

@endforeach
@if($duration)
({{ $duration }} minute sessions)
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

@if(!empty($formattedSlots))
Click the button below to confirm one of these times or choose a different slot.
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
