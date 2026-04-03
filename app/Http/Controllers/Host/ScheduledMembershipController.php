<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\MembershipPlan;
use App\Services\Schedule\RecurrenceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduledMembershipController extends Controller
{
    public function __construct(
        protected RecurrenceService $recurrenceService
    ) {}

    /**
     * Display a listing of membership schedule sessions.
     */
    public function index(Request $request)
    {
        $host = auth()->user()->host;

        // Get filter parameters
        $membershipPlanId = $request->get('membership_plan_id');
        $instructorId = $request->get('instructor_id');
        $status = $request->get('status');
        $date = $request->get('date', now()->format('Y-m-d'));
        $range = $request->get('range', 'month');

        // Calculate date range based on range filter
        if ($range === 'today') {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
        } elseif ($range === 'week') {
            $startDate = Carbon::parse($date)->startOfWeek();
            $endDate = Carbon::parse($date)->endOfWeek();
        } elseif ($range === 'month') {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = Carbon::parse($date)->endOfMonth();
        } else {
            // 'all' - show all upcoming sessions
            $startDate = now()->startOfDay();
            $endDate = now()->addYear();
        }

        // Query membership sessions (class_plan_id is null for membership-only sessions)
        $query = ClassSession::where('host_id', $host->id)
            ->whereNull('class_plan_id')
            ->with(['primaryInstructor', 'location', 'room', 'confirmedBookings.client', 'membershipPlans'])
            ->when($membershipPlanId, function ($q) use ($membershipPlanId) {
                $q->whereHas('membershipPlans', function ($query) use ($membershipPlanId) {
                    $query->where('membership_plans.id', $membershipPlanId);
                });
            })
            ->when($instructorId, fn($q) => $q->where('primary_instructor_id', $instructorId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_time');

        if ($range !== 'all') {
            $query->forDateRange($startDate, $endDate);
        } else {
            $query->where('start_time', '>=', $startDate);
        }

        $sessions = $query->get();

        // Group sessions by date for display
        $sessionsByDate = $sessions->groupBy(fn($session) => $session->start_time->format('Y-m-d'));

        // Get filter options
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $instructors = $host->instructors()->active()->orderBy('name')->get();
        $statuses = ClassSession::getStatuses();

        return view('host.membership-schedules.index', compact(
            'sessions', 'sessionsByDate', 'membershipPlans', 'instructors', 'statuses',
            'membershipPlanId', 'instructorId', 'status', 'date', 'range',
            'startDate', 'endDate'
        ));
    }

    /**
     * Show the form for creating scheduled membership class sessions.
     */
    public function create(Request $request)
    {
        $host = auth()->user()->host;

        // Get all active membership plans
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('host.scheduled-membership.create', [
            'membershipPlans' => $membershipPlans,
            'instructors' => $host->instructors()->active()->orderBy('name')->get(),
            'locations' => $host->locations()->orderBy('name')->get(),
            'selectedMembershipPlanId' => $request->membership_plan_id,
            'selectedDate' => $request->date ?? now()->format('Y-m-d'),
        ]);
    }

    /**
     * Show the form for editing a scheduled membership session.
     */
    public function edit(ClassSession $classSession)
    {
        $host = auth()->user()->host;

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        $classSession->load(['primaryInstructor', 'location', 'membershipPlans', 'backupInstructors']);

        // Parse recurrence rule to get days and end config
        $recurrenceDays = [];
        $recurrenceEndType = 'after';
        $recurrenceCount = 12;
        $recurrenceEndDate = null;

        if ($classSession->recurrence_rule) {
            $parsed = $this->recurrenceService->parseRecurrenceRule($classSession->recurrence_rule);

            $dayMap = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];
            if (!empty($parsed['days_of_week'])) {
                $recurrenceDays = array_map(fn($d) => $dayMap[(int) $d] ?? '', $parsed['days_of_week']);
            }

            if (!empty($parsed['count'])) {
                $recurrenceEndType = 'after';
                $recurrenceCount = $parsed['count'];
            } elseif (!empty($parsed['until'])) {
                $recurrenceEndType = 'on';
                $recurrenceEndDate = $parsed['until'];
            } else {
                $recurrenceEndType = 'never';
            }
        }

        // Gather instructor IDs (primary first, then backups)
        $instructorIds = [];
        if ($classSession->primary_instructor_id) {
            $instructorIds[] = $classSession->primary_instructor_id;
        }
        foreach ($classSession->backupInstructors as $backup) {
            $instructorIds[] = $backup->id;
        }

        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('host.scheduled-membership.create', [
            'membershipPlans' => $membershipPlans,
            'instructors' => $host->instructors()->active()->orderBy('name')->get(),
            'locations' => $host->locations()->orderBy('name')->get(),
            'selectedMembershipPlanId' => $classSession->membershipPlans->first()?->id,
            'selectedDate' => $classSession->start_time->format('Y-m-d'),
            'session' => $classSession,
            'editMode' => true,
            'sessionTitle' => $classSession->title,
            'startTime' => $classSession->start_time->format('H:i'),
            'endTime' => $classSession->end_time->format('H:i'),
            'recurrenceDays' => $recurrenceDays,
            'recurrenceEndType' => $recurrenceEndType,
            'recurrenceCount' => $recurrenceCount,
            'recurrenceEndDate' => $recurrenceEndDate,
            'instructorIds' => $instructorIds,
            'locationId' => $classSession->location_id,
            'capacity' => $classSession->capacity,
            'notes' => $classSession->notes,
            'status' => $classSession->status,
        ]);
    }

    /**
     * Update a scheduled membership session.
     */
    public function update(Request $request, ClassSession $classSession)
    {
        $host = auth()->user()->host;

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
            'location_id' => 'nullable|exists:locations,id',
            'capacity' => 'required|integer|min:1|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,published',
        ]);

        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->start_date . ' ' . $request->end_time);
        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

        $instructorIds = array_filter($request->instructor_ids ?? []);
        $primaryInstructorId = !empty($instructorIds) ? array_shift($instructorIds) : null;

        $membershipPlan = MembershipPlan::find($request->membership_plan_id);
        $sessionTitle = $request->title ?: $membershipPlan->name . ' Session';

        $classSession->update([
            'primary_instructor_id' => $primaryInstructorId,
            'location_id' => $request->location_id,
            'title' => $sessionTitle,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'duration_minutes' => $durationMinutes,
            'capacity' => $request->capacity,
            'status' => $request->status ?? $classSession->status,
            'notes' => $request->notes,
        ]);

        if (!empty($instructorIds)) {
            $classSession->syncBackupInstructors($instructorIds);
        } else {
            $classSession->syncBackupInstructors([]);
        }

        $classSession->membershipPlans()->sync([$request->membership_plan_id]);

        return redirect()
            ->route('schedule-planner.index', ['type' => 'membership', 'membership_plan_id' => $request->membership_plan_id])
            ->with('success', 'Membership schedule updated successfully.');
    }

    /**
     * Store scheduled membership class sessions.
     */
    public function store(Request $request)
    {
        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'recurrence_days' => 'required|array|min:1',
            'recurrence_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'recurrence_end_type' => 'required|in:after,on,never',
            'recurrence_count' => 'required_if:recurrence_end_type,after|nullable|integer|min:1|max:52',
            'recurrence_end_date' => 'required_if:recurrence_end_type,on|nullable|date|after:start_date',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
            'location_id' => 'nullable|exists:locations,id',
            'capacity' => 'required|integer|min:1|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,published',
        ]);

        $host = auth()->user()->host;

        // Parse start and end datetime
        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->start_date . ' ' . $request->end_time);
        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

        // Get instructor IDs
        $instructorIds = array_filter($request->instructor_ids ?? []);
        $primaryInstructorId = !empty($instructorIds) ? array_shift($instructorIds) : null;
        $backupInstructorIds = $instructorIds; // Remaining instructors are backups

        // Build recurrence rule
        $endValue = match ($request->recurrence_end_type) {
            'after' => (int) $request->recurrence_count,
            'on' => Carbon::parse($request->recurrence_end_date),
            default => null,
        };

        $recurrenceRule = $this->recurrenceService->buildRecurrenceRule(
            $request->recurrence_days,
            $request->recurrence_end_type,
            $endValue
        );

        // Get membership plan for default title
        $membershipPlan = \App\Models\MembershipPlan::find($request->membership_plan_id);
        $sessionTitle = $request->title ?: $membershipPlan->name . ' Session';

        // Create the first session (no class_plan_id for membership-only sessions)
        $session = ClassSession::create([
            'host_id' => $host->id,
            'class_plan_id' => null, // Membership-only session, not tied to specific class
            'primary_instructor_id' => $primaryInstructorId,
            'location_id' => $request->location_id,
            'title' => $sessionTitle,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'duration_minutes' => $durationMinutes,
            'capacity' => $request->capacity,
            'price' => null, // Included in membership
            'status' => $request->status ?? ClassSession::STATUS_DRAFT,
            'recurrence_rule' => $recurrenceRule,
            'notes' => $request->notes,
        ]);

        // Sync backup instructors
        if (!empty($backupInstructorIds)) {
            $session->syncBackupInstructors($backupInstructorIds);
        }

        // Sync the membership plan for auto-enrollment
        $session->membershipPlans()->sync([$request->membership_plan_id]);

        // Create recurring sessions
        $recurringSession = $this->recurrenceService->createRecurringSessions(
            $session,
            $request->recurrence_days,
            $request->recurrence_end_type,
            $endValue
        );

        $createdCount = 1 + $recurringSession->count();

        // Sync membership plan and backup instructors to recurring sessions
        foreach ($recurringSession as $recurring) {
            $recurring->membershipPlans()->sync([$request->membership_plan_id]);
            if (!empty($backupInstructorIds)) {
                $recurring->syncBackupInstructors($backupInstructorIds);
            }
        }

        $message = "Created {$createdCount} scheduled membership class sessions successfully.";

        return redirect()
            ->route('class-sessions.index', ['date' => $startDateTime->format('Y-m-d'), 'range' => 'month'])
            ->with('success', $message);
    }
}
