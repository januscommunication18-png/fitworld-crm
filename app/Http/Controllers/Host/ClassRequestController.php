<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassRequest;
use App\Models\ClassSession;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassRequestController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        $query = ClassRequest::where('host_id', $host->id)
            ->with(['classPlan', 'servicePlan', 'classSession', 'helpdeskTicket', 'waitlistEntry']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            if ($request->type === 'class') {
                $query->forClasses();
            } else {
                $query->forServices();
            }
        }

        if ($request->filled('class_plan_id')) {
            $query->forClassPlan($request->class_plan_id);
        }

        if ($request->filled('service_plan_id')) {
            $query->forServicePlan($request->service_plan_id);
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        // Get counts by status
        $statusCounts = [
            'open' => ClassRequest::where('host_id', $host->id)->open()->count(),
            'in_discussion' => ClassRequest::where('host_id', $host->id)->inDiscussion()->count(),
            'need_to_convert' => ClassRequest::where('host_id', $host->id)->needToConvert()->count(),
            'booked' => ClassRequest::where('host_id', $host->id)->booked()->count(),
        ];

        return view('host.class-requests.index', [
            'requests' => $requests,
            'statusCounts' => $statusCounts,
            'classPlans' => $host->classPlans()->orderBy('name')->get(),
            'servicePlans' => $host->servicePlans()->orderBy('name')->get(),
            'statuses' => ClassRequest::getStatuses(),
            'currentStatus' => $request->status,
            'currentType' => $request->type,
        ]);
    }

    public function show(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $classRequest->load(['classPlan', 'servicePlan', 'classSession', 'helpdeskTicket.messages', 'waitlistEntries.classSession']);

        // Get upcoming sessions for this class plan (for waitlist modal)
        $upcomingSessions = collect();
        if ($classRequest->class_plan_id) {
            $upcomingSessions = ClassSession::where('host_id', $classRequest->host_id)
                ->where('class_plan_id', $classRequest->class_plan_id)
                ->where('start_time', '>=', now())
                ->notCancelled()
                ->with(['primaryInstructor', 'location', 'confirmedBookings'])
                ->orderBy('start_time')
                ->limit(20)
                ->get();
        }

        return view('host.class-requests.show', [
            'classRequest' => $classRequest,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }

    public function updateStatus(Request $request, ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $validated = $request->validate([
            'status' => 'required|in:open,in_discussion,need_to_convert,booked',
        ]);

        $classRequest->status = $validated['status'];
        $classRequest->save();

        return back()->with('success', 'Status updated successfully.');
    }

    public function markAsBooked(Request $request, ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $validated = $request->validate([
            'class_session_id' => 'nullable|exists:class_sessions,id',
        ]);

        $classRequest->markAsBooked($validated['class_session_id'] ?? null);

        return back()->with('success', 'Request marked as booked.');
    }

    public function scheduleFromRequest(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        // Redirect to create session form with pre-filled data
        if ($classRequest->isClassRequest()) {
            return redirect()->route('class-sessions.create', [
                'class_plan_id' => $classRequest->class_plan_id,
                'from_request' => $classRequest->id,
            ]);
        }

        // For service requests, redirect to service slots create
        return redirect()->route('service-slots.create', [
            'service_plan_id' => $classRequest->service_plan_id,
            'from_request' => $classRequest->id,
        ]);
    }

    public function addToWaitlist(Request $request, ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $validated = $request->validate([
            'session_ids' => 'nullable|array',
            'session_ids.*' => 'exists:class_sessions,id',
            'add_to_general_waitlist' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $sessionIds = $validated['session_ids'] ?? [];
        $addToGeneral = $validated['add_to_general_waitlist'] ?? false;
        $notes = $validated['notes'] ?? $classRequest->message;

        // Check if nothing selected
        if (empty($sessionIds) && !$addToGeneral) {
            return back()->with('error', 'Please select at least one session or enable general waitlist.');
        }

        $entriesCreated = 0;

        DB::transaction(function () use ($classRequest, $sessionIds, $addToGeneral, $notes, &$entriesCreated) {
            // Create waitlist entries for each selected session
            foreach ($sessionIds as $sessionId) {
                // Check if already on waitlist for this session
                $exists = WaitlistEntry::where('class_request_id', $classRequest->id)
                    ->where('class_session_id', $sessionId)
                    ->exists();

                if (!$exists) {
                    WaitlistEntry::create([
                        'host_id' => $classRequest->host_id,
                        'class_request_id' => $classRequest->id,
                        'class_plan_id' => $classRequest->class_plan_id,
                        'class_session_id' => $sessionId,
                        'client_id' => $classRequest->client_id,
                        'first_name' => $classRequest->first_name,
                        'last_name' => $classRequest->last_name,
                        'email' => $classRequest->email,
                        'phone' => $classRequest->phone,
                        'notes' => $notes,
                        'status' => WaitlistEntry::STATUS_WAITING,
                    ]);
                    $entriesCreated++;
                }
            }

            // Create general waitlist entry (no specific session)
            if ($addToGeneral) {
                $generalExists = WaitlistEntry::where('class_request_id', $classRequest->id)
                    ->whereNull('class_session_id')
                    ->exists();

                if (!$generalExists) {
                    WaitlistEntry::create([
                        'host_id' => $classRequest->host_id,
                        'class_request_id' => $classRequest->id,
                        'class_plan_id' => $classRequest->class_plan_id,
                        'class_session_id' => null,
                        'client_id' => $classRequest->client_id,
                        'first_name' => $classRequest->first_name,
                        'last_name' => $classRequest->last_name,
                        'email' => $classRequest->email,
                        'phone' => $classRequest->phone,
                        'notes' => $notes,
                        'status' => WaitlistEntry::STATUS_WAITING,
                    ]);
                    $entriesCreated++;
                }
            }

            // Update the request to mark waitlist as requested
            $classRequest->update(['waitlist_requested' => true]);
        });

        if ($entriesCreated > 0) {
            $message = $entriesCreated === 1
                ? 'Added to 1 waitlist successfully.'
                : "Added to {$entriesCreated} waitlists successfully.";
            return back()->with('success', $message);
        }

        return back()->with('info', 'Already on all selected waitlists.');
    }

    public function destroy(ClassRequest $classRequest)
    {
        $this->authorizeRequest($classRequest);

        $classRequest->delete();

        return redirect()
            ->route('class-requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    protected function authorizeRequest(ClassRequest $classRequest): void
    {
        if ($classRequest->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
