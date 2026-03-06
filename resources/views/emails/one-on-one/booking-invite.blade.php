<x-mail::message>
# You're Invited to Book a 1:1 Meeting

Hi there,

You've been invited to schedule a 1:1 meeting with **{{ $instructorName }}**@if($instructorTitle) ({{ $instructorTitle }})@endif at **{{ $studioName }}**.

@if($instructorBio)
**About {{ $instructorName }}:**
{{ $instructorBio }}
@endif

---

Click the button below to view available times and book your session.

<x-mail::button :url="$bookingUrl">
Book Your Meeting
</x-mail::button>

If you have any questions, please contact **{{ $studioName }}**.

Thanks,<br>
{{ $studioName }}
</x-mail::message>
