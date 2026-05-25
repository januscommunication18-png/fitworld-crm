<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\Client;
use App\Models\Host;
use Illuminate\Mail\Mailable;

class MemberActivationCode extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public Client $client,
        public string $code,
        public Host $host
    ) {}

    public function build()
    {
        $settings = $this->host->member_portal_settings ?? Host::defaultMemberPortalSettings();
        $expiryMinutes = $settings['activation_code_expiry_minutes'] ?? 10;
        $studioName = $this->host->studio_name ?? 'Our Studio';

        // Render the code as a styled OTP block — wherever the user placed
        // {{verification_code}} in their template, this HTML lands. Inline styles
        // because most email clients ignore <style> blocks.
        $styledCode = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 24px auto;">'
            . '<tr><td style="background:#f3f4f6; border-radius:8px; padding:16px 28px; font-family:Menlo,Consolas,monospace; font-size:32px; font-weight:700; letter-spacing:10px; color:#111827; text-align:center;">'
            . htmlspecialchars($this->code, ENT_QUOTES, 'UTF-8')
            . '</td></tr></table>';

        $variables = [
            'customer_name' => $this->client->first_name,
            'verification_code' => $styledCode,
            'expiry_minutes' => (string) $expiryMinutes,
            'studio_name' => $studioName,
            'studio_email' => $this->host->studio_email ?? $this->host->contact_email ?? '',
        ];

        // Honor host's custom template from /settings/communication/email-templates if present.
        if ($this->buildFromCustomTemplate('member_activation_code', $this->host, $variables)) {
            return $this;
        }

        // Fall back to the bundled markdown template.
        return $this->subject("Your Verification Code - {$studioName}")
            ->markdown('emails.member.activation-code', [
                'client' => $this->client,
                'code' => $this->code,
                'host' => $this->host,
                'studioName' => $studioName,
                'expiryMinutes' => $expiryMinutes,
            ]);
    }
}
