<?php

namespace App\Notifications;

use App\Models\PriceOverrideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceOverrideApprovedNotification extends Notification implements ShouldQueue
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
        $approver = $request->actionedBy;

        $discountAmount = number_format($request->discount_amount, 2);
        $requestedPrice = number_format($request->requested_price, 2);

        return (new MailMessage)
            ->subject("Price Override Approved - {$request->confirmation_code}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your price override request has been **approved**.")
            ->line('')
            ->line("**Request Details:**")
            ->line("- **Confirmation Code:** {$request->confirmation_code}")
            ->line("- **Approved By:** {$approver->name}")
            ->line("- **Approved Price:** \${$requestedPrice}")
            ->line("- **Discount Applied:** \${$discountAmount}")
            ->line('')
            ->line("You can now complete the booking using the confirmation code **{$request->confirmation_code}**.")
            ->salutation("Thank you,\n{$host->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'confirmation_code' => $this->request->confirmation_code,
            'status' => 'approved',
        ];
    }
}
