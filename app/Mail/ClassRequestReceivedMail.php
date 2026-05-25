<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\ClassPlan;
use App\Models\ClassRequest;
use App\Models\Host;
use Illuminate\Mail\Mailable;

/**
 * Confirmation email sent to a customer after they submit a class info-request.
 * Honors the customizable "class_request_received" template.
 */
class ClassRequestReceivedMail extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public ClassRequest $classRequest,
        public ClassPlan $classPlan,
        public Host $host
    ) {}

    public function build()
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';
        $customerName = trim($this->classRequest->first_name . ' ' . $this->classRequest->last_name);

        $variables = [
            'customer_name' => $customerName,
            'class_name' => $this->classPlan->name,
            'message' => $this->classRequest->message ?: '(no message)',
            'studio_name' => $studioName,
            'studio_email' => $this->host->studio_email ?? $this->host->contact_email ?? '',
            'studio_phone' => $this->host->phone ?? $this->host->contact_phone ?? '',
        ];

        if ($this->buildFromCustomTemplate('class_request_received', $this->host, $variables)) {
            return $this;
        }

        // Bundled fallback when no custom template exists.
        $body = '<h2>Thanks for reaching out!</h2>'
            . '<p>Hi ' . htmlspecialchars($customerName) . ',</p>'
            . '<p>We\'ve received your request about <strong>' . htmlspecialchars($this->classPlan->name) . '</strong>. A member of our team will follow up with you shortly.</p>';

        if (!empty($this->classRequest->message)) {
            $body .= '<p><strong>Your message:</strong></p><blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">'
                . nl2br(htmlspecialchars($this->classRequest->message)) . '</blockquote>';
        }

        $body .= '<p>— ' . htmlspecialchars($studioName) . '</p>';

        $this->subject("We got your request — {$this->classPlan->name}");
        $this->html($this->wrapInLayout($body, $this->host));

        return $this;
    }
}
