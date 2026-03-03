<x-mail::message>
# We Miss You!

Hi {{ $client->first_name }},

It's been a while since we've seen you at **{{ $studioName }}**@if($lastVisit) — your last visit was on {{ $lastVisit }}@endif.

We wanted to reach out and let you know that we'd love to see you back!

## Here's What You've Been Missing

Our classes are as energizing as ever, and our instructors are ready to help you get back on track with your fitness goals.

Remember, every workout counts, and it's never too late to pick up where you left off.

@if($bookingUrl)
<x-mail::button :url="$bookingUrl">
Book a Class
</x-mail::button>
@endif

---

Need to talk about your schedule or have questions? We're always here to help.

@if($studioEmail)
**Email:** {{ $studioEmail }}
@endif

We hope to see you soon!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
