<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\ClassPlan;
use App\Models\ClassRequest;
use App\Models\Host;
use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * Notification email sent to a team member configured in the class plan's
 * Email Workflow when a new class info-request arrives.
 * Honors the customizable "class_request_team_notification" template.
 */
class ClassRequestTeamNotificationMail extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public ClassRequest $classRequest,
        public ClassPlan $classPlan,
        public Host $host,
        public User $teamMember
    ) {}

    public function build()
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';
        $customerName = trim($this->classRequest->first_name . ' ' . $this->classRequest->last_name);

        $variables = [
            'team_member_name' => $this->teamMember->name ?? trim(($this->teamMember->first_name ?? '') . ' ' . ($this->teamMember->last_name ?? '')),
            'customer_name' => $customerName,
            'customer_email' => $this->classRequest->email,
            'customer_phone' => $this->classRequest->phone ?? '—',
            'class_name' => $this->classPlan->name,
            'message' => $this->classRequest->message ?: '(no message)',
            'waitlist_requested' => $this->classRequest->waitlist_requested ? 'Yes' : 'No',
            'studio_name' => $studioName,
        ];

        if ($this->buildFromCustomTemplate('class_request_team_notification', $this->host, $variables)) {
            return $this;
        }

        // Bundled fallback when no custom template exists.
        $body = '<h2>New class request</h2>'
            . '<p>Hi ' . htmlspecialchars($variables['team_member_name']) . ',</p>'
            . '<p>A new class info-request just came in from the public booking page:</p>'
            . '<ul>'
            . '<li><strong>Class:</strong> ' . htmlspecialchars($this->classPlan->name) . '</li>'
            . '<li><strong>From:</strong> ' . htmlspecialchars($customerName) . '</li>'
            . '<li><strong>Email:</strong> ' . htmlspecialchars($this->classRequest->email) . '</li>'
            . '<li><strong>Phone:</strong> ' . htmlspecialchars($this->classRequest->phone ?? '—') . '</li>'
            . '<li><strong>Waitlist requested:</strong> ' . ($this->classRequest->waitlist_requested ? 'Yes' : 'No') . '</li>'
            . '</ul>';

        if (!empty($this->classRequest->message)) {
            $body .= '<p><strong>Their message:</strong></p><blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">'
                . nl2br(htmlspecialchars($this->classRequest->message)) . '</blockquote>';
        }

        $body .= '<p>Please follow up with them as soon as you can.</p><p>— ' . htmlspecialchars($studioName) . '</p>';

        $this->subject("New class request: {$this->classPlan->name} from {$customerName}");
        $this->html($this->wrapInLayout($body, $this->host));

        return $this;
    }
}
