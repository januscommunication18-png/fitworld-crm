<?php

namespace App\Mail;

use App\Http\Controllers\Host\EmailTemplateController;
use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\Client;
use App\Services\QrImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

/**
 * Sends a client their personal check-in QR code. Triggered manually from the
 * client profile. Rendered through the editable `client_qr_code` template.
 */
class ClientQrMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesCustomTemplate;

    public function __construct(
        public Client $client,
    ) {}

    public function build()
    {
        $host = $this->client->host;
        $studioName = $host?->studio_name ?? config('app.name');

        $fromAddress = $host?->email ?: config('mail.from.address');
        if ($fromAddress) {
            $this->from($fromAddress, $studioName);
        }

        $variables = $this->buildTemplateVariables();
        $templateKey = 'client_qr_code';

        if ($host && $this->buildFromCustomTemplate($templateKey, $host, $variables)) {
            return $this;
        }

        $defaultBody = EmailTemplateController::getDefaultTemplateHtml($templateKey);
        $defaultSubject = EmailTemplateController::getDefaultSubject($templateKey);

        return $this->renderTemplateStrings($defaultSubject, $defaultBody, $host, $variables);
    }

    /**
     * @return array<string,string>
     */
    protected function buildTemplateVariables(): array
    {
        $client = $this->client;
        $host = $client->host;
        $token = $client->getOrCreateQrCode()->qr_token;

        return [
            'customer_name' => $client->full_name ?: ($client->email ?? ''),
            'customer_email' => $client->email ?? '',
            // Inline as a self-contained data URI so it renders without an
            // external fetch (matches the booking-confirmation approach).
            'qr_image_url' => app(QrImageService::class)->dataUriForToken($token),
            'qr_download_url' => route('checkin-qr.show', ['token' => $token]) . '?dl=1',
            'studio_name' => $host?->studio_name ?? '',
            'studio_phone' => $host?->phone ?? '',
            'studio_email' => $host?->email ?? '',
        ];
    }

    public function attachments(): array
    {
        $png = app(QrImageService::class)->pngForClient($this->client);

        return [
            Attachment::fromData(fn () => $png, 'checkin-qr.png')->withMime('image/png'),
        ];
    }
}
