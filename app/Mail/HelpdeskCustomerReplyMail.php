<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * Notification to studio staff when a member replies to their helpdesk
 * ticket from the portal.
 */
class HelpdeskCustomerReplyMail extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public HelpdeskTicket $ticket,
        public HelpdeskMessage $message,
        public Host $host,
        public User $teamMember
    ) {}

    public function build()
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';
        $teamMemberName = $this->teamMember->name
            ?? trim(($this->teamMember->first_name ?? '') . ' ' . ($this->teamMember->last_name ?? ''));

        $variables = [
            'team_member_name' => $teamMemberName,
            'customer_name' => $this->ticket->name,
            'customer_email' => $this->ticket->email,
            'ticket_id' => (string) $this->ticket->id,
            'ticket_subject' => $this->ticket->subject ?? '',
            'customer_message' => $this->message->message ?? '',
            'ticket_url' => url('/helpdesk/' . $this->ticket->id),
            'studio_name' => $studioName,
        ];

        if ($this->buildFromCustomTemplate('helpdesk_customer_reply', $this->host, $variables)) {
            return $this;
        }

        // Bundled fallback.
        $body = '<h2>Customer reply received</h2>'
            . '<p>Hi ' . htmlspecialchars($teamMemberName) . ',</p>'
            . '<p><strong>' . htmlspecialchars($this->ticket->name) . '</strong> just replied on ticket #'
            . $this->ticket->id . ' — <em>' . htmlspecialchars($variables['ticket_subject']) . '</em>.</p>'
            . '<blockquote style="margin:8px 0;padding:8px 12px;border-left:3px solid #6366f1;background:#f9fafb;">'
            . $variables['customer_message']
            . '</blockquote>'
            . '<p><a href="' . htmlspecialchars($variables['ticket_url']) . '">Open the ticket</a> to respond.</p>'
            . '<p>— ' . htmlspecialchars($studioName) . '</p>';

        $this->subject('Re: ' . $variables['ticket_subject'] . ' (customer reply)');
        $this->html($this->wrapInLayout($body, $this->host));

        return $this;
    }
}
