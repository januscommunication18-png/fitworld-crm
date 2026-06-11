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

@if(!empty($qrImageSrc))
## Your Check-In QR Code

Show this code at the studio to check in. It's your personal code — it works for all your bookings, services, and memberships.

<p style="text-align:center; margin:16px 0;">
<img src="{{ $qrImageSrc }}" alt="Your check-in QR code" width="220" height="220" style="border:1px solid #e5e7eb; border-radius:12px; padding:8px; background:#ffffff;">
</p>

@if(!empty($qrDownloadUrl))
<x-mail::button :url="$qrDownloadUrl . '?dl=1'">
Download QR Code
</x-mail::button>
@endif

The QR code is also attached to this email.

---
@endif

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
