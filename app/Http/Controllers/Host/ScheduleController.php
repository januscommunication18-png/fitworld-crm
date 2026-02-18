<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    /**
     * Schedule index - redirects to calendar (default view)
     */
    public function index(Request $request)
    {
        return $this->calendar($request);
    }

    /**
     * Calendar View
     */
    public function calendar(Request $request)
    {
        $host = auth()->user()->host;

        // Get data for drawers - current month plus next 2 months
        $startDate = Carbon::now()->startOfMonth()->subWeek();
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();

        $classSessions = ClassSession::where('host_id', $host->id)
            ->with(['classPlan', 'primaryInstructor', 'location', 'room', 'confirmedBookings.client'])
            ->forDateRange($startDate, $endDate)
            ->notCancelled()
            ->orderBy('start_time')
            ->get();

        $serviceSlots = ServiceSlot::where('host_id', $host->id)
            ->with(['servicePlan', 'instructor', 'location', 'room', 'bookings.client'])
            ->forDateRange($startDate, $endDate)
            ->notCancelled()
            ->orderBy('start_time')
            ->get();

        return view('host.schedule.calendar', [
            'locations' => $host->locations()->orderBy('name')->get(),
            'instructors' => $host->instructors()->active()->orderBy('name')->get(),
            'classPlans' => $host->classPlans()->where('is_active', true)->orderBy('name')->get(),
            'servicePlans' => $host->servicePlans()->where('is_active', true)->orderBy('name')->get(),
            'classSessions' => $classSessions,
            'serviceSlots' => $serviceSlots,
            'timezone' => $host->timezone ?? config('app.timezone', 'America/New_York'),
        ]);
    }

    /**
     * List View - 7 day schedule
     */
    public function list(Request $request)
    {
        $host = auth()->user()->host;
        $startDate = Carbon::parse($request->input('start_date', today()));
        $endDate = Carbon::parse($request->input('end_date', today()->addDays(6)));

        // Get class sessions
        $classSessionsQuery = ClassSession::where('host_id', $host->id)
            ->with(['classPlan', 'primaryInstructor', 'location', 'room', 'confirmedBookings'])
            ->forDateRange($startDate->startOfDay(), $endDate->endOfDay())
            ->notCancelled();

        // Get service slots
        $serviceSlotsQuery = ServiceSlot::where('host_id', $host->id)
            ->with(['servicePlan', 'instructor', 'location', 'room', 'bookings'])
            ->forDateRange($startDate->startOfDay(), $endDate->endOfDay())
            ->notCancelled();

        // Apply filters
        if ($request->filled('location_id')) {
            $classSessionsQuery->where('location_id', $request->location_id);
            $serviceSlotsQuery->where('location_id', $request->location_id);
        }

        if ($request->filled('instructor_id')) {
            $classSessionsQuery->forInstructor($request->instructor_id);
            $serviceSlotsQuery->forInstructor($request->instructor_id);
        }

        if ($request->filled('status')) {
            $classSessionsQuery->where('status', $request->status);
            $serviceSlotsQuery->where('status', $request->status);
        }

        // Type filter
        $type = $request->input('type', 'all');
        $classSessions = collect();
        $serviceSlots = collect();

        if ($type === 'all' || $type === 'class') {
            $classSessions = $classSessionsQuery->orderBy('start_time')->get();
        }

        if ($type === 'all' || $type === 'service') {
            $serviceSlots = $serviceSlotsQuery->orderBy('start_time')->get();
        }

        // Combine and group by date
        $scheduleByDate = collect();

        foreach ($classSessions as $session) {
            $dateKey = $session->start_time->format('Y-m-d');
            if (!$scheduleByDate->has($dateKey)) {
                $scheduleByDate[$dateKey] = collect();
            }
            $scheduleByDate[$dateKey]->push([
                'type' => 'class',
                'item' => $session,
                'start_time' => $session->start_time,
            ]);
        }

        foreach ($serviceSlots as $slot) {
            $dateKey = $slot->start_time->format('Y-m-d');
            if (!$scheduleByDate->has($dateKey)) {
                $scheduleByDate[$dateKey] = collect();
            }
            $scheduleByDate[$dateKey]->push([
                'type' => 'service',
                'item' => $slot,
                'start_time' => $slot->start_time,
            ]);
        }

        // Sort by date and time
        $scheduleByDate = $scheduleByDate->sortKeys()->map(function ($items) {
            return $items->sortBy('start_time')->values();
        });

        return view('host.schedule.list', [
            'scheduleByDate' => $scheduleByDate,
            'classSessions' => $classSessions,
            'serviceSlots' => $serviceSlots,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'locations' => $host->locations()->orderBy('name')->get(),
            'instructors' => $host->instructors()->active()->orderBy('name')->get(),
            'classStatuses' => ClassSession::getStatuses(),
            'serviceStatuses' => ServiceSlot::getStatuses(),
            'filters' => [
                'location_id' => $request->location_id,
                'instructor_id' => $request->instructor_id,
                'type' => $type,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * AJAX endpoint for calendar events
     */
    public function events(Request $request): JsonResponse
    {
        $host = auth()->user()->host;
        $hostTimezone = $host->timezone ?? config('app.timezone', 'America/New_York');
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));
        $type = $request->input('type', 'all');

        $events = [];

        // Get class sessions
        if ($type === 'all' || $type === 'class') {
            $classSessionsQuery = ClassSession::where('host_id', $host->id)
                ->with(['classPlan', 'primaryInstructor', 'location', 'confirmedBookings'])
                ->forDateRange($start, $end)
                ->notCancelled();

            if ($request->filled('instructor_id')) {
                $classSessionsQuery->forInstructor($request->instructor_id);
            }

            if ($request->filled('location_id')) {
                $classSessionsQuery->where('location_id', $request->location_id);
            }

            if ($request->filled('class_plan_id')) {
                $classSessionsQuery->where('class_plan_id', $request->class_plan_id);
            }

            foreach ($classSessionsQuery->get() as $session) {
                $confirmedBookings = $session->confirmedBookings;
                $checkedInCount = $confirmedBookings->filter(fn($b) => $b->checked_in_at !== null)->count();
                $cancelledCount = $session->bookings()->where('status', 'cancelled')->count();

                // Times in DB are stored as local times (host timezone), not UTC
                // Just format them without timezone conversion
                $events[] = [
                    'id' => 'class_' . $session->id,
                    'title' => $session->display_title,
                    'start' => $session->start_time->format('Y-m-d\TH:i:s'),
                    'end' => $session->end_time->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $session->status === ClassSession::STATUS_DRAFT ? '#f59e0b' : '#6366f1',
                    'borderColor' => $session->status === ClassSession::STATUS_DRAFT ? '#f59e0b' : '#6366f1',
                    'extendedProps' => [
                        'type' => 'class',
                        'instructor' => $session->primaryInstructor?->name ?? 'TBD',
                        'location' => $session->location?->name ?? 'TBD',
                        'capacity' => $session->getEffectiveCapacity(),
                        'booked' => $confirmedBookings->count(),
                        'checkedIn' => $checkedInCount,
                        'cancelled' => $cancelledCount,
                        'status' => $session->status,
                    ],
                ];
            }
        }

        // Get service slots
        if ($type === 'all' || $type === 'service') {
            $serviceSlotsQuery = ServiceSlot::where('host_id', $host->id)
                ->with(['servicePlan', 'instructor', 'location'])
                ->forDateRange($start, $end)
                ->notCancelled();

            if ($request->filled('instructor_id')) {
                $serviceSlotsQuery->forInstructor($request->instructor_id);
            }

            if ($request->filled('location_id')) {
                $serviceSlotsQuery->where('location_id', $request->location_id);
            }

            if ($request->filled('service_plan_id')) {
                $serviceSlotsQuery->where('service_plan_id', $request->service_plan_id);
            }

            foreach ($serviceSlotsQuery->get() as $slot) {
                // Times in DB are stored as local times (host timezone), not UTC
                // Just format them without timezone conversion
                $events[] = [
                    'id' => 'service_' . $slot->id,
                    'title' => $slot->servicePlan?->name ?? 'Service',
                    'start' => $slot->start_time->format('Y-m-d\TH:i:s'),
                    'end' => $slot->end_time->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $slot->status === ServiceSlot::STATUS_DRAFT ? '#f59e0b' : '#10b981',
                    'borderColor' => $slot->status === ServiceSlot::STATUS_DRAFT ? '#f59e0b' : '#10b981',
                    'extendedProps' => [
                        'type' => 'service',
                        'instructor' => $slot->instructor?->name ?? 'TBD',
                        'location' => $slot->location?->name ?? 'TBD',
                        'status' => $slot->status,
                        'isBooked' => $slot->status === ServiceSlot::STATUS_BOOKED,
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * AJAX endpoint for check-in
     */
    public function checkIn(Request $request, Booking $booking): JsonResponse
    {
        // Verify the booking belongs to the current host
        if ($booking->host_id !== auth()->user()->host_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if already checked in
        if ($booking->isCheckedIn()) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in',
            ], 400);
        }

        // Check in the booking
        $booking->checkIn(auth()->id(), Booking::CHECKIN_STAFF);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully',
            'checked_in_at' => $booking->fresh()->checked_in_at->format('g:i A'),
        ]);
    }

    /**
     * AJAX endpoint for marking class session complete
     */
    public function markComplete(Request $request, ClassSession $classSession): JsonResponse
    {
        // Verify the class session belongs to the current host
        if ($classSession->host_id !== auth()->user()->host_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if already completed or cancelled
        if ($classSession->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Already marked as completed',
            ], 400);
        }

        if ($classSession->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete a cancelled session',
            ], 400);
        }

        // Mark as complete
        $classSession->markComplete();

        return response()->json([
            'success' => true,
            'message' => 'Session marked as completed',
            'status' => 'completed',
        ]);
    }

    /**
     * Requests placeholder (coming soon)
     */
    public function requests()
    {
        return view('host.schedule.requests');
    }

    /**
     * Waitlist placeholder (coming soon)
     */
    public function waitlist()
    {
        return view('host.schedule.waitlist');
    }
}
