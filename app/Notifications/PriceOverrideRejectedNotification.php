<?php

namespace App\Notifications;

use App\Models\PriceOverrideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceOverrideRejectedNotification extends Notification implements ShouldQueue
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
        $rejecter = $request->actionedBy;

        $originalPrice = number_format($request->original_price, 2);
        $requestedPrice = number_format($request->requested_price, 2);

        $message = (new MailMessage)
            ->subject("Price Override Rejected - {$request->confirmation_code}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your price override request has been **rejected**.")
            ->line('')
            ->line("**Request Details:**")
            ->line("- **Confirmation Code:** {$request->confirmation_code}")
            ->line("- **Rejected By:** {$rejecter->name}")
            ->line("- **Original Price:** \${$originalPrice}")
            ->line("- **Requested Price:** \${$requestedPrice}");

        if ($request->rejection_reason) {
            $message->line('')
                ->line("**Reason:** {$request->rejection_reason}");
        }

        $message->line('')
            ->line("Please proceed with the original price or submit a new request if needed.")
            ->salutation("Thank you,\n{$host->name}");

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'confirmation_code' => $this->request->confirmation_code,
            'status' => 'rejected',
            'rejection_reason' => $this->request->rejection_reason,
        ];
    }
}
