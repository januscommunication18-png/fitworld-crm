<?php

namespace App\Notifications;

use App\Models\PriceOverrideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceOverrideRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PriceOverrideRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request;
        $host = $request->host;
        $requester = $request->requester;
        $client = $request->client;
        $location = $request->location;

        $discountAmount = number_format($request->discount_amount, 2);
        $discountPercent = $request->discount_percentage;
        $originalPrice = number_format($request->original_price, 2);
        $requestedPrice = number_format($request->requested_price, 2);

        $approvalUrl = url("/price-override/review/{$request->confirmation_code}");

        return (new MailMessage)
            ->subject("Price Override Request - {$request->confirmation_code}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("A new price override request has been submitted and requires your approval.")
            ->line('')
            ->line("**Request Details:**")
            ->line("- **Confirmation Code:** {$request->confirmation_code}")
            ->line("- **Requested By:** {$requester->name}")
            ->line("- **Location:** " . ($location?->name ?? 'Not specified'))
            ->line("- **Client:** " . ($client?->full_name ?? 'Walk-in'))
            ->line('')
            ->line("**Pricing:**")
            ->line("- **Original Price:** \${$originalPrice}")
            ->line("- **Requested Price:** \${$requestedPrice}")
            ->line("- **Discount:** \${$discountAmount} ({$discountPercent}% off)")
            ->when($request->discount_code, function ($message) use ($request) {
                return $message->line("- **Discount Code:** {$request->discount_code}");
            })
            ->when($request->reason, function ($message) use ($request) {
                return $message->line("- **Reason:** {$request->reason}");
            })
            ->line('')
            ->line("**This request expires in 30 minutes.**")
            ->action('Review Request', $approvalUrl)
            ->line('')
            ->line("Or enter the confirmation code **{$request->confirmation_code}** in the booking screen to approve.")
            ->salutation("Thank you,\n{$host->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'confirmation_code' => $this->request->confirmation_code,
            'original_price' => $this->request->original_price,
            'requested_price' => $this->request->requested_price,
        ];
    }
}
