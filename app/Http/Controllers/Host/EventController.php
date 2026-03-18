<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    protected function getHost()
    {
        $host = Auth::user()->currentHost() ?? Auth::user()->host;

        if (!$host) {
            abort(403, 'No studio access. Please select a studio.');
        }

        return $host;
    }

    protected function authorizeEvent(Event $event): void
    {
        $host = $this->getHost();
        if ($event->host_id !== $host->id) {
            abort(403, 'This event belongs to a different studio.');
        }
    }

    /**
     * Display a listing of events.
     */
    public function index(Request $request): View
    {
        $host = $this->getHost();

        $query = Event::forHost($host->id)
            ->withCount(['registeredAttendees']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->get('filter') === 'upcoming') {
            $query->upcoming();
        } elseif ($request->get('filter') === 'past') {
            $query->past();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->latest('start_datetime')->paginate(12)->withQueryString();

        // Get counts for tabs
        $counts = [
            'all' => Event::forHost($host->id)->count(),
            'draft' => Event::forHost($host->id)->draft()->count(),
            'published' => Event::forHost($host->id)->published()->count(),
            'upcoming' => Event::forHost($host->id)->published()->upcoming()->count(),
            'past' => Event::forHost($host->id)->past()->count(),
        ];

        return view('host.events.index', compact('events', 'counts'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $host = $this->getHost();
        $timezones = timezone_identifiers_list();

        return view('host.events.create', compact('host', 'timezones'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): RedirectResponse
    {
        $host = $this->getHost();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', 'in:in_person,online,hybrid'],
            'visibility' => ['required', 'in:public,unlisted,private'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required'],
            'timezone' => ['required', 'string'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'online_url' => ['nullable', 'url', 'max:255'],
            'online_platform' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'skill_level' => ['required', 'in:beginner,intermediate,advanced,all_levels'],
            'audience_type' => ['required', 'in:adults,kids,families,seniors,all'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'waitlist_enabled' => ['boolean'],
            'hide_attendee_list' => ['boolean'],
        ]);

        // Combine date and time
        $startDatetime = $validated['start_date'] . ' ' . $validated['start_time'];
        $endDatetime = $validated['end_date'] . ' ' . $validated['end_time'];

        // Create the event
        $event = Event::create([
            'host_id' => $host->id,
            'created_by_user_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'event_type' => $validated['event_type'],
            'visibility' => $validated['visibility'],
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'timezone' => $validated['timezone'],
            'venue_name' => $validated['venue_name'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'online_url' => $validated['online_url'],
            'online_platform' => $validated['online_platform'],
            'capacity' => $validated['capacity'],
            'skill_level' => $validated['skill_level'],
            'audience_type' => $validated['audience_type'],
            'waitlist_enabled' => $request->boolean('waitlist_enabled'),
            'hide_attendee_list' => $request->boolean('hide_attendee_list'),
            'status' => Event::STATUS_DRAFT,
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('events/' . $event->id, 'public');
            $event->update(['cover_image' => '/storage/' . $path]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully! You can now add attendees or publish it.');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): View
    {
        $this->authorizeEvent($event);
        $host = $this->getHost();

        $event->load([
            'attendees.client',
            'createdBy',
        ]);

        $stats = [
            'total_registered' => $event->attendees()->registered()->count(),
            'attended' => $event->attendees()->attended()->count(),
            'waitlist' => $event->attendees()->waitlisted()->count(),
            'cancelled' => $event->attendees()->cancelled()->count(),
        ];

        // Get available clients for adding to event
        $availableClients = Client::forHost($host->id)
            ->active()
            ->whereNotIn('id', $event->attendees()->pluck('client_id'))
            ->orderBy('first_name')
            ->get();

        return view('host.events.show', compact('event', 'stats', 'availableClients'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): View
    {
        $this->authorizeEvent($event);
        $host = $this->getHost();
        $timezones = timezone_identifiers_list();

        return view('host.events.edit', compact('event', 'host', 'timezones'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', 'in:in_person,online,hybrid'],
            'visibility' => ['required', 'in:public,unlisted,private'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required'],
            'timezone' => ['required', 'string'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'online_url' => ['nullable', 'url', 'max:255'],
            'online_platform' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'skill_level' => ['required', 'in:beginner,intermediate,advanced,all_levels'],
            'audience_type' => ['required', 'in:adults,kids,families,seniors,all'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'waitlist_enabled' => ['boolean'],
            'hide_attendee_list' => ['boolean'],
        ]);

        // Combine date and time
        $startDatetime = $validated['start_date'] . ' ' . $validated['start_time'];
        $endDatetime = $validated['end_date'] . ' ' . $validated['end_time'];

        $event->update([
            'title' => $validated['title'],
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'event_type' => $validated['event_type'],
            'visibility' => $validated['visibility'],
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'timezone' => $validated['timezone'],
            'venue_name' => $validated['venue_name'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'online_url' => $validated['online_url'],
            'online_platform' => $validated['online_platform'],
            'capacity' => $validated['capacity'],
            'skill_level' => $validated['skill_level'],
            'audience_type' => $validated['audience_type'],
            'waitlist_enabled' => $request->boolean('waitlist_enabled'),
            'hide_attendee_list' => $request->boolean('hide_attendee_list'),
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('events/' . $event->id, 'public');
            $event->update(['cover_image' => '/storage/' . $path]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Publish the event.
     */
    public function publish(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $event->publish();

        return back()->with('success', 'Event published successfully!');
    }

    /**
     * Cancel the event.
     */
    public function cancel(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $event->cancel($validated['cancellation_reason'] ?? null);

        return back()->with('success', 'Event cancelled.');
    }

    /**
     * Delete the event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        // Only allow deletion of draft events or events with no attendees
        if ($event->status !== Event::STATUS_DRAFT && $event->attendees()->exists()) {
            return back()->with('error', 'Cannot delete an event with attendees. Please cancel the event instead.');
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Add clients to event.
     */
    public function addClients(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);
        $host = $this->getHost();

        $validated = $request->validate([
            'client_ids' => ['required', 'array'],
            'client_ids.*' => ['exists:clients,id'],
        ]);

        $addedCount = 0;
        $waitlistedCount = 0;

        foreach ($validated['client_ids'] as $clientId) {
            // Verify client belongs to this host
            $client = Client::forHost($host->id)->find($clientId);
            if (!$client) {
                continue;
            }

            // Check if already registered
            if ($event->isClientRegistered($clientId)) {
                continue;
            }

            // Check capacity
            $status = EventAttendee::STATUS_REGISTERED;
            if ($event->capacity !== null && $event->registration_count >= $event->capacity) {
                if ($event->waitlist_enabled) {
                    $status = EventAttendee::STATUS_WAITLISTED;
                    $waitlistedCount++;
                } else {
                    continue; // Skip if full and no waitlist
                }
            } else {
                $addedCount++;
            }

            $attendee = EventAttendee::create([
                'event_id' => $event->id,
                'client_id' => $clientId,
                'added_by_user_id' => Auth::id(),
                'status' => $status,
                'registered_at' => now(),
            ]);

            if ($status === EventAttendee::STATUS_WAITLISTED) {
                $attendee->joinWaitlist();
            } else {
                $event->increment('registration_count');
            }
        }

        $message = '';
        if ($addedCount > 0) {
            $message = "{$addedCount} client(s) added to the event.";
        }
        if ($waitlistedCount > 0) {
            $message .= " {$waitlistedCount} client(s) added to waitlist.";
        }

        return back()->with('success', $message ?: 'No clients were added.');
    }

    /**
     * Remove a client from event.
     */
    public function removeClient(Event $event, Client $client): RedirectResponse
    {
        $this->authorizeEvent($event);

        $attendee = $event->attendees()->where('client_id', $client->id)->first();

        if ($attendee) {
            $attendee->cancel('Removed by studio');
        }

        return back()->with('success', 'Client removed from event.');
    }

    /**
     * Check in a client.
     */
    public function checkIn(Event $event, EventAttendee $attendee): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($attendee->event_id !== $event->id) {
            abort(403, 'Attendee does not belong to this event.');
        }

        if (!$attendee->can_check_in) {
            return back()->with('error', 'This attendee cannot be checked in.');
        }

        $attendee->checkIn(Auth::id());

        return back()->with('success', $attendee->client->full_name . ' has been checked in!');
    }

    /**
     * Mark attendee as no-show.
     */
    public function markNoShow(Event $event, EventAttendee $attendee): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($attendee->event_id !== $event->id) {
            abort(403, 'Attendee does not belong to this event.');
        }

        $attendee->markNoShow();

        return back()->with('success', $attendee->client->full_name . ' marked as no-show.');
    }

    /**
     * AJAX check-in for a client by client_id.
     */
    public function checkInClient(Request $request, Event $event, Client $client): \Illuminate\Http\JsonResponse
    {
        $this->authorizeEvent($event);

        // Find the attendee record
        $attendee = EventAttendee::where('event_id', $event->id)
            ->where('client_id', $client->id)
            ->first();

        if (!$attendee) {
            return response()->json([
                'success' => false,
                'message' => 'Client is not registered for this event.'
            ], 404);
        }

        if ($attendee->checked_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in.'
            ], 400);
        }

        $attendee->checkIn(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully',
            'checked_in_at' => $attendee->fresh()->checked_in_at->format('g:i A'),
        ]);
    }
}
