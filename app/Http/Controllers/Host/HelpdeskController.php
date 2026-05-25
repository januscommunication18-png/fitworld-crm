<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\HelpdeskAssignedMail;
use App\Mail\HelpdeskReplyMail;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTag;
use App\Models\HelpdeskTicket;
use App\Models\ServicePlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HelpdeskController extends Controller
{
    protected function getHost()
    {
        $host = Auth::user()->currentHost() ?? Auth::user()->host;

        if (!$host) {
            abort(403, 'No studio access. Please select a studio.');
        }

        return $host;
    }

    /**
     * Users with the helpdesk.view permission see and manage every ticket on
     * the host. Users with only helpdesk.view_assigned see tickets assigned to
     * them. Users with neither are denied helpdesk access entirely.
     */
    protected function canSeeAllTickets($host = null): bool
    {
        $host = $host ?? $this->getHost();
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasPermission('helpdesk.view', $host);
    }

    /**
     * Anyone with either helpdesk.view or helpdesk.view_assigned has at least
     * some level of helpdesk access.
     */
    protected function canAccessHelpdesk($host = null): bool
    {
        $host = $host ?? $this->getHost();
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasPermission('helpdesk.view', $host)
            || $user->hasPermission('helpdesk.view_assigned', $host);
    }

    /**
     * Block access to a ticket unless the caller can see all tickets OR is
     * the assignee and has helpdesk.view_assigned.
     */
    protected function authorizeTicketAccess(HelpdeskTicket $ticket, $host = null): void
    {
        if ($this->canSeeAllTickets($host)) {
            return;
        }
        $user = Auth::user();
        if (
            $user
            && $user->hasPermission('helpdesk.view_assigned', $host)
            && (int) $ticket->assigned_user_id === (int) $user->id
        ) {
            return;
        }
        abort(403, 'You do not have permission to view this ticket.');
    }

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $host = $this->getHost();

        if (!$this->canAccessHelpdesk($host)) {
            abort(403, 'You do not have permission to access the helpdesk.');
        }

        $canSeeAll = $this->canSeeAllTickets($host);
        $userId = (int) Auth::id();

        $query = HelpdeskTicket::forHost($host->id);

        // Users without "view all" permission only see tickets assigned to them.
        if (!$canSeeAll) {
            $query->where('assigned_user_id', $userId);
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('source')) {
            $query->bySource($request->source);
        }

        // Assigned-to filter only makes sense for users who can see everything.
        if ($canSeeAll && $request->filled('assigned')) {
            if ($request->assigned === 'unassigned') {
                $query->unassigned();
            } else {
                $query->assignedTo($request->assigned);
            }
        }

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $tickets = $query->with(['client', 'assignedUser', 'tags', 'servicePlan'])
            ->paginate(25)
            ->withQueryString();

        $tags = HelpdeskTag::forHost($host->id)->orderBy('name')->get();
        $teamMembers = $host->getAllTeamMembers();

        // Status-tab counts respect the same scoping so the numbers match the list.
        $countsBase = fn () => $canSeeAll
            ? HelpdeskTicket::forHost($host->id)
            : HelpdeskTicket::forHost($host->id)->where('assigned_user_id', $userId);

        $counts = [
            'all' => $countsBase()->count(),
            'open' => $countsBase()->open()->count(),
            'in_progress' => $countsBase()->inProgress()->count(),
            'customer_reply' => $countsBase()->customerReply()->count(),
            'resolved' => $countsBase()->resolved()->count(),
        ];

        return view('host.helpdesk.index', [
            'tickets' => $tickets,
            'tags' => $tags,
            'teamMembers' => $teamMembers,
            'counts' => $counts,
            'filters' => $request->only(['search', 'status', 'source', 'assigned', 'tag']),
            'statuses' => HelpdeskTicket::getStatuses(),
            'sources' => HelpdeskTicket::getSourceTypes(),
            'canSeeAllTickets' => $canSeeAll,
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $host = $this->getHost();
        if (!Auth::user()->hasPermission('helpdesk.create', $host)) {
            abort(403, 'You do not have permission to create helpdesk tickets.');
        }
        $tags = HelpdeskTag::forHost($host->id)->orderBy('name')->get();
        $teamMembers = $host->getAllTeamMembers();

        return view('host.helpdesk.create', [
            'tags' => $tags,
            'teamMembers' => $teamMembers,
            'offeringsByType' => $this->buildOfferingsByType($host),
            'sources' => HelpdeskTicket::getSourceTypes(),
        ]);
    }

    /**
     * Returns active catalog offerings grouped by REQUESTED_TYPE_MAP alias.
     * The create form renders one picker per alias and shows the matching
     * one based on which "type" the user chose. Aliases with no active items
     * are omitted so the type chooser doesn't offer empty buckets.
     *
     * @return array<string, array{label: string, icon: string, items: \Illuminate\Support\Collection}>
     */
    protected function buildOfferingsByType($host): array
    {
        // Each model's scopeActive() handles the "active" filter — ClassPass
        // and MembershipPlan gate on status='active' while the others gate on
        // is_active=1. Calling ->active() avoids hard-coding the wrong column.
        $sources = [
            'class_plan'   => ['label' => 'Class Plan',   'icon' => 'tabler--users-group',     'query' => fn() => $host->classPlans()->active()->orderBy('name')->get(['id', 'name'])],
            'service_plan' => ['label' => 'Service Plan', 'icon' => 'tabler--user',            'query' => fn() => $host->servicePlans()->active()->orderBy('name')->get(['id', 'name'])],
            'class_pass'   => ['label' => 'Class Pass',   'icon' => 'tabler--ticket',          'query' => fn() => $host->classPasses()->active()->orderBy('name')->get(['id', 'name'])],
            'membership'   => ['label' => 'Membership',   'icon' => 'tabler--id-badge-2',      'query' => fn() => $host->membershipPlans()->active()->orderBy('name')->get(['id', 'name'])],
            'rental_space' => ['label' => 'Rental Space', 'icon' => 'tabler--building',        'query' => fn() => $host->spaceRentalConfigs()->active()->orderBy('name')->get(['id', 'name'])],
            'item_rental'  => ['label' => 'Item Rental',  'icon' => 'tabler--package',         'query' => fn() => $host->rentalItems()->active()->orderBy('name')->get(['id', 'name'])],
            'event'        => ['label' => 'Event',        'icon' => 'tabler--calendar-event',  'query' => fn() => Event::forHost($host->id)->where('start_datetime', '>=', now())->orderBy('start_datetime')->get(['id', 'title'])->map(fn($e) => (object) ['id' => $e->id, 'name' => $e->title])],
        ];

        $out = [];
        foreach ($sources as $alias => $cfg) {
            $items = $cfg['query']();
            if ($items->isNotEmpty()) {
                $out[$alias] = [
                    'label' => $cfg['label'],
                    'icon'  => $cfg['icon'],
                    'items' => $items,
                ];
            }
        }
        return $out;
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $host = $this->getHost();

        if (!Auth::user()->hasPermission('helpdesk.create', $host)) {
            abort(403, 'You do not have permission to create helpdesk tickets.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'source_type' => 'required|in:booking_request,general_inquiry,lead_magnet,manual',
            'requested_type_alias' => ['nullable', 'string', \Illuminate\Validation\Rule::in(array_keys(HelpdeskTicket::REQUESTED_TYPE_MAP))],
            'requested_offering_id' => 'nullable|integer',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',
            'assigned_user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:helpdesk_tags,id',
            'client_id' => 'nullable|integer|exists:clients,id',
        ]);

        // Resolve the chosen offering: confirm the alias is known, look up the
        // record host-scoped, and emit (requested_type, requested_id). When the
        // alias is service_plan, also mirror to the legacy column so older
        // code paths that read service_plan_id still resolve to the same row.
        $requestedType = null;
        $requestedId = null;
        $servicePlanId = null;
        if (!empty($validated['requested_type_alias']) && !empty($validated['requested_offering_id'])) {
            $modelClass = HelpdeskTicket::REQUESTED_TYPE_MAP[$validated['requested_type_alias']] ?? null;
            if ($modelClass) {
                $candidate = $modelClass::where('host_id', $host->id)
                    ->find((int) $validated['requested_offering_id']);
                if ($candidate) {
                    $requestedType = $modelClass;
                    $requestedId = $candidate->getKey();
                    if ($validated['requested_type_alias'] === 'service_plan') {
                        $servicePlanId = $candidate->getKey();
                    }
                }
            }
        }

        // Prefer the explicit client_id selected via the existing-client picker.
        // Fall back to email lookup so the legacy "just type contact details" flow
        // still auto-links if there happens to be a matching client.
        $client = null;
        if (!empty($validated['client_id'])) {
            $client = Client::where('host_id', $host->id)
                ->where('id', $validated['client_id'])
                ->first();
        }
        if (!$client) {
            $client = Client::where('host_id', $host->id)
                ->where('email', $validated['email'])
                ->first();
        }

        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $client?->id,
            'source_type' => $validated['source_type'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? null,
            'service_plan_id' => $servicePlanId,
            'requested_type' => $requestedType,
            'requested_id' => $requestedId,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'status' => $validated['assigned_user_id'] ? HelpdeskTicket::STATUS_IN_PROGRESS : HelpdeskTicket::STATUS_OPEN,
        ]);

        // Attach tags
        if (!empty($validated['tags'])) {
            $ticket->tags()->attach($validated['tags']);
        }

        // Add initial message if provided
        if (!empty($validated['message'])) {
            $ticket->addMessage($validated['message'], null, 'customer');
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        if ($ticket->host_id !== $host->id) {
            abort(404);
        }

        $this->authorizeTicketAccess($ticket, $host);

        $ticket->load(['client', 'assignedUser', 'tags', 'servicePlan', 'requestedItem', 'messages.user']);
        $tags = HelpdeskTag::forHost($host->id)->orderBy('name')->get();
        $teamMembers = $host->getAllTeamMembers();

        return view('host.helpdesk.show', [
            'ticket' => $ticket,
            'tags' => $tags,
            'teamMembers' => $teamMembers,
            'statuses' => HelpdeskTicket::getStatuses(),
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        if ($ticket->host_id !== $host->id) {
            abort(404);
        }

        $this->authorizeTicketAccess($ticket, $host);

        if (!Auth::user()->hasPermission('helpdesk.edit', $host)) {
            abort(403, 'You do not have permission to edit helpdesk tickets.');
        }

        // Reassignment is a separate permission so admins can grant edit-only
        // access without letting someone shuffle tickets around.
        if ($request->has('assigned_user_id') && !Auth::user()->hasPermission('helpdesk.assign', $host)) {
            abort(403, 'You do not have permission to assign helpdesk tickets.');
        }

        $validated = $request->validate([
            'status' => 'nullable|in:open,in_progress,customer_reply,resolved',
            'assigned_user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:helpdesk_tags,id',
        ]);

        if ($request->has('status')) {
            $ticket->status = $validated['status'];
        }

        $assignmentChanged = false;
        $newAssigneeId = null;
        if ($request->has('assigned_user_id')) {
            $newAssigneeId = $validated['assigned_user_id'] ?: null;
            $assignmentChanged = (int) $ticket->assigned_user_id !== (int) $newAssigneeId;
            $ticket->assigned_user_id = $newAssigneeId;
            if ($newAssigneeId && $ticket->status === HelpdeskTicket::STATUS_OPEN) {
                $ticket->status = HelpdeskTicket::STATUS_IN_PROGRESS;
            }
        }

        $ticket->save();

        // Fire the team-notification email when a new assignee was set.
        if ($assignmentChanged && $newAssigneeId) {
            $this->sendAssignmentEmail($ticket->fresh(), (int) $newAssigneeId, $host);
        }

        if ($request->has('tags')) {
            $ticket->tags()->sync($validated['tags'] ?? []);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully.',
            ]);
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Add a reply to the ticket.
     */
    public function reply(Request $request, HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        if ($ticket->host_id !== $host->id) {
            abort(404);
        }

        $this->authorizeTicketAccess($ticket, $host);

        if (!Auth::user()->hasPermission('helpdesk.reply', $host)) {
            abort(403, 'You do not have permission to reply to helpdesk tickets.');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $ticket->addMessage(
            $validated['message'],
            Auth::id(),
            'staff'
        );

        // Only flip status to "customer_reply" when the staff member explicitly
        // checked the "Waiting for customer reply" box on the reply form.
        if (
            $request->boolean('waiting_for_customer_reply')
            && $ticket->status !== HelpdeskTicket::STATUS_RESOLVED
            && $ticket->status !== HelpdeskTicket::STATUS_CUSTOMER_REPLY
        ) {
            $ticket->update(['status' => HelpdeskTicket::STATUS_CUSTOMER_REPLY]);
        }

        // Notify the customer that the studio replied.
        $this->sendReplyEmail($ticket->fresh(), $message, $host);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully.',
                'data' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_name' => $message->sender_name,
                    'sender_type' => $message->sender_type,
                    'created_at' => $message->created_at->format('M j, Y g:i A'),
                ],
            ]);
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Reply added successfully.');
    }

    /**
     * Convert ticket to client.
     */
    public function convertToClient(HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        if ($ticket->host_id !== $host->id) {
            abort(404);
        }

        $this->authorizeTicketAccess($ticket, $host);

        if ($ticket->client_id) {
            return redirect()->route('helpdesk.show', $ticket)
                ->with('info', 'This ticket is already linked to a client.');
        }

        $client = $ticket->convertToClient();

        if ($client) {
            return redirect()->route('helpdesk.show', $ticket)
                ->with('success', "Client '{$client->full_name}' created successfully.");
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('error', 'Failed to create client.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        if ($ticket->host_id !== $host->id) {
            abort(404);
        }

        if (!Auth::user()->hasPermission('helpdesk.delete', $host)) {
            abort(403, 'You do not have permission to delete helpdesk tickets.');
        }

        $ticket->delete();

        return redirect()->route('helpdesk.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Store a new helpdesk tag (consumed by the tag-config drawer via JSON).
     */
    public function storeTag(Request $request)
    {
        $host = $this->getHost();

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:helpdesk_tags,name,NULL,id,host_id,' . $host->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag = HelpdeskTag::create([
            'host_id' => $host->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully.',
                'tag' => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                    'usage_count' => 0,
                    'is_active' => true,
                    'is_preset' => false,
                ],
            ]);
        }

        return back()->with('success', 'Tag created successfully.');
    }

    /**
     * Update an existing helpdesk tag (JSON only).
     */
    public function updateTag(Request $request, HelpdeskTag $tag)
    {
        $host = $this->getHost();
        if ($tag->host_id !== $host->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:helpdesk_tags,name,' . $tag->id . ',id,host_id,' . $host->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully.',
                'tag' => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ],
            ]);
        }

        return back()->with('success', 'Tag updated successfully.');
    }

    /**
     * Delete a helpdesk tag (JSON or redirect).
     */
    public function destroyTag(Request $request, HelpdeskTag $tag)
    {
        $host = $this->getHost();
        if ($tag->host_id !== $host->id) {
            abort(404);
        }

        $tag->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tag deleted successfully.']);
        }

        return back()->with('success', 'Tag deleted successfully.');
    }

    /**
     * Send the customer the studio's reply email. Failures are logged
     * but don't roll back the reply (the message is already saved).
     */
    protected function sendReplyEmail(HelpdeskTicket $ticket, HelpdeskMessage $message, $host): void
    {
        if (empty($ticket->email)) {
            return;
        }

        $staff = $message->user_id ? User::find($message->user_id) : null;

        $log = EmailLog::logEmail(
            recipientEmail: $ticket->email,
            subject: 'Re: ' . ($ticket->subject ?? ''),
            bodyPreview: strip_tags((string) $message->message),
            hostId: $host->id,
            recipientName: $ticket->name
        );

        try {
            Mail::to($ticket->email)->send(new HelpdeskReplyMail($ticket, $message, $host, $staff));
            $log->markAsSent();
        } catch (\Throwable $e) {
            $log->markAsFailed($e->getMessage());
            Log::warning('Failed to send helpdesk reply email', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send the assignee a notification that the ticket was assigned to them.
     */
    protected function sendAssignmentEmail(HelpdeskTicket $ticket, int $userId, $host): void
    {
        $teamMember = User::find($userId);
        if (!$teamMember || empty($teamMember->email)) {
            return;
        }

        $log = EmailLog::logEmail(
            recipientEmail: $teamMember->email,
            subject: 'Ticket assigned to you: ' . ($ticket->subject ?? ''),
            bodyPreview: "Ticket #{$ticket->id} from {$ticket->name} has been assigned to you.",
            hostId: $host->id,
            recipientName: $teamMember->name ?? trim(($teamMember->first_name ?? '') . ' ' . ($teamMember->last_name ?? ''))
        );

        try {
            Mail::to($teamMember->email)->send(new HelpdeskAssignedMail($ticket, $teamMember, $host));
            $log->markAsSent();
        } catch (\Throwable $e) {
            $log->markAsFailed($e->getMessage());
            Log::warning('Failed to send helpdesk assignment email', [
                'ticket_id' => $ticket->id,
                'team_member_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
