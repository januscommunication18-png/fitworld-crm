<x-mail::message>
# Booking Confirmed!

Hi {{ $client->first_name }},

Your booking at **{{ $studioName }}** has been confirmed.

## Booking Details

**Class:** {{ $className }}

**Date:** {{ $sessionDate }}

**Time:** {{ $sessionTime }}

@if($instructorName)
**Instructor:** {{ $instructorName }}
@endif

@if($locationName)
**Location:** {{ $locationName }}
@endif

---

@if($hasQuestionnaires)
## Please Complete Your Intake Form(s)

Before your session, please take a moment to complete the following form(s):

@foreach($questionnaireResponses as $response)
<x-mail::button :url="$response->getResponseUrl()">
{{ $response->version->questionnaire->name ?? 'Intake Form' }}
</x-mail::button>
@endforeach

Completing these forms helps us provide you with the best possible experience.

---
@endif

We look forward to seeing you!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
