<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * Sent to a team member when a helpdesk ticket gets assigned to them.
 * Honors the customizable "helpdesk_assigned_team" template.
 */
class HelpdeskAssignedMail extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public HelpdeskTicket $ticket,
        public User $teamMember,
        public Host $host
    ) {}

    public function build()
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';
        $teamMemberName = $this->teamMember->name
            ?? trim(($this->teamMember->first_name ?? '') . ' ' . ($this->teamMember->last_name ?? ''));

        $variables = [
            'team_member_name' => $teamMemberName,
            'ticket_id' => (string) $this->ticket->id,
            'ticket_subject' => $this->ticket->subject ?? '',
            'customer_name' => $this->ticket->name ?? '',
            'customer_email' => $this->ticket->email ?? '',
            'ticket_url' => url('/helpdesk/' . $this->ticket->id),
            'studio_name' => $studioName,
        ];

        if ($this->buildFromCustomTemplate('helpdesk_assigned_team', $this->host, $variables)) {
            return $this;
        }

        $body = '<h2>Ticket assigned to you</h2>'
            . '<p>Hi ' . htmlspecialchars($teamMemberName) . ',</p>'
            . '<p>A helpdesk ticket has just been assigned to you. Details:</p>'
            . '<ul>'
            . '<li><strong>Ticket:</strong> #' . $variables['ticket_id'] . ' — ' . htmlspecialchars($variables['ticket_subject']) . '</li>'
            . '<li><strong>From:</strong> ' . htmlspecialchars($variables['customer_name']) . '</li>'
            . '<li><strong>Email:</strong> ' . htmlspecialchars($variables['customer_email']) . '</li>'
            . '</ul>'
            . '<p><a href="' . htmlspecialchars($variables['ticket_url']) . '">Open the ticket</a> to review and reply.</p>'
            . '<p>— ' . htmlspecialchars($studioName) . '</p>';

        $this->subject('Ticket assigned to you: ' . $variables['ticket_subject']);
        $this->html($this->wrapInLayout($body, $this->host));

        return $this;
    }
}
