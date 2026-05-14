<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address — ' . config('app.name'))
            ->greeting('Welcome to ' . config('app.name') . '!')
            ->line('Thanks for signing up. Please click the button below to verify your email address and continue setting up your studio.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.')
            ->salutation('— The ' . config('app.name') . ' Team');
    }

    /**
     * Get the verification URL for the given notifiable.
     * Always uses APP_URL so links are correct regardless of server port.
     */
    protected function verificationUrl($notifiable): string
    {
        $appUrl = config('app.url');

        // Temporarily force URL root to APP_URL
        $previousUrl = URL::formatRoot('https');
        URL::forceRootUrl($appUrl);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Restore previous root
        URL::forceRootUrl($previousUrl);

        return $url;
    }
}
