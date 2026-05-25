<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Mail\HelpdeskCustomerReplyMail;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MemberHelpdeskController extends Controller
{
    protected function getMember(): Client
    {
        return Auth::guard('member')->user();
    }

    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Ensure the ticket belongs to this host AND this member.
     */
    protected function authorizeTicket(HelpdeskTicket $ticket, Host $host, Client $member): void
    {
        if ($ticket->host_id !== $host->id || (int) $ticket->client_id !== (int) $member->id) {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $tickets = HelpdeskTicket::where('host_id', $host->id)
            ->where('client_id', $member->id)
            ->orderByRaw("FIELD(status, 'customer_reply', 'in_progress', 'open', 'resolved')")
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('subdomain.member.portal.helpdesk.index', [
            'host' => $host,
            'member' => $member,
            'tickets' => $tickets,
        ]);
    }

    public function create(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        return view('subdomain.member.portal.helpdesk.create', [
            'host' => $host,
            'member' => $member,
        ]);
    }

    public function store(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:20000',
        ]);

        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $member->id,
            'source_type' => HelpdeskTicket::SOURCE_BOOKING_REQUEST ?? 'general_inquiry',
            'name' => $member->full_name,
            'email' => $member->email,
            'phone' => $member->phone,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => HelpdeskTicket::STATUS_OPEN,
        ]);

        // Seed the conversation thread with the customer's first message so the
        // staff side renders the same message-thread UI for portal-created tickets.
        $ticket->addMessage($validated['message'], null, 'customer');

        return redirect()
            ->route('member.portal.helpdesk.show', ['subdomain' => $host->subdomain, 'ticket' => $ticket->id])
            ->with('success', 'Your request has been sent. We will get back to you shortly.');
    }

    public function show(Request $request, string $subdomain, HelpdeskTicket $ticket)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();
        $this->authorizeTicket($ticket, $host, $member);

        $ticket->load(['messages.user', 'assignedUser']);

        return view('subdomain.member.portal.helpdesk.show', [
            'host' => $host,
            'member' => $member,
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, string $subdomain, HelpdeskTicket $ticket)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();
        $this->authorizeTicket($ticket, $host, $member);

        $validated = $request->validate([
            'message' => 'required|string|max:20000',
        ]);

        // Customer reply — addMessage auto-flips status to customer_reply.
        $message = $ticket->addMessage($validated['message'], null, 'customer');

        // Notify the assigned team member (and fall back to the host owner if unassigned).
        $this->notifyStaffOfCustomerReply($ticket->fresh(), $message, $host);

        return redirect()
            ->route('member.portal.helpdesk.show', ['subdomain' => $host->subdomain, 'ticket' => $ticket->id])
            ->with('success', 'Your reply has been sent.');
    }

    /**
     * Send an email to the assignee (or host owner if unassigned) letting them
     * know the customer replied. Logged in EmailLog like the other helpdesk mailers.
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
            Log::warning('Failed to send helpdesk customer-reply notification', [
                'ticket_id' => $ticket->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
