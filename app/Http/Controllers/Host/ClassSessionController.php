<?php

namespace App\Http\Controllers\Host;

use App\Events\ClassSessionPublished;
use App\Http\Controllers\Controller;
use App\Http\Requests\Host\ClassSessionRequest;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\Location;
use App\Models\User;
use App\Services\Schedule\ConflictChecker;
use App\Services\Schedule\RecurrenceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    public function __construct(
        protected ConflictChecker $conflictChecker,
        protected RecurrenceService $recurrenceService
    ) {}

    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $range = $request->input('range', 'today'); // 'today', 'week', 'month', or 'all'
        $dateInput = $request->input('date', now()->format('Y-m-d'));

        // Handle month input format (Y-m) by appending -01
        if ($range === 'month' && preg_match('/^\d{4}-\d{2}$/', $dateInput)) {
            $dateInput = $dateInput . '-01';
        }

        $date = $dateInput;

        // Determine date range based on range filter
        $startDate = null;
        $endDate = null;

        if ($range === 'today') {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
        } elseif ($range === 'week') {
            $startDate = Carbon::parse($date)->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
        } elseif ($range === 'month') {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }
        // 'all' - no date filtering

        $query = ClassSession::where('host_id', $host->id)
            ->with(['classPlan', 'primaryInstructor', 'backupInstructors', 'location', 'room']);

        // Apply date range filter if not 'all'
        if ($startDate && $endDate) {
            $query->forDateRange($startDate, $endDate);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_plan_id')) {
            $query->where('class_plan_id', $request->class_plan_id);
        }

        if ($request->filled('instructor_id')) {
            $query->forInstructor($request->instructor_id);
        }

        if ($request->filled('location_id')) {
            $query->forLocation($request->location_id);
        }

        if ($request->boolean('conflicts_only')) {
            $query->withConflicts();
        }

        $sessions = $query->orderBy('start_time')->get();

        // Count unresolved conflicts for banner
        $unresolvedConflictsCount = ClassSession::where('host_id', $host->id)
            ->withConflicts()
            ->count();

        // Group sessions by date
        $sessionsByDate = $sessions->groupBy(function ($session) {
            return $session->start_time->format('Y-m-d');
        })->sortKeys();

        return view('host.class-sessions.index', [
            'sessions' => $sessions,
            'sessionsByDate' => $sessionsByDate,
            'date' => $date,
            'range' => $range,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'classPlans' => $host->classPlans()->active()->orderBy('name')->get(),
            'instructors' => $this->getTeachingInstructors($host),
            'locations' => $host->locations()->orderBy('name')->get(),
            'statuses' => ClassSession::getStatuses(),
            'status' => $request->status,
            'classPlanId' => $request->class_plan_id,
            'instructorId' => $request->instructor_id,
            'locationId' => $request->location_id,
            'unresolvedConflictsCount' => $unresolvedConflictsCount,
        ]);
    }

    public function create(Request $request)
    {
        $host = auth()->user()->host;

        return view('host.class-sessions.create', [
            'classSession' => null,
            'classPlans' => $host->classPlans()->active()->orderBy('name')->get(),
            'instructors' => $this->getTeachingInstructors($host),
            'locations' => $host->locations()->with('rooms')->orderBy('name')->get(),
            'statuses' => ClassSession::getStatuses(),
            'selectedClassPlanId' => $request->class_plan_id,
            'selectedDate' => $request->date ?? now()->format('Y-m-d'),
        ]);
    }

    public function store(ClassSessionRequest $request)
    {
        $host = auth()->user()->host;
        $startTime = $request->getStartTime();
        $endTime = $request->getEndTime();

        // Check for availability warnings that need user acknowledgment
        if ($request->hasAvailabilityWarnings()) {
            return back()
                ->withInput()
                ->with('availability_warnings', $request->getAvailabilityWarnings());
        }

        // Build recurrence rule if recurring
        $recurrenceRule = null;
        if ($request->boolean('is_recurring') && $request->recurrence_days) {
            $endValue = match ($request->recurrence_end_type) {
                'after' => (int) $request->recurrence_count,
                'on' => Carbon::parse($request->recurrence_end_date),
                default => null,
            };

            $recurrenceRule = $this->recurrenceService->buildRecurrenceRule(
                $request->recurrence_days,
                $request->recurrence_end_type ?? 'never',
                $endValue
            );
        }

        // Check if user acknowledged scheduling conflicts
        $hasConflict = $request->boolean('override_availability_warnings');
        $conflictNotes = null;
        if ($hasConflict && !empty($request->availabilityWarnings)) {
            $conflictNotes = collect($request->availabilityWarnings)
                ->pluck('message')
                ->implode('; ');
        }

        // Create the session
        $session = ClassSession::create([
            'host_id' => $host->id,
            'class_plan_id' => $request->class_plan_id,
            'primary_instructor_id' => $request->primary_instructor_id,
            'location_id' => $request->location_id,
            'room_id' => $request->getFirstRoomId(),
            'location_notes' => $request->location_notes,
            'title' => $request->title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $request->duration_minutes,
            'capacity' => $request->capacity,
            'price' => $request->price,
            'status' => ClassSession::STATUS_DRAFT,
            'has_scheduling_conflict' => $hasConflict,
            'conflict_notes' => $conflictNotes,
            'recurrence_rule' => $recurrenceRule,
            'notes' => $request->notes,
        ]);

        // Sync backup instructors
        $backupInstructorIds = array_filter($request->backup_instructor_ids ?? []);
        if (!empty($backupInstructorIds)) {
            $session->syncBackupInstructors($backupInstructorIds);
        }

        // Create recurring sessions if applicable
        $createdCount = 1;
        if ($request->boolean('is_recurring') && $request->recurrence_days) {
            $endValue = match ($request->recurrence_end_type) {
                'after' => (int) $request->recurrence_count,
                'on' => Carbon::parse($request->recurrence_end_date),
                default => null,
            };

            $recurringSession = $this->recurrenceService->createRecurringSessions(
                $session,
                $request->recurrence_days,
                $request->recurrence_end_type ?? 'never',
                $endValue
            );

            $createdCount += $recurringSession->count();
        }

        $message = $createdCount > 1
            ? "Created {$createdCount} class sessions successfully."
            : 'Class session created successfully.';

        return redirect()
            ->route('class-sessions.index', ['date' => $startTime->format('Y-m-d')])
            ->with('success', $message);
    }

    public function show(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        $classSession->load([
            'classPlan',
            'primaryInstructor',
            'backupInstructors',
            'location',
            'room',
            'recurrenceChildren' => fn ($q) => $q->orderBy('start_time'),
            'bookings.client',
            'bookings.questionnaireResponses.version.questionnaire',
            'bookings.questionnaireResponses.answers.question',
        ]);

        // Get booking stats
        $allBookings = $classSession->bookings;
        $confirmedBookings = $allBookings->whereIn('status', ['confirmed', 'completed']);
        $cancelledBookings = $allBookings->where('status', 'cancelled');
        $checkedInCount = $confirmedBookings->filter(fn($b) => $b->isCheckedIn())->count();
        $intakeCompleted = $confirmedBookings->filter(fn($b) => $b->intake_status === 'completed')->count();
        $intakePending = $confirmedBookings->filter(fn($b) => $b->intake_status === 'pending')->count();

        return view('host.class-sessions.show', [
            'classSession' => $classSession,
            'allBookings' => $allBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'checkedInCount' => $checkedInCount,
            'intakeCompleted' => $intakeCompleted,
            'intakePending' => $intakePending,
        ]);
    }

    public function edit(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);
        $host = auth()->user()->host;

        $classSession->load('backupInstructors');

        return view('host.class-sessions.edit', [
            'classSession' => $classSession,
            'classPlans' => $host->classPlans()->active()->orderBy('name')->get(),
            'instructors' => $this->getTeachingInstructors($host),
            'locations' => $host->locations()->with('rooms')->orderBy('name')->get(),
            'statuses' => ClassSession::getStatuses(),
        ]);
    }

    public function update(ClassSessionRequest $request, ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        // Check for availability warnings that need user acknowledgment
        if ($request->hasAvailabilityWarnings()) {
            return back()
                ->withInput()
                ->with('availability_warnings', $request->getAvailabilityWarnings());
        }

        $startTime = $request->getStartTime();
        $endTime = $request->getEndTime();

        $classSession->update([
            'class_plan_id' => $request->class_plan_id,
            'primary_instructor_id' => $request->primary_instructor_id,
            'location_id' => $request->location_id,
            'room_id' => $request->getFirstRoomId(),
            'location_notes' => $request->location_notes,
            'title' => $request->title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $request->duration_minutes,
            'capacity' => $request->capacity,
            'price' => $request->price,
            'status' => $request->status ?? $classSession->status,
            'notes' => $request->notes,
        ]);

        // Sync backup instructors
        $backupInstructorIds = array_filter($request->backup_instructor_ids ?? []);
        $classSession->syncBackupInstructors($backupInstructorIds);

        return redirect()
            ->route('class-sessions.show', $classSession)
            ->with('success', 'Class session updated successfully.');
    }

    public function destroy(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        // Only allow deletion of draft or cancelled sessions
        if ($classSession->isPublished()) {
            return back()->with('error', 'Cannot delete a published session. Cancel it first.');
        }

        $date = $classSession->start_time->format('Y-m-d');
        $classSession->delete();

        return redirect()
            ->route('class-sessions.index', ['date' => $date])
            ->with('success', 'Class session deleted successfully.');
    }

    public function publish(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        if ($classSession->isCancelled()) {
            return back()->with('error', 'Cannot publish a cancelled session.');
        }

        $classSession->publish();

        // Dispatch event for auto-enrollment of scheduled membership holders
        ClassSessionPublished::dispatch($classSession);

        return back()->with('success', 'Session published successfully.');
    }

    public function unpublish(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        if ($classSession->isCancelled()) {
            return back()->with('error', 'Cannot unpublish a cancelled session.');
        }

        $classSession->unpublish();

        return back()->with('success', 'Session unpublished and returned to draft.');
    }

    public function cancel(Request $request, ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $classSession->cancel($request->cancellation_reason);

        return back()->with('success', 'Session cancelled successfully.');
    }

    public function promoteBackup(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        if (!$classSession->hasBackupInstructor()) {
            return back()->with('error', 'No backup instructor assigned to promote.');
        }

        $classSession->promoteBackupToPrimary();

        return back()->with('success', 'Backup instructor promoted to primary.');
    }

    public function duplicate(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        $newSession = $classSession->replicate([
            'status',
            'cancelled_at',
            'cancellation_reason',
            'recurrence_rule',
            'recurrence_parent_id',
        ]);

        $newSession->status = ClassSession::STATUS_DRAFT;
        $newSession->start_time = $classSession->start_time->addWeek();
        $newSession->end_time = $classSession->end_time->addWeek();
        $newSession->save();

        // Copy backup instructors
        $backupInstructorIds = $classSession->backupInstructors->pluck('id')->toArray();
        if (!empty($backupInstructorIds)) {
            $newSession->syncBackupInstructors($backupInstructorIds);
        }

        return redirect()
            ->route('class-sessions.edit', $newSession)
            ->with('success', 'Session duplicated. Please adjust the date and time as needed.');
    }

    /**
     * Resolve a scheduling conflict
     */
    public function resolveConflict(ClassSession $classSession)
    {
        $this->authorizeSession($classSession);

        if (!$classSession->hasUnresolvedConflict()) {
            return back()->with('error', 'This session does not have an unresolved conflict.');
        }

        $classSession->resolveConflict(auth()->id());

        return back()->with('success', 'Conflict marked as resolved.');
    }

    protected function authorizeSession(ClassSession $classSession): void
    {
        if ($classSession->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }

    /**
     * Get all active instructors who can teach classes
     * Includes both instructors with login accounts and those without
     */
    protected function getTeachingInstructors(Host $host)
    {
        return $host->instructors()
            ->active()
            ->orderBy('name')
            ->get();
    }
}
