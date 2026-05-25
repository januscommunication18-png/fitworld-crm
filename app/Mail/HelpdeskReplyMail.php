<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCustomTemplate;
use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;

/**
 * Sent to the customer when a staff member replies to their helpdesk ticket.
 * Honors the customizable "helpdesk_reply" template.
 */
class HelpdeskReplyMail extends Mailable
{
    use UsesCustomTemplate;

    public function __construct(
        public HelpdeskTicket $ticket,
        public HelpdeskMessage $message,
        public Host $host,
        public ?User $staff = null
    ) {}

    public function build()
    {
        $studioName = $this->host->studio_name ?? 'Our Studio';
        $staffName = $this->staff?->name ?? ($this->message->sender_name ?: $studioName);
        $ticketUrl = $this->buildTicketUrl();

        $variables = [
            'customer_name' => $this->ticket->name,
            'ticket_id' => (string) $this->ticket->id,
            'ticket_subject' => $this->ticket->subject ?? '',
            'response_message' => $this->message->message ?? '',
            'studio_name' => $studioName,
            'studio_signature' => $staffName . ' — ' . $studioName,
            'ticket_url' => $ticketUrl,
        ];

        if ($this->buildFromCustomTemplate('helpdesk_reply', $this->host, $variables)) {
            return $this;
        }

        // Bundled fallback when no custom template exists.
        $body = '<h2>Re: ' . htmlspecialchars($variables['ticket_subject']) . '</h2>'
            . '<p>Hi ' . htmlspecialchars($variables['customer_name']) . ',</p>'
            . '<div>' . $variables['response_message'] . '</div>'
            . '<p style="margin:24px 0 8px;"><a href="' . htmlspecialchars($ticketUrl) . '" style="display:inline-block;padding:10px 18px;background:#6366f1;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Open conversation</a></p>'
            . '<p style="font-size:12px;color:#6b7280;margin:0 0 4px;">Or copy this link into your browser:</p>'
            . '<p style="font-size:12px;color:#6366f1;word-break:break-all;margin:0 0 16px;"><a href="' . htmlspecialchars($ticketUrl) . '" style="color:#6366f1;">' . htmlspecialchars($ticketUrl) . '</a></p>'
            . '<p style="font-size:12px;color:#6b7280;margin-top:24px;">Ticket #' . $variables['ticket_id'] . '</p>'
            . '<p>Best regards,<br>' . htmlspecialchars($variables['studio_signature']) . '</p>';

        $this->subject('Re: ' . $variables['ticket_subject']);
        $this->html($this->wrapInLayout($body, $this->host));

        return $this;
    }

    /**
     * Return the URL the customer should click to view this conversation.
     * Portal members get a deep-link into /portal/helpdesk/{id} (they will be
     * prompted to log in if they aren't already). Guests with no portal account
     * get a 30-day signed magic link.
     */
    protected function buildTicketUrl(): string
    {
        $client = $this->ticket->client;

        if ($client && method_exists($client, 'hasPortalAccess') && $client->hasPortalAccess()) {
            return route('member.portal.helpdesk.show', [
                'subdomain' => $this->host->subdomain,
                'ticket' => $this->ticket->id,
            ]);
        }

        return URL::signedRoute(
            'guest.helpdesk.show',
            [
                'subdomain' => $this->host->subdomain,
                'ticket' => $this->ticket->id,
            ],
            now()->addDays(30)
        );
    }
}
