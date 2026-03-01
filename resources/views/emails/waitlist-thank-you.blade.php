<x-mail::message>
# You're on the Waitlist!

Hi {{ $waitlistEntry->first_name }},

Thank you for joining the **FitCRM** waitlist! We're thrilled to have you on board.

## What happens next?

We're working hard to build the all-in-one studio management platform that boutique fitness businesses deserve. As a waitlist member, you'll be among the first to:

- **Get early access** when we launch
- **Receive founding member pricing** (exclusive discounts)
- **Help shape the product** with your feedback

## Your Details

**Name:** {{ $waitlistEntry->full_name }}

**Email:** {{ $waitlistEntry->email }}

@if($waitlistEntry->studio_name)
**Studio:** {{ $waitlistEntry->studio_name }}
@endif

---

We'll reach out soon with updates on our progress. In the meantime, feel free to reply to this email if you have any questions or suggestions — we read every message.

Thanks for believing in us!

The FitCRM Team<br>
[hello@fitcrm.io](mailto:hello@fitcrm.io)
</x-mail::message>
