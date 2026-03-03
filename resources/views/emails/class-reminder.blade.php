<x-mail::message>
# Class Reminder

Hi {{ $client->first_name }},

This is a friendly reminder that you have a class coming up tomorrow at **{{ $studioName }}**.

## Class Details

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

**Need to cancel?** Please let us know as soon as possible if you can't make it so we can open up your spot for someone else.

We look forward to seeing you!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
