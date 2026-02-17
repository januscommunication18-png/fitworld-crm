<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\Client;
use App\Models\Booking;
use App\Models\ClassPlan;
use App\Models\QuestionnaireAttachment;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\MembershipService;
use App\Services\ClassPackService;
use Illuminate\Http\Request;

class WalkInController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;
    protected MembershipService $membershipService;
    protected ClassPackService $classPackService;

    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        MembershipService $membershipService,
        ClassPackService $classPackService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->membershipService = $membershipService;
        $this->classPackService = $classPackService;
    }

    /**
     * Show session selection page for walk-in booking
     */
    public function selectSession(Request $request)
    {
        $host = auth()->user()->currentHost();
        $date = $request->get('date', now()->format('Y-m-d'));

        // Check if a specific session is being preloaded
        $preloadSession = null;
        if ($request->has('session_id')) {
            $preloadSession = ClassSession::where('host_id', $host->id)
                ->where('id', $request->get('session_id'))
                ->with(['classPlan:id,name,color,default_price'])
                ->first();

            if ($preloadSession) {
                $date = $preloadSession->start_time->format('Y-m-d');
            }
        }

        // Cache class plans for 5 minutes (they rarely change)
        $classPlans = cache()->remember(
            "host.{$host->id}.active_class_plans",
            300,
            fn() => \App\Models\ClassPlan::where('host_id', $host->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'color', 'default_price', 'default_duration_minutes', 'default_capacity'])
        );

        // Get active instructors
        $instructors = \App\Models\Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('host.walk-in.select-session', [
            'selectedDate' => $date,
            'classPlans' => $classPlans,
            'instructors' => $instructors,
            'preloadSession' => $preloadSession,
        ]);
    }

    /**
     * Get sessions by date (AJAX) - Optimized for speed
     */
    public function getSessionsByDate(Request $request)
    {
        $host = auth()->user()->currentHost();
        $date = $request->get('date', now()->format('Y-m-d'));
        $classPlanId = $request->get('class_plan_id');

        $query = ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', $date)
            ->where('status', ClassSession::STATUS_PUBLISHED);

        if ($classPlanId) {
            $query->where('class_plan_id', $classPlanId);
        }

        // Use withCount to avoid N+1 queries for booking counts
        $sessions = $query
            ->select(['id', 'class_plan_id', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time', 'capacity', 'price'])
            ->with([
                'classPlan:id,name,color,default_price',
                'primaryInstructor:id,name',
                'location:id,name'
            ])
            ->withCount(['bookings as active_bookings_count' => function ($q) {
                $q->where('status', '!=', Booking::STATUS_CANCELLED);
            }])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title ?: $session->classPlan?->name,
                    'time' => $session->start_time->format('g:i A') . ' - ' . $session->end_time->format('g:i A'),
                    'start_time_iso' => $session->start_time->toIso8601String(),
                    'end_time_iso' => $session->end_time->toIso8601String(),
                    'instructor' => $session->primaryInstructor?->name ?? 'TBD',
                    'location' => $session->location?->name ?? null,
                    'capacity' => $session->capacity,
                    'booked' => $session->active_bookings_count,
                    'spots_remaining' => $session->capacity - $session->active_bookings_count,
                    'color' => $session->classPlan?->color ?? '#6366f1',
                    'price' => $session->price ?? $session->classPlan?->default_price ?? 0,
                ];
            });

        // If no sessions found, get next available dates (optimized query)
        $nextAvailable = [];
        if ($sessions->isEmpty() && $classPlanId) {
            $nextAvailable = ClassSession::where('host_id', $host->id)
                ->where('class_plan_id', $classPlanId)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>', $date)
                ->selectRaw('DATE(start_time) as session_date, COUNT(*) as session_count')
                ->groupBy('session_date')
                ->orderBy('session_date')
                ->limit(3)
                ->get()
                ->map(function ($row) {
                    return [
                        'date' => $row->session_date,
                        'formatted_date' => \Carbon\Carbon::parse($row->session_date)->format('D, M j'),
                        'session_count' => $row->session_count,
                    ];
                });
        }

        return response()->json([
            'sessions' => $sessions,
            'next_available' => $nextAvailable,
        ]);
    }

    /**
     * Show walk-in booking page for a class session
     */
    public function classSession(ClassSession $classSession)
    {
        $host = auth()->user()->currentHost();

        // Verify session belongs to host
        if ($classSession->host_id !== $host->id) {
            abort(403);
        }

        // Get recent clients
        $recentClients = Client::where('host_id', $host->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get booking count
        $bookedCount = $classSession->bookings()
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->count();

        return view('host.walk-in.class-session', [
            'session' => $classSession,
            'recentClients' => $recentClients,
            'bookedCount' => $bookedCount,
            'spotsRemaining' => $classSession->capacity - $bookedCount,
        ]);
    }

    /**
     * Show walk-in booking page for a service slot
     */
    public function serviceSlot(ServiceSlot $serviceSlot)
    {
        $host = auth()->user()->currentHost();

        // Verify slot belongs to host
        if ($serviceSlot->host_id !== $host->id) {
            abort(403);
        }

        // Get recent clients
        $recentClients = Client::where('host_id', $host->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('host.walk-in.service-slot', [
            'slot' => $serviceSlot,
            'recentClients' => $recentClients,
        ]);
    }

    /**
     * Get payment methods for a client (AJAX)
     */
    public function getPaymentMethods(Request $request, Client $client)
    {
        $host = auth()->user()->currentHost();
        $classPlanId = $request->get('class_plan_id');

        $methods = [
            'membership' => $this->membershipService->getEligibleMembership($client, $classPlanId),
            'packs' => $this->classPackService->getEligiblePacks($client, $classPlanId),
            'manual' => true,
            'comp' => auth()->user()->hasPermission('bookings.comp'),
        ];

        return response()->json($methods);
    }

    /**
     * Process walk-in booking for class session
     */
    public function bookClass(Request $request, ClassSession $classSession)
    {
        $host = auth()->user()->currentHost();

        // Verify session belongs to host
        if ($classSession->host_id !== $host->id) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_method' => 'required|in:membership,pack,manual,comp',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'pack_id' => 'required_if:payment_method,pack|exists:class_pack_purchases,id',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'send_intake_form' => 'boolean',
            'questionnaire_ids' => 'nullable|array',
            'questionnaire_ids.*' => 'exists:questionnaires,id',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        try {
            $booking = $this->bookingService->createWalkInClassBooking(
                host: $host,
                client: $client,
                session: $classSession,
                options: [
                    'payment_method' => $validated['payment_method'],
                    'manual_method' => $validated['manual_method'] ?? null,
                    'price_paid' => $validated['price_paid'] ?? null,
                    'class_pack_purchase_id' => $validated['pack_id'] ?? null,
                    'check_in_now' => $validated['check_in_now'] ?? false,
                    'payment_notes' => $validated['notes'] ?? null,
                    'capacity_override' => true, // Walk-ins can override capacity
                    'send_intake_form' => $validated['send_intake_form'] ?? false,
                    'questionnaire_ids' => $validated['questionnaire_ids'] ?? [],
                    'send_confirmation_email' => !empty($validated['send_intake_form']),
                ]
            );

            return redirect()
                ->route('schedule.calendar')
                ->with('success', "Walk-in booking confirmed for {$client->full_name}!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process walk-in booking for service slot
     */
    public function bookService(Request $request, ServiceSlot $serviceSlot)
    {
        $host = auth()->user()->currentHost();

        // Verify slot belongs to host
        if ($serviceSlot->host_id !== $host->id) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_method' => 'required|in:membership,pack,manual,comp',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        try {
            $booking = $this->bookingService->createWalkInServiceBooking(
                host: $host,
                client: $client,
                slot: $serviceSlot,
                options: [
                    'payment_method' => $validated['payment_method'],
                    'manual_method' => $validated['manual_method'] ?? null,
                    'price_paid' => $validated['price_paid'] ?? null,
                    'check_in_now' => $validated['check_in_now'] ?? false,
                    'payment_notes' => $validated['notes'] ?? null,
                ]
            );

            return redirect()
                ->route('service-slots.index')
                ->with('success', "Walk-in booking confirmed for {$client->full_name}!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Quick add client (AJAX)
     */
    public function quickAddClient(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $host = auth()->user()->currentHost();

        // Check for existing client
        $existingClient = null;
        if (!empty($validated['email'])) {
            $existingClient = Client::where('host_id', $host->id)
                ->where('email', $validated['email'])
                ->first();
        }
        if (!$existingClient && !empty($validated['phone'])) {
            $existingClient = Client::where('host_id', $host->id)
                ->where('phone', $validated['phone'])
                ->first();
        }

        if ($existingClient) {
            return response()->json([
                'success' => true,
                'client' => [
                    'id' => $existingClient->id,
                    'first_name' => $existingClient->first_name,
                    'last_name' => $existingClient->last_name,
                    'email' => $existingClient->email,
                    'phone' => $existingClient->phone,
                    'initials' => $existingClient->initials,
                    'avatar_url' => $existingClient->avatar_url,
                ],
                'existing' => true,
            ]);
        }

        $client = Client::create([
            'host_id' => $host->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'lead',
        ]);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'initials' => $client->initials,
                'avatar_url' => $client->avatar_url,
            ],
            'existing' => false,
        ]);
    }

    /**
     * Search clients (AJAX)
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');
        $host = auth()->user()->currentHost();

        if (strlen($query) < 2) {
            return response()->json(['clients' => []]);
        }

        $clients = Client::where('host_id', $host->id)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'initials' => $client->initials,
                    'avatar_url' => $client->avatar_url,
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    /**
     * Quick create session for walk-in (AJAX)
     */
    public function quickCreateSession(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'class_plan_id' => 'required|exists:class_plans,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'capacity' => 'nullable|integer|min:1|max:100',
            'primary_instructor_id' => 'required|exists:instructors,id',
            'backup_instructor_ids' => 'nullable|array',
            'backup_instructor_ids.*' => 'exists:instructors,id',
        ]);

        $classPlan = \App\Models\ClassPlan::where('host_id', $host->id)
            ->findOrFail($validated['class_plan_id']);

        // Verify instructor belongs to this host
        $primaryInstructor = \App\Models\Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->findOrFail($validated['primary_instructor_id']);

        $startTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);

        // Check if instructor works on this day
        $dayOfWeek = $startTime->dayOfWeek;
        if (!$primaryInstructor->worksOnDay($dayOfWeek)) {
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return response()->json([
                'success' => false,
                'message' => "Instructor does not work on {$dayNames[$dayOfWeek]}s",
            ], 422);
        }
        $durationMinutes = $validated['duration_minutes'] ?? $classPlan->default_duration_minutes ?? 60;
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $session = ClassSession::create([
            'host_id' => $host->id,
            'class_plan_id' => $classPlan->id,
            'primary_instructor_id' => $primaryInstructor->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'capacity' => $validated['capacity'] ?? $classPlan->default_capacity ?? 10,
            'price' => $classPlan->default_price ?? 0,
            'status' => ClassSession::STATUS_PUBLISHED,
        ]);

        // Attach backup instructors if provided
        if (!empty($validated['backup_instructor_ids'])) {
            $backupIds = array_filter($validated['backup_instructor_ids']);
            $backupData = [];
            foreach ($backupIds as $priority => $instructorId) {
                $backupData[$instructorId] = ['priority' => $priority];
            }
            if (!empty($backupData)) {
                $session->backupInstructors()->attach($backupData);
            }
        }

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'title' => $classPlan->name,
                'time' => $session->start_time->format('g:i A') . ' - ' . $session->end_time->format('g:i A'),
                'start_time_iso' => $session->start_time->toIso8601String(),
                'end_time_iso' => $session->end_time->toIso8601String(),
                'instructor' => $primaryInstructor->name,
                'location' => null,
                'capacity' => $session->capacity,
                'booked' => 0,
                'spots_remaining' => $session->capacity,
                'color' => $classPlan->color ?? '#6366f1',
                'price' => $session->price,
            ],
        ]);
    }

    /**
     * Get class plan defaults for quick create (AJAX)
     */
    public function getClassPlanDefaults(Request $request)
    {
        $host = auth()->user()->currentHost();
        $classPlanId = $request->get('class_plan_id');

        $classPlan = \App\Models\ClassPlan::where('host_id', $host->id)
            ->findOrFail($classPlanId);

        return response()->json([
            'duration_minutes' => $classPlan->default_duration_minutes ?? 60,
            'capacity' => $classPlan->default_capacity ?? 10,
            'price' => $classPlan->default_price ?? 0,
        ]);
    }

    /**
     * Get instructor availability for a specific date (AJAX)
     */
    public function getInstructorAvailability(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
        ]);

        $instructor = \App\Models\Instructor::where('host_id', $host->id)
            ->findOrFail($validated['instructor_id']);

        $date = \Carbon\Carbon::parse($validated['date']);
        $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 6=Saturday
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Get availability for the day
        $worksToday = $instructor->worksOnDay($dayOfWeek);
        $availabilityRaw = $instructor->getAvailabilityForDay($dayOfWeek);

        // Format availability times if they exist
        $availability = null;
        if ($availabilityRaw && !empty($availabilityRaw['from']) && !empty($availabilityRaw['to'])) {
            try {
                $fromTime = $availabilityRaw['from'];
                $toTime = $availabilityRaw['to'];

                // Handle both "HH:MM" and "HH:MM:SS" formats
                $availability = [
                    'from' => \Carbon\Carbon::createFromFormat(strlen($fromTime) > 5 ? 'H:i:s' : 'H:i', $fromTime)->format('g:i A'),
                    'to' => \Carbon\Carbon::createFromFormat(strlen($toTime) > 5 ? 'H:i:s' : 'H:i', $toTime)->format('g:i A'),
                ];
            } catch (\Exception $e) {
                // Fallback: just return raw values
                $availability = [
                    'from' => $availabilityRaw['from'],
                    'to' => $availabilityRaw['to'],
                ];
            }
        }

        // Get existing sessions for this instructor on this date
        $existingSessions = ClassSession::where('host_id', $host->id)
            ->where('primary_instructor_id', $instructor->id)
            ->whereDate('start_time', $date)
            ->whereNotIn('status', [ClassSession::STATUS_CANCELLED])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'time' => $session->start_time->format('g:i A') . ' - ' . $session->end_time->format('g:i A'),
                    'title' => $session->title ?: $session->classPlan?->name ?? 'Class Session',
                ];
            });

        // Calculate weekly workload
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $weeklyStats = ClassSession::where('host_id', $host->id)
            ->where('primary_instructor_id', $instructor->id)
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->whereNotIn('status', [ClassSession::STATUS_CANCELLED])
            ->selectRaw('COUNT(*) as class_count, SUM(duration_minutes) as total_minutes')
            ->first();

        $classesThisWeek = $weeklyStats->class_count ?? 0;
        $hoursThisWeek = round(($weeklyStats->total_minutes ?? 0) / 60, 1);

        // Get working days as array of booleans for display
        $workingDaysDisplay = [];
        for ($i = 0; $i < 7; $i++) {
            $workingDaysDisplay[] = $instructor->worksOnDay($i);
        }

        return response()->json([
            'instructor' => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'avatar_url' => $instructor->avatar_url,
                'initials' => strtoupper(substr($instructor->name, 0, 1) . (strpos($instructor->name, ' ') !== false ? substr($instructor->name, strpos($instructor->name, ' ') + 1, 1) : '')),
            ],
            'date' => $date->format('Y-m-d'),
            'formatted_date' => $date->format('l, M j, Y'),
            'day_of_week' => $dayOfWeek,
            'day_name' => $dayNames[$dayOfWeek],
            'works_today' => $worksToday,
            'working_days' => $workingDaysDisplay,
            'availability' => $availability,
            'existing_sessions' => $existingSessions,
            'workload' => [
                'classes_this_week' => $classesThisWeek,
                'max_classes' => $instructor->max_classes_per_week,
                'hours_this_week' => $hoursThisWeek,
                'max_hours' => $instructor->hours_per_week,
            ],
        ]);
    }

    /**
     * Get available time slots for an instructor (AJAX)
     */
    public function getAvailableSlots(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:5|max:480',
        ]);

        $instructor = \App\Models\Instructor::where('host_id', $host->id)
            ->findOrFail($validated['instructor_id']);

        $date = \Carbon\Carbon::parse($validated['date']);
        $duration = (int) $validated['duration'];
        $dayOfWeek = $date->dayOfWeek;

        // Check if instructor works on this day
        if (!$instructor->worksOnDay($dayOfWeek)) {
            return response()->json(['slots' => []]);
        }

        // Get instructor's working hours for this day
        $availability = $instructor->getAvailabilityForDay($dayOfWeek);

        // Default hours if not set (9 AM to 9 PM)
        $startHour = 9;
        $endHour = 21;

        if ($availability && !empty($availability['from']) && !empty($availability['to'])) {
            try {
                $fromTime = $availability['from'];
                $toTime = $availability['to'];

                $startHour = (int) \Carbon\Carbon::createFromFormat(strlen($fromTime) > 5 ? 'H:i:s' : 'H:i', $fromTime)->format('G');
                $endHour = (int) \Carbon\Carbon::createFromFormat(strlen($toTime) > 5 ? 'H:i:s' : 'H:i', $toTime)->format('G');
            } catch (\Exception $e) {
                // Use defaults
            }
        }

        // Get existing sessions for this instructor on this date
        $existingSessions = ClassSession::where('host_id', $host->id)
            ->where('primary_instructor_id', $instructor->id)
            ->whereDate('start_time', $date)
            ->whereNotIn('status', [ClassSession::STATUS_CANCELLED])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        // Generate available slots
        $slots = [];
        $slotInterval = $duration; // Slot interval matches the session duration

        $currentTime = $date->copy()->setTime($startHour, 0, 0);
        $dayEnd = $date->copy()->setTime($endHour, 0, 0);

        // If date is today, start from current time (rounded up to next interval)
        if ($date->isToday()) {
            $now = now();
            if ($now->gt($currentTime)) {
                // Calculate minutes from start of day and round up to next slot interval
                $minutesFromDayStart = $now->hour * 60 + $now->minute;
                $minutesFromWorkStart = $startHour * 60;
                $minutesSinceWorkStart = $minutesFromDayStart - $minutesFromWorkStart;
                $nextSlotIndex = ceil($minutesSinceWorkStart / $slotInterval);
                $nextSlotMinutes = $minutesFromWorkStart + ($nextSlotIndex * $slotInterval);

                $currentTime = $date->copy()->setTime(0, 0, 0)->addMinutes($nextSlotMinutes);
            }
        }

        while ($currentTime->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            // Check for conflicts with existing sessions
            $hasConflict = false;
            foreach ($existingSessions as $session) {
                // Check if this slot overlaps with an existing session
                if ($currentTime->lt($session->end_time) && $slotEnd->gt($session->start_time)) {
                    $hasConflict = true;
                    break;
                }
            }

            if (!$hasConflict) {
                $slots[] = [
                    'time' => $currentTime->format('H:i'),
                    'display' => $currentTime->format('g:i A'),
                ];
            }

            $currentTime->addMinutes($slotInterval);
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Get questionnaires attached to a class plan (AJAX)
     */
    public function getClassPlanQuestionnaires(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'class_plan_id' => 'required|exists:class_plans,id',
        ]);

        // Verify class plan belongs to host
        $classPlan = ClassPlan::where('host_id', $host->id)
            ->findOrFail($validated['class_plan_id']);

        // Get questionnaires attached to this class plan
        $attachments = QuestionnaireAttachment::where('attachable_type', ClassPlan::class)
            ->where('attachable_id', $classPlan->id)
            ->with(['questionnaire:id,name,type,estimated_minutes'])
            ->get()
            ->map(function ($attachment) {
                return [
                    'id' => $attachment->questionnaire_id,
                    'name' => $attachment->questionnaire->name ?? 'Unknown',
                    'type' => $attachment->questionnaire->type ?? 'form',
                    'estimated_duration' => $attachment->questionnaire->estimated_minutes ?? null,
                    'is_required' => $attachment->is_required,
                    'collection_timing' => $attachment->collection_timing,
                    'applies_to' => $attachment->applies_to,
                ];
            });

        return response()->json(['questionnaires' => $attachments]);
    }
}
