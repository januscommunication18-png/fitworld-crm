<x-mail::message>
# Welcome to {{ $studioName }}!

Hi {{ $client->first_name }},

We're thrilled to have you join us at **{{ $studioName }}**!

Whether you're looking to build strength, improve flexibility, or just feel your best, we're here to support you on your journey.

## Getting Started

Here are a few things you can do to get started:

1. **Book your first class** - Check out our schedule and find a class that works for you
2. **Meet our instructors** - Our experienced team is here to guide and motivate you
3. **Explore our facilities** - Take a tour and see everything we have to offer

@if($bookingUrl)
<x-mail::button :url="$bookingUrl">
Book Your First Class
</x-mail::button>
@endif

---

If you have any questions, don't hesitate to reach out. We're here to help!

@if($studioEmail)
**Email:** {{ $studioEmail }}
@endif

@if($studioPhone)
**Phone:** {{ $studioPhone }}
@endif

Welcome aboard!

Thanks,<br>
{{ $studioName }}
</x-mail::message>
