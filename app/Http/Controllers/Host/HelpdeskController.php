<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\HelpdeskTicket;
use App\Models\HelpdeskTag;
use App\Models\Client;
use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $host = $this->getHost();
        $query = HelpdeskTicket::forHost($host->id);

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

        if ($request->filled('assigned')) {
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
        $teamMembers = $host->teamMembers()->get();

        // Get counts for status tabs
        $counts = [
            'all' => HelpdeskTicket::forHost($host->id)->count(),
            'open' => HelpdeskTicket::forHost($host->id)->open()->count(),
            'in_progress' => HelpdeskTicket::forHost($host->id)->inProgress()->count(),
            'customer_reply' => HelpdeskTicket::forHost($host->id)->customerReply()->count(),
            'resolved' => HelpdeskTicket::forHost($host->id)->resolved()->count(),
        ];

        return view('host.helpdesk.index', [
            'tickets' => $tickets,
            'tags' => $tags,
            'teamMembers' => $teamMembers,
            'counts' => $counts,
            'filters' => $request->only(['search', 'status', 'source', 'assigned', 'tag']),
            'statuses' => HelpdeskTicket::getStatuses(),
            'sources' => HelpdeskTicket::getSourceTypes(),
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $host = $this->getHost();
        $tags = HelpdeskTag::forHost($host->id)->orderBy('name')->get();
        $teamMembers = $host->teamMembers()->get();
        $servicePlans = ServicePlan::where('host_id', $host->id)->where('is_active', true)->orderBy('name')->get();

        return view('host.helpdesk.create', [
            'tags' => $tags,
            'teamMembers' => $teamMembers,
            'servicePlans' => $servicePlans,
            'sources' => HelpdeskTicket::getSourceTypes(),
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $host = $this->getHost();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'source_type' => 'required|in:booking_request,general_inquiry,lead_magnet,manual',
            'service_plan_id' => 'nullable|exists:service_plans,id',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',
            'assigned_user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:helpdesk_tags,id',
        ]);

        // Check if client exists with this email
        $client = Client::where('host_id', $host->id)
            ->where('email', $validated['email'])
            ->first();

        $ticket = HelpdeskTicket::create([
            'host_id' => $host->id,
            'client_id' => $client?->id,
            'source_type' => $validated['source_type'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? null,
            'service_plan_id' => $validated['service_plan_id'] ?? null,
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

        $ticket->load(['client', 'assignedUser', 'tags', 'servicePlan', 'messages.user']);
        $tags = HelpdeskTag::forHost($host->id)->orderBy('name')->get();
        $teamMembers = $host->teamMembers()->get();

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

        $validated = $request->validate([
            'status' => 'nullable|in:open,in_progress,customer_reply,resolved',
            'assigned_user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:helpdesk_tags,id',
        ]);

        if ($request->has('status')) {
            $ticket->status = $validated['status'];
        }

        if ($request->has('assigned_user_id')) {
            $ticket->assigned_user_id = $validated['assigned_user_id'];
            if ($validated['assigned_user_id'] && $ticket->status === HelpdeskTicket::STATUS_OPEN) {
                $ticket->status = HelpdeskTicket::STATUS_IN_PROGRESS;
            }
        }

        $ticket->save();

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

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $ticket->addMessage(
            $validated['message'],
            Auth::id(),
            'staff'
        );

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

        $ticket->delete();

        return redirect()->route('helpdesk.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Manage helpdesk tags.
     */
    public function tags()
    {
        $host = $this->getHost();
        $tags = HelpdeskTag::forHost($host->id)->withCount('tickets')->orderBy('name')->get();

        return view('host.helpdesk.tags', [
            'tags' => $tags,
            'colors' => HelpdeskTag::getDefaultColors(),
        ]);
    }

    /**
     * Store a new tag.
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully.',
                'tag' => $tag,
            ]);
        }

        return redirect()->route('helpdesk.tags')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Delete a tag.
     */
    public function destroyTag(HelpdeskTag $tag)
    {
        $host = $this->getHost();

        if ($tag->host_id !== $host->id) {
            abort(404);
        }

        $tag->delete();

        return redirect()->route('helpdesk.tags')
            ->with('success', 'Tag deleted successfully.');
    }
}
