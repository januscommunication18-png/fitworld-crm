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
     * Resolve staff user IDs and standalone instructor IDs into a single array of instructor IDs.
     */
    private function resolveInstructorIds($host, $request): array
    {
        $instructorIds = [];

        // Staff members: convert user IDs to instructor IDs via Instructor.user_id
        $staffUserIds = array_filter($request->input('staff_member_ids', []));
        if (!empty($staffUserIds)) {
            $linked = \App\Models\Instructor::where('host_id', $host->id)
                ->whereIn('user_id', $staffUserIds)
                ->pluck('id')
                ->toArray();
            $instructorIds = array_merge($instructorIds, $linked);
        }

        // Standalone instructors: already instructor IDs
        $standaloneIds = array_filter($request->input('instructor_ids', []));
        $instructorIds = array_merge($instructorIds, $standaloneIds);

        return $instructorIds;
    }

    /**
     * Display a listing of membership schedule sessions.
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->hasPermission('schedule.view') && !$authUser->hasPermission('schedule.view_own')) {
            abort(403, 'You do not have permission to view membership schedules.');
        }
        $viewOwnOnly = !$authUser->hasPermission('schedule.view') && $authUser->hasPermission('schedule.view_own');
        $host = $authUser->host;

        // Get filter parameters
        $membershipPlanId = $request->get('membership_plan_id');
        $instructorId = $request->get('instructor_id');
        $locationId = $request->get('location_id');
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
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_time');

        // Scope to sessions assigned to this user when only view_own is granted
        if ($viewOwnOnly) {
            $myInstructorIds = \App\Models\Instructor::where('host_id', $host->id)
                ->where('user_id', $authUser->id)
                ->pluck('id');
            $query->where(function ($q) use ($myInstructorIds) {
                $q->whereIn('primary_instructor_id', $myInstructorIds)
                  ->orWhereIn('backup_instructor_id', $myInstructorIds)
                  ->orWhereHas('backupInstructors', function ($q2) use ($myInstructorIds) {
                      $q2->whereIn('instructors.id', $myInstructorIds);
                  });
            });
        }

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
        $locations = $host->locations()->orderBy('name')->get();
        $statuses = ClassSession::getStatuses();

        return view('host.membership-schedules.index', compact(
            'sessions', 'sessionsByDate', 'membershipPlans', 'instructors', 'locations', 'statuses',
            'membershipPlanId', 'instructorId', 'locationId', 'status', 'date', 'range',
            'startDate', 'endDate'
        ));
    }

    /**
     * Display a membership session.
     */
    public function show(ClassSession $classSession)
    {
        $host = auth()->user()->host;

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        $classSession->load([
            'primaryInstructor',
            'backupInstructors',
            'location',
            'room',
            'membershipPlans',
            'recurrenceChildren' => fn($q) => $q->orderBy('start_time'),
            'bookings.client',
        ]);

        $membershipPlan = $classSession->membershipPlans->first();

        $confirmedBookings = $classSession->bookings->where('status', 'confirmed');
        $cancelledBookings = $classSession->bookings->where('status', 'cancelled');
        $allBookings = $classSession->bookings;
        $checkedInCount = $confirmedBookings->filter(fn($b) => $b->isCheckedIn())->count();

        return view('host.scheduled-membership.show', compact(
            'classSession', 'membershipPlan', 'confirmedBookings', 'cancelledBookings',
            'allBookings', 'checkedInCount'
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

        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        // Determine schedule type and location from query param or from the selected plan
        $scheduleType = $request->schedule_type;
        $locationId = null;
        if ($request->membership_plan_id) {
            $selectedPlan = $membershipPlans->find($request->membership_plan_id);
            if (!$scheduleType) {
                $scheduleType = $selectedPlan?->schedule_type;
            }
            if ($selectedPlan?->location_ids && count($selectedPlan->location_ids) > 0) {
                $locationId = $selectedPlan->location_ids[0];
            }
        }

        $qrCheckinEnabled = $selectedPlan->qr_checkin_enabled ?? false;

        return view('host.scheduled-membership.create', [
            'membershipPlans' => $membershipPlans,
            'locations' => $host->locations()->orderBy('name')->get(),
            'selectedMembershipPlanId' => $request->membership_plan_id,
            'selectedDate' => $request->date ?? now()->format('Y-m-d'),
            'scheduleType' => $scheduleType ?? 'scheduled',
            'locationId' => $locationId,
            'qrCheckinEnabled' => $qrCheckinEnabled,
            'hostCurrencies' => $hostCurrencies,
            'defaultCurrency' => $defaultCurrency,
            'currencySymbols' => $currencySymbols,
        ]);
    }

    /**
     * Show the form for editing a scheduled membership session.
     */
    public function edit(ClassSession $classSession)
    {
        $authUser = auth()->user();
        $host = $authUser->host;

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        if (!$authUser->hasPermission('schedule.edit')) {
            abort(403, 'You do not have permission to edit existing schedules.');
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

        // Gather instructor IDs and split into staff (user IDs) and standalone instructors
        $rawInstructorIds = [];
        if ($classSession->primary_instructor_id) {
            $rawInstructorIds[] = $classSession->primary_instructor_id;
        }
        foreach ($classSession->backupInstructors as $backup) {
            $rawInstructorIds[] = $backup->id;
        }

        // Convert instructor IDs to user IDs for staff, keep standalone instructor IDs
        $assignedStaffMemberIds = [];
        $assignedInstructorIds = [];
        if (!empty($rawInstructorIds)) {
            $instructors = \App\Models\Instructor::whereIn('id', $rawInstructorIds)->get();
            $staffUserIds = $host->teamMembers()->pluck('users.id')->toArray();

            foreach ($instructors as $instructor) {
                if ($instructor->user_id && in_array($instructor->user_id, $staffUserIds)) {
                    // Instructor is linked to a team member
                    $assignedStaffMemberIds[] = $instructor->user_id;
                } else {
                    // Standalone instructor
                    $assignedInstructorIds[] = $instructor->id;
                }
            }
        }

        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->active()
            ->orderBy('name')
            ->get();

        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.scheduled-membership.create', [
            'membershipPlans' => $membershipPlans,
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
            'assignedStaffMemberIds' => $assignedStaffMemberIds,
            'assignedInstructorIds' => $assignedInstructorIds,
            'locationId' => $classSession->location_id,
            'capacity' => $classSession->capacity,
            'notes' => $classSession->notes,
            'status' => $classSession->status,
            'qrCheckinEnabled' => $classSession->membershipPlans->first()?->qr_checkin_enabled ?? false,
            'hostCurrencies' => $hostCurrencies,
            'defaultCurrency' => $defaultCurrency,
            'currencySymbols' => $currencySymbols,
        ]);
    }

    /**
     * Update a scheduled membership session.
     */
    public function update(Request $request, ClassSession $classSession)
    {
        $authUser = auth()->user();
        $host = $authUser->host;

        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        if (!$authUser->hasPermission('schedule.edit')) {
            abort(403, 'You do not have permission to edit existing schedules.');
        }

        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'title' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'staff_member_ids' => 'nullable|array',
            'staff_member_ids.*' => 'exists:users,id',
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

        // Resolve staff user IDs to instructor IDs, merge with standalone instructor IDs
        $allInstructorIds = $this->resolveInstructorIds($host, $request);
        $primaryInstructorId = !empty($allInstructorIds) ? array_shift($allInstructorIds) : null;

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

        $classSession->syncBackupInstructors($allInstructorIds);
        $classSession->membershipPlans()->sync([$request->membership_plan_id]);

        return redirect()
            ->route('schedule-planner.index', ['type' => 'membership', 'membership_plan_id' => $request->membership_plan_id])
            ->with('success', 'Membership schedule updated successfully.');
    }

    /**
     * Store scheduled membership class sessions or open access plan.
     */
    public function store(Request $request)
    {
        $host = auth()->user()->host;
        $scheduleType = $request->input('schedule_type', 'scheduled');

        // Common validation
        $rules = [
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'title' => 'nullable|string|max:255',
            'schedule_type' => 'required|in:scheduled,open_access',
            'location_id' => 'nullable|exists:locations,id',
            'capacity' => 'nullable|integer|min:1|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,published',
        ];

        // Scheduled-only validation
        if ($scheduleType === 'scheduled') {
            $rules = array_merge($rules, [
                'start_date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'recurrence_days' => 'required|array|min:1',
                'recurrence_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'recurrence_end_type' => 'required|in:after,on,never',
                'recurrence_count' => 'required_if:recurrence_end_type,after|nullable|integer|min:1|max:52',
                'recurrence_end_date' => 'required_if:recurrence_end_type,on|nullable|date|after:start_date',
                'staff_member_ids' => 'nullable|array',
                'staff_member_ids.*' => 'exists:users,id',
                'instructor_ids' => 'nullable|array',
                'instructor_ids.*' => 'exists:instructors,id',
                'capacity' => 'required|integer|min:1|max:500',
            ]);
        }

        $request->validate($rules);

        $membershipPlan = MembershipPlan::find($request->membership_plan_id);

        // ── Open Access: update the plan, no sessions ──
        if ($scheduleType === 'open_access') {
            $updateData = [
                'schedule_type' => MembershipPlan::SCHEDULE_TYPE_OPEN_ACCESS,
                'has_scheduled_class' => true,
                'status' => in_array($request->input('status'), ['published', 'active']) ? 'active' : 'draft',
            ];

            // Save location to the plan
            if ($request->filled('location_id')) {
                $updateData['location_scope_type'] = 'selected';
                $updateData['location_ids'] = [(int) $request->location_id];
            }

            $updateData['qr_checkin_enabled'] = $request->boolean('qr_checkin_enabled');
            $membershipPlan->update($updateData);

            return redirect()
                ->route('membership-plans.show', $membershipPlan)
                ->with('success', 'Membership plan set to Open Access. Members can now be checked in anytime.');
        }

        // ── Scheduled Sessions: existing logic ──
        $membershipPlan->update([
            'schedule_type' => MembershipPlan::SCHEDULE_TYPE_SCHEDULED,
            'qr_checkin_enabled' => $request->boolean('qr_checkin_enabled'),
        ]);

        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->start_date . ' ' . $request->end_time);
        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

        $allInstructorIds = $this->resolveInstructorIds($host, $request);
        $primaryInstructorId = !empty($allInstructorIds) ? array_shift($allInstructorIds) : null;
        $backupInstructorIds = $allInstructorIds;

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

        $sessionTitle = $request->title ?: $membershipPlan->name . ' Session';

        $session = ClassSession::create([
            'host_id' => $host->id,
            'class_plan_id' => null,
            'primary_instructor_id' => $primaryInstructorId,
            'location_id' => $request->location_id,
            'title' => $sessionTitle,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'duration_minutes' => $durationMinutes,
            'capacity' => $request->capacity,
            'price' => null,
            'status' => $request->status ?? ClassSession::STATUS_DRAFT,
            'recurrence_rule' => $recurrenceRule,
            'notes' => $request->notes,
        ]);

        if (!empty($backupInstructorIds)) {
            $session->syncBackupInstructors($backupInstructorIds);
        }

        $session->membershipPlans()->sync([$request->membership_plan_id]);

        $recurringSession = $this->recurrenceService->createRecurringSessions(
            $session,
            $request->recurrence_days,
            $request->recurrence_end_type,
            $endValue
        );

        $createdCount = 1 + $recurringSession->count();

        foreach ($recurringSession as $recurring) {
            $recurring->membershipPlans()->sync([$request->membership_plan_id]);
            if (!empty($backupInstructorIds)) {
                $recurring->syncBackupInstructors($backupInstructorIds);
            }
        }

        return redirect()
            ->route('class-sessions.index', ['date' => $startDateTime->format('Y-m-d'), 'range' => 'month'])
            ->with('success', "Created {$createdCount} scheduled membership sessions successfully.");
    }
}
