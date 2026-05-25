<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Mail\HelpdeskCustomerReplyMail;
use App\Models\EmailLog;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Public (signed-URL gated) helpdesk page for customers without a portal account.
 * Both endpoints are protected by the `signed` middleware so only links generated
 * via URL::signedRoute(...) — i.e., the magic links we embed in reply emails — work.
 */
class GuestHelpdeskController extends Controller
{
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Make sure the ticket belongs to this host. Signed URLs guarantee identity,
     * but we still scope by host to defend against cross-host link replay.
     */
    protected function authorizeTicket(HelpdeskTicket $ticket, Host $host): void
    {
        if ($ticket->host_id !== $host->id) {
            abort(404);
        }
    }

    public function show(Request $request, string $subdomain, HelpdeskTicket $ticket)
    {
        $host = $this->getHost($request);
        $this->authorizeTicket($ticket, $host);

        $ticket->load(['messages.user', 'assignedUser']);

        // Generate a fresh signed reply URL with the same expiry envelope as the
        // current show URL — the customer posts back to it from the form.
        $replyUrl = URL::signedRoute(
            'guest.helpdesk.reply',
            ['subdomain' => $host->subdomain, 'ticket' => $ticket->id],
            now()->addDays(30)
        );

        return view('subdomain.guest.helpdesk', [
            'host' => $host,
            'ticket' => $ticket,
            'replyUrl' => $replyUrl,
        ]);
    }

    public function reply(Request $request, string $subdomain, HelpdeskTicket $ticket)
    {
        $host = $this->getHost($request);
        $this->authorizeTicket($ticket, $host);

        $validated = $request->validate([
            'message' => 'required|string|max:20000',
        ]);

        // Customer reply — addMessage auto-flips status to customer_reply.
        $message = $ticket->addMessage($validated['message'], null, 'customer');

        // Notify the assigned team member (or host owner if unassigned).
        $this->notifyStaffOfCustomerReply($ticket->fresh(), $message, $host);

        // Re-issue a fresh signed show URL so the success page link still works.
        $showUrl = URL::signedRoute(
            'guest.helpdesk.show',
            ['subdomain' => $host->subdomain, 'ticket' => $ticket->id],
            now()->addDays(30)
        );

        return redirect($showUrl)->with('success', 'Your reply has been sent.');
    }

    /**
     * Email the studio that the customer replied. Mirrors the member-portal flow.
     */
    protected function notifyStaffOfCustomerReply(HelpdeskTicket $ticket, $message, Host $host): void
    {
        $recipient = $ticket->assigned_user_id
            ? User::find($ticket->assigned_user_id)
            : $host->getOwner();

        if (!$recipient || empty($recipient->email)) {
            return;
        }

        $recipientName = $recipient->name
            ?? trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? ''));

        $log = EmailLog::logEmail(
            recipientEmail: $recipient->email,
            subject: 'Re: ' . ($ticket->subject ?? '') . ' (customer reply)',
            bodyPreview: $ticket->name . ' replied on ticket #' . $ticket->id . ': ' . strip_tags((string) $message->message),
            hostId: $host->id,
            recipientName: $recipientName
        );

        try {
            Mail::to($recipient->email)->send(new HelpdeskCustomerReplyMail($ticket, $message, $host, $recipient));
            $log->markAsSent();
        } catch (\Throwable $e) {
            $log->markAsFailed($e->getMessage());
            Log::warning('Failed to send helpdesk customer-reply notification (guest)', [
                'ticket_id' => $ticket->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
