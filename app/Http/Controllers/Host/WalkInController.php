<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\Client;
use App\Models\Booking;
use App\Models\ClassPlan;
use App\Models\ServicePlan;
use App\Models\ClassPass;
use App\Models\MembershipPlan;
use App\Models\CustomerMembership;
use App\Models\Event;
use App\Models\Instructor;
use App\Models\Offer;
use App\Models\QuestionnaireAttachment;
use App\Rules\ValidName;
use App\Services\BookingService;
use App\Services\OfferService;
use App\Services\PaymentService;
use App\Services\MembershipService;
use App\Services\ClassPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalkInController extends Controller
{
    protected BookingService $bookingService;
    protected OfferService $offerService;
    protected PaymentService $paymentService;
    protected MembershipService $membershipService;
    protected ClassPassService $classPassService;

    public function __construct(
        BookingService $bookingService,
        OfferService $offerService,
        PaymentService $paymentService,
        MembershipService $membershipService,
        ClassPassService $classPassService
    ) {
        $this->bookingService = $bookingService;
        $this->offerService = $offerService;
        $this->paymentService = $paymentService;
        $this->membershipService = $membershipService;
        $this->classPassService = $classPassService;
    }

    /**
     * Show session selection page for walk-in booking
     */
    public function selectSession(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost();
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

        // Price override permissions - only if feature is enabled
        $hasFeature = $host->hasFeature('price-override');
        $canOverridePrice = $hasFeature && $user->canApprovePriceOverride($host);
        $canRequestOverride = $hasFeature && !$user->canApprovePriceOverride($host);

        return view('host.walk-in.select-session', [
            'selectedDate' => $date,
            'classPlans' => $classPlans,
            'instructors' => $instructors,
            'preloadSession' => $preloadSession,
            'canOverridePrice' => $canOverridePrice,
            'canRequestOverride' => $canRequestOverride,
            'host' => $host,
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
                'classPlan:id,name,color,default_price,billing_discounts,registration_fee,cancellation_fee,cancellation_grace_hours',
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
                    'price' => (float) $session->price > 0 ? $session->price : ($session->classPlan?->default_price ?? 0),
                    'billing_discounts' => $session->classPlan?->billing_discounts ?? null,
                    'registration_fee' => (float) ($session->classPlan?->registration_fee ?? 0),
                    'cancellation_fee' => (float) ($session->classPlan?->cancellation_fee ?? 0),
                    'cancellation_grace_hours' => $session->classPlan?->cancellation_grace_hours ?? 48,
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
     * Get sessions for a date range (for billing period bookings)
     */
    public function getSessionsByDateRange(Request $request)
    {
        $host = auth()->user()->currentHost();
        $classPlanId = $request->get('class_plan_id');
        $months = (int) $request->get('months', 1);

        if (!$classPlanId) {
            return response()->json(['sessions' => []]);
        }

        $startDate = now();
        $endDate = now()->addMonths($months);

        $sessions = ClassSession::where('host_id', $host->id)
            ->where('class_plan_id', $classPlanId)
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->select(['id', 'class_plan_id', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time', 'capacity', 'price', 'recurrence_parent_id'])
            ->with([
                'classPlan:id,name,color,default_price,billing_discounts,registration_fee,cancellation_fee,cancellation_grace_hours',
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
                    'date' => $session->start_time->format('D, M d'),
                    'time' => $session->start_time->format('g:i A') . ' - ' . $session->end_time->format('g:i A'),
                    'start_time_iso' => $session->start_time->toIso8601String(),
                    'end_time_iso' => $session->end_time->toIso8601String(),
                    'instructor' => $session->primaryInstructor?->name ?? 'TBD',
                    'location' => $session->location?->name ?? null,
                    'capacity' => $session->capacity,
                    'booked' => $session->active_bookings_count,
                    'spots_remaining' => $session->capacity - $session->active_bookings_count,
                    'color' => $session->classPlan?->color ?? '#6366f1',
                    'price' => (float) $session->price > 0 ? $session->price : ($session->classPlan?->default_price ?? 0),
                    'billing_discounts' => $session->classPlan?->billing_discounts ?? null,
                    'registration_fee' => (float) ($session->classPlan?->registration_fee ?? 0),
                    'cancellation_fee' => (float) ($session->classPlan?->cancellation_fee ?? 0),
                    'cancellation_grace_hours' => $session->classPlan?->cancellation_grace_hours ?? 48,
                    'recurrence_parent_id' => $session->recurrence_parent_id,
                ];
            });

        return response()->json([
            'sessions' => $sessions,
            'total' => $sessions->count(),
            'period_start' => $startDate->format('M d, Y'),
            'period_end' => $endDate->format('M d, Y'),
        ]);
    }

    /**
     * Get schedule options for a class plan (for series booking schedule picker)
     */
    public function getClassSchedules(Request $request)
    {
        $host = auth()->user()->currentHost();
        $classPlanId = $request->get('class_plan_id');
        $months = (int) $request->get('months', 1);

        if (!$classPlanId) {
            return response()->json(['schedules' => []]);
        }

        $startDate = now();
        $endDate = now()->addMonths($months);

        // Fetch all published sessions in range
        $sessions = ClassSession::where('host_id', $host->id)
            ->where('class_plan_id', $classPlanId)
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->select(['id', 'recurrence_parent_id', 'recurrence_rule', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time'])
            ->with(['primaryInstructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        // Group by recurrence_parent_id
        $grouped = $sessions->groupBy(function ($session) {
            return $session->recurrence_parent_id ?? ($session->recurrence_rule ? $session->id : 'oneoff');
        });

        // Load parent sessions for groups
        $parentIds = $grouped->keys()->filter(fn($k) => is_numeric($k))->values()->toArray();
        $parents = ClassSession::whereIn('id', $parentIds)
            ->select(['id', 'recurrence_rule', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time'])
            ->with(['primaryInstructor:id,name', 'location:id,name'])
            ->get()
            ->keyBy('id');

        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

        $schedules = [];
        foreach ($grouped as $key => $groupSessions) {
            if ($groupSessions->isEmpty()) continue;

            $first = $groupSessions->first();
            $parent = is_numeric($key) ? ($parents->get($key) ?? $first) : $first;

            // Build day label from recurrence_rule
            $label = 'One-off Sessions';
            if ($key !== 'oneoff' && $parent->recurrence_rule) {
                $recurrenceService = app(\App\Services\Schedule\RecurrenceService::class);
                $parsed = $recurrenceService->parseRecurrenceRule($parent->recurrence_rule);
                if (!empty($parsed['days_of_week'])) {
                    $label = collect($parsed['days_of_week'])
                        ->map(fn($d) => $dayNames[(int) $d] ?? $d)
                        ->implode(', ');
                }
            } elseif ($key !== 'oneoff') {
                // Parent session without rule — use day of week
                $label = $parent->start_time->format('l');
            }

            $lastSession = $groupSessions->last();

            $schedules[] = [
                'parent_id' => $key,
                'title' => $parent->title ?? null,
                'label' => $label,
                'time' => $parent->start_time->format('g:i A') . ' - ' . $parent->end_time->format('g:i A'),
                'instructor' => $parent->primaryInstructor?->name ?? 'TBD',
                'location' => $parent->location?->name ?? null,
                'last_session_date' => $lastSession?->start_time->format('M d, Y') ?? null,
                'session_count' => $groupSessions->count(),
                'session_ids' => $groupSessions->pluck('id')->toArray(),
            ];
        }

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * Check if a client already has bookings for sessions in a series
     */
    public function checkSeriesConflict(Request $request)
    {
        $host = auth()->user()->currentHost();
        $clientId = $request->get('client_id');
        $sessionIds = array_filter(explode(',', $request->get('session_ids', '')));

        if (!$clientId || empty($sessionIds)) {
            return response()->json(['has_conflict' => false]);
        }

        $client = Client::where('id', $clientId)->where('host_id', $host->id)->first();
        if (!$client) {
            return response()->json(['has_conflict' => false]);
        }

        $existingCount = Booking::where('client_id', $client->id)
            ->where('bookable_type', ClassSession::class)
            ->whereIn('bookable_id', $sessionIds)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->count();

        if ($existingCount > 0) {
            $totalSessions = count($sessionIds);
            $newSessions = $totalSessions - $existingCount;
            return response()->json([
                'has_conflict' => true,
                'existing_count' => $existingCount,
                'total_sessions' => $totalSessions,
                'new_sessions' => $newSessions,
                'client_name' => $client->full_name,
                'message' => $newSessions <= 0
                    ? "{$client->full_name} is already booked into all {$existingCount} session(s) in this schedule."
                    : "{$client->full_name} is already booked into {$existingCount} of {$totalSessions} session(s).",
            ]);
        }

        return response()->json(['has_conflict' => false]);
    }

    /**
     * Show walk-in booking page for a class session
     */
    public function classSession(ClassSession $classSession)
    {
        $user = auth()->user();
        $host = $user->currentHost();

        // Verify session belongs to host
        if ($classSession->host_id !== $host->id) {
            abort(403);
        }

        // Load classPlan and host relationships for pricing display
        $classSession->load(['classPlan', 'host']);

        // Get recent clients
        $recentClients = Client::where('host_id', $host->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get booking count
        $bookedCount = $classSession->bookings()
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->count();

        // Price override permissions - only if feature is enabled
        $hasFeature = $host->hasFeature('price-override');
        $canOverridePrice = $hasFeature && $user->canApprovePriceOverride($host);
        $canRequestOverride = $hasFeature && !$user->canApprovePriceOverride($host);

        return view('host.walk-in.class-session', [
            'session' => $classSession,
            'recentClients' => $recentClients,
            'bookedCount' => $bookedCount,
            'spotsRemaining' => $classSession->capacity - $bookedCount,
            'host' => $host,
            'canOverridePrice' => $canOverridePrice,
            'canRequestOverride' => $canRequestOverride,
        ]);
    }

    /**
     * Show walk-in booking page for a service slot
     */
    public function serviceSlot(ServiceSlot $serviceSlot)
    {
        $user = auth()->user();
        $host = $user->currentHost();

        // Verify slot belongs to host
        if ($serviceSlot->host_id !== $host->id) {
            abort(403);
        }

        // Get recent clients
        $recentClients = Client::where('host_id', $host->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Price override permissions - only if feature is enabled
        $hasFeature = $host->hasFeature('price-override');
        $canOverridePrice = $hasFeature && $user->canApprovePriceOverride($host);
        $canRequestOverride = $hasFeature && !$user->canApprovePriceOverride($host);

        return view('host.walk-in.service-slot', [
            'slot' => $serviceSlot,
            'recentClients' => $recentClients,
            'host' => $host,
            'canOverridePrice' => $canOverridePrice,
            'canRequestOverride' => $canRequestOverride,
        ]);
    }

    /**
     * Validate promo code (AJAX)
     */
    public function validatePromoCode(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'nullable|string|in:classes,services,memberships,class_packs,all',
            'original_price' => 'required|numeric|min:0',
            'client_id' => 'nullable|integer|exists:clients,id',
        ]);

        $client = null;
        if (!empty($validated['client_id'])) {
            $client = Client::where('host_id', $host->id)
                ->where('id', $validated['client_id'])
                ->first();
        }

        $result = $this->offerService->validatePromoCode(
            $host,
            $validated['code'],
            $client,
            $validated['type'] ?? null,
            $validated['original_price']
        );

        if (!$result['valid']) {
            return response()->json([
                'valid' => false,
                'error' => $result['error'],
            ]);
        }

        $offer = $result['offer'];
        $discountAmount = $result['discount_amount'];
        $finalPrice = max(0, $validated['original_price'] - $discountAmount);

        return response()->json([
            'valid' => true,
            'offer_id' => $offer->id,
            'offer_name' => $offer->name,
            'discount_display' => $result['discount_display'],
            'discount_amount' => $discountAmount,
            'original_price' => $validated['original_price'],
            'final_price' => $finalPrice,
        ]);
    }

    /**
     * Get applicable promo codes for walk-in booking (AJAX)
     */
    public function getApplicableOffers(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'type' => 'nullable|string|in:classes,services,memberships,class_packs,all',
            'original_price' => 'nullable|numeric|min:0',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        // Client is required - only show offers for the selected client
        $client = Client::where('host_id', $host->id)
            ->where('id', $validated['client_id'])
            ->first();

        if (!$client) {
            return response()->json([
                'offers' => [],
                'message' => 'Client not found',
            ]);
        }

        $appliesTo = $validated['type'] ?? null;
        $originalPrice = $validated['original_price'] ?? 0;

        // Get all active offers that can be used at front desk
        $offers = Offer::where('host_id', $host->id)
            ->where('status', 'active')
            ->where(function ($query) {
                // Allow front desk use - not online only, not app only
                $query->where('online_booking_only', false)
                    ->orWhereNull('online_booking_only');
            })
            ->where(function ($query) {
                $query->where('app_only', false)
                    ->orWhereNull('app_only');
            })
            ->where(function ($query) {
                // Date validity
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) {
                // Usage limit not reached
                $query->whereNull('total_usage_limit')
                    ->orWhereColumn('total_redemptions', '<', 'total_usage_limit');
            })
            ->orderBy('name')
            ->get();

        $applicableOffers = [];

        foreach ($offers as $offer) {
            // Check if offer applies to this type
            if ($appliesTo && $offer->applies_to !== 'all' && $offer->applies_to !== $appliesTo) {
                continue;
            }

            // Check client eligibility - this is now required
            if (!$this->offerService->isClientEligible($offer, $client)) {
                continue;
            }

            // Calculate discount for display
            $discountAmount = $this->offerService->calculateDiscount($offer, $originalPrice);

            $applicableOffers[] = [
                'id' => $offer->id,
                'name' => $offer->name,
                'code' => $offer->code,
                'discount_display' => $offer->getFormattedDiscount(),
                'discount_amount' => $discountAmount,
                'final_price' => max(0, $originalPrice - $discountAmount),
                'description' => $offer->description,
                'auto_apply' => $offer->auto_apply,
                'require_code' => $offer->require_code,
            ];
        }

        return response()->json([
            'offers' => $applicableOffers,
            'client_name' => $client->full_name,
        ]);
    }

    /**
     * Get payment methods for a client (AJAX)
     */
    public function getPaymentMethods(Request $request, int $client_id)
    {
        $host = auth()->user()->currentHost();
        $client = Client::where('id', $client_id)->where('host_id', $host->id)->firstOrFail();
        $classPlanId = $request->get('class_plan_id');

        // Look up the class plan for membership eligibility check
        $classPlan = $classPlanId ? \App\Models\ClassPlan::find($classPlanId) : null;

        // Get eligible membership (needs ClassPlan object)
        $membership = null;
        if ($classPlan) {
            $eligibleMembership = $this->membershipService->getEligibleMembershipForClass($client, $classPlan);
            if ($eligibleMembership) {
                $membership = [
                    'id' => $eligibleMembership->id,
                    'name' => $eligibleMembership->membershipPlan->name,
                    'credits_remaining' => $eligibleMembership->credits_remaining,
                ];
            }
        }

        // Get active billing credits for this client, filtered by context
        $sourceType = $classPlanId ? 'class_plan' : $request->get('source_type');
        $billingCreditsQuery = \App\Models\BillingCredit::where('host_id', $host->id)
            ->where('client_id', $client->id)
            ->active();

        if ($sourceType) {
            $billingCreditsQuery->where('source_type', $sourceType);
        }

        $billingCredits = $billingCreditsQuery->get()
            ->map(function ($credit) {
                return [
                    'id' => $credit->id,
                    'source_type' => $credit->source_type,
                    'source_name' => $credit->getSourceName(),
                    'billing_period' => $credit->billing_period,
                    'discount_percent' => $credit->discount_percent,
                    'credit_remaining' => (float) $credit->credit_remaining,
                    'monthly_rate' => (float) $credit->monthly_rate,
                    'end_date' => $credit->end_date->format('M d, Y'),
                    'days_left' => now()->diffInDays($credit->end_date, false),
                ];
            });

        // Get eligible packs and calculate credits required
        $packs = $this->classPassService->getEligiblePasses($client, $classPlanId);

        // If a specific session is provided, calculate credits required per pack
        $sessionId = $request->get('session_id');
        if ($sessionId) {
            $session = ClassSession::where('host_id', $host->id)->find($sessionId);
            if ($session) {
                foreach ($packs as &$pack) {
                    $purchase = \App\Models\ClassPassPurchase::with('classPass')->find($pack['id']);
                    if ($purchase && $purchase->classPass) {
                        $pack['credits_required'] = $purchase->classPass->calculateCreditsForSession($session);
                    } else {
                        $pack['credits_required'] = 1;
                    }
                }
                unset($pack);
            }
        } else {
            // Default: use default_credits_per_class from each pack's class pass
            foreach ($packs as &$pack) {
                $purchase = \App\Models\ClassPassPurchase::with('classPass')->find($pack['id']);
                $pack['credits_required'] = $purchase?->classPass?->default_credits_per_class ?? 1;
            }
            unset($pack);
        }

        $methods = [
            'membership' => $membership,
            'packs' => $packs,
            'billing_credits' => $billingCredits,
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
            'payment_method' => 'required|in:membership,pack,manual,comp,billing_credit',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'pack_id' => 'nullable|required_if:payment_method,pack|exists:class_pass_purchases,id',
            'billing_credit_id' => 'nullable|integer|exists:billing_credits,id',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'send_intake_form' => 'boolean',
            'questionnaire_ids' => 'nullable|array',
            'questionnaire_ids.*' => 'exists:questionnaires,id',
            'offer_id' => 'nullable|integer|exists:offers,id',
            'promo_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'price_override_code' => 'nullable|string|max:20',
            'price_override_amount' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|integer|in:1,3,6,9,12',
            'billing_discount_percent' => 'nullable|numeric|min:0',
            'include_registration_fee' => 'nullable|in:0,1',
            'booking_type' => 'nullable|in:single,period,trial',
            'series_session_ids' => 'nullable|string',
            'credits_to_deduct' => 'nullable|integer|min:1|max:99',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Handle offer/discount
        $offer = null;
        $discountAmount = 0;
        $originalPrice = $classSession->price ?? $classSession->classPlan?->default_price ?? 0;
        $priceOverrideRequest = null;

        // Handle price override (takes precedence over promo codes)
        if (!empty($validated['price_override_code'])) {
            $overrideCode = strtoupper($validated['price_override_code']);
            $priceOverrideService = app(\App\Services\PriceOverrideService::class);

            // Check if this is a direct override (manager/owner editing price directly)
            if ($overrideCode === 'DIRECT' && !empty($validated['price_override_amount'])) {
                $overridePrice = floatval($validated['price_override_amount']);
                $discountAmount = $originalPrice - $overridePrice;

                // Log the direct override
                $priceOverrideRequest = $priceOverrideService->logDirectOverride([
                    'host_id' => $host->id,
                    'location_id' => $classSession->location_id ?? null,
                    'user_id' => auth()->id(),
                    'bookable_type' => get_class($classSession),
                    'bookable_id' => $classSession->id,
                    'client_id' => $client->id,
                    'original_price' => $originalPrice,
                    'new_price' => $overridePrice,
                    'metadata' => [
                        'class_name' => $classSession->classPlan?->name ?? $classSession->title ?? 'Class',
                    ],
                ]);

                // Update the price_paid to reflect the override price
                $validated['price_paid'] = $overridePrice;
            }
            // Check if this is a personal code (MY-XXXXX)
            elseif ($priceOverrideService->isPersonalCode($overrideCode)) {
                // Verify the personal code
                $codeOwner = $priceOverrideService->verifyPersonalCode($overrideCode, $host);

                if ($codeOwner && !empty($validated['price_override_amount'])) {
                    $overridePrice = floatval($validated['price_override_amount']);
                    $discountAmount = $originalPrice - $overridePrice;

                    // Log the personal override
                    $priceOverrideRequest = $priceOverrideService->logPersonalOverride([
                        'host_id' => $host->id,
                        'location_id' => $classSession->location_id ?? null,
                        'requested_by' => auth()->id(),
                        'personal_code' => $overrideCode,
                        'bookable_type' => get_class($classSession),
                        'bookable_id' => $classSession->id,
                        'client_id' => $client->id,
                        'original_price' => $originalPrice,
                        'new_price' => $overridePrice,
                    ]);

                    // Update the price_paid to reflect the override price
                    $validated['price_paid'] = $overridePrice;
                }
            } else {
                // Regular override request code (PO-XXXXX)
                $priceOverrideRequest = \App\Models\PriceOverrideRequest::where('host_id', $host->id)
                    ->where('confirmation_code', $overrideCode)
                    ->where('status', \App\Models\PriceOverrideRequest::STATUS_APPROVED)
                    ->first();

                if ($priceOverrideRequest) {
                    $discountAmount = $priceOverrideRequest->discount_amount;
                    // Update the price_paid to reflect the override price
                    if (isset($validated['price_paid'])) {
                        $validated['price_paid'] = $priceOverrideRequest->requested_price;
                    }
                }
            }
        }

        // Only process promo code offer if no price override was applied
        if (!$priceOverrideRequest && !empty($validated['offer_id'])) {
            $offer = \App\Models\Offer::where('id', $validated['offer_id'])
                ->where('host_id', $host->id)
                ->first();

            if ($offer) {
                $validation = $this->offerService->validateOffer($offer, $client, 'classes', $originalPrice);
                if ($validation['valid']) {
                    $discountAmount = $validation['discount_amount'];
                }
            }
        }

        // For series bookings, check if ALL sessions are already booked (block only if nothing new to add)
        $bookingType = $validated['booking_type'] ?? 'single';
        if ($bookingType === 'period' && !empty($validated['series_session_ids'])) {
            $sessionIds = array_filter(explode(',', $validated['series_session_ids']));
            $existingCount = Booking::where('client_id', $client->id)
                ->where('bookable_type', ClassSession::class)
                ->whereIn('bookable_id', $sessionIds)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->count();

            if ($existingCount >= count($sessionIds)) {
                return back()
                    ->withInput()
                    ->with('error', "{$client->full_name} is already booked into all sessions in this schedule.");
            }
        }

        try {
            $booking = $this->bookingService->createWalkInClassBooking(
                host: $host,
                client: $client,
                session: $classSession,
                options: [
                    'payment_method' => $validated['payment_method'],
                    'manual_method' => $validated['manual_method'] ?? null,
                    'price_paid' => $validated['price_paid'] ?? null,
                    'class_pass_purchase_id' => $validated['pack_id'] ?? null,
                    'credits_to_deduct' => $validated['credits_to_deduct'] ?? null,
                    'check_in_now' => $validated['check_in_now'] ?? false,
                    'payment_notes' => $validated['notes'] ?? null,
                    'capacity_override' => true, // Walk-ins can override capacity
                    'send_intake_form' => $validated['send_intake_form'] ?? false,
                    'questionnaire_ids' => $validated['questionnaire_ids'] ?? [],
                    'send_confirmation_email' => !empty($validated['send_intake_form']),
                ]
            );

            // Record offer redemption if applicable
            if ($offer && $discountAmount > 0) {
                $this->offerService->recordRedemption(
                    $offer,
                    $client,
                    $originalPrice,
                    $discountAmount,
                    'front_desk',
                    $validated['promo_code'] ?? null,
                    auth()->id(),
                    get_class($booking),
                    $booking->id
                );
            }

            // Record price override usage if applicable
            if ($priceOverrideRequest) {
                $metadata = $priceOverrideRequest->metadata ?? [];
                $metadata['applied_at'] = now()->toIso8601String();
                $metadata['booking_id'] = $booking->id;
                $metadata['booking_type'] = get_class($booking);
                $priceOverrideRequest->update(['metadata' => $metadata]);
            }

            // Create billing credit if a billing period was selected
            if (!empty($validated['billing_period']) && !empty($validated['billing_discount_percent'])) {
                $billingPeriod = (int) $validated['billing_period'];
                $totalAmount = (float) $validated['billing_discount_percent']; // Total amount for the entire period
                $classPlan = $classSession->classPlan;
                $baseMonthly = (float) ($classPlan?->default_price ?? $originalPrice);
                $monthlyRate = $billingPeriod > 0 ? $totalAmount / $billingPeriod : 0;
                $totalWithout = $baseMonthly * $billingPeriod;
                $discountPct = $totalWithout > 0 ? round((1 - $totalAmount / $totalWithout) * 100, 2) : 0;
                $includeRegFee = ($validated['include_registration_fee'] ?? '1') === '1';
                $registrationFee = $includeRegFee ? (float) ($classPlan?->registration_fee ?? 0) : 0;

                \App\Models\BillingCredit::create([
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'source_type' => 'class_plan',
                    'source_id' => $classPlan?->id ?? 0,
                    'booking_id' => $booking->id,
                    'billing_period' => $billingPeriod,
                    'discount_percent' => $discountPct,
                    'amount_paid' => $totalAmount,
                    'monthly_rate' => $monthlyRate,
                    'original_monthly_rate' => $baseMonthly,
                    'credit_remaining' => $totalAmount,
                    'registration_fee_paid' => $registrationFee,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths($billingPeriod)->toDateString(),
                    'status' => \App\Models\BillingCredit::STATUS_ACTIVE,
                    'created_by' => auth()->id(),
                ]);
            }

            // Deduct from billing credit if used as payment method
            if ($validated['payment_method'] === 'billing_credit' && !empty($validated['billing_credit_id'])) {
                $billingCredit = \App\Models\BillingCredit::where('id', $validated['billing_credit_id'])
                    ->where('host_id', $host->id)
                    ->where('client_id', $client->id)
                    ->first();

                if ($billingCredit && $billingCredit->isUsable()) {
                    $billingCredit->deduct($originalPrice);
                    $booking->update(['billing_credit_id' => $billingCredit->id]);
                }
            }

            // For series bookings, book client into all other sessions in the series
            $seriesBookedCount = 0;
            $bookingType = $validated['booking_type'] ?? 'single';
            if ($bookingType === 'period' && !empty($validated['series_session_ids'])) {
                $sessionIds = array_filter(explode(',', $validated['series_session_ids']));
                // Remove the primary session (already booked above)
                $sessionIds = array_diff($sessionIds, [$classSession->id]);

                $otherSessions = ClassSession::where('host_id', $host->id)
                    ->whereIn('id', $sessionIds)
                    ->where('status', ClassSession::STATUS_PUBLISHED)
                    ->get();

                foreach ($otherSessions as $otherSession) {
                    // Skip if client already has a booking for this session
                    $existingBooking = Booking::where('client_id', $client->id)
                        ->where('bookable_type', ClassSession::class)
                        ->where('bookable_id', $otherSession->id)
                        ->where('status', '!=', Booking::STATUS_CANCELLED)
                        ->exists();

                    if ($existingBooking) continue;

                    Booking::create([
                        'host_id' => $host->id,
                        'client_id' => $client->id,
                        'bookable_type' => ClassSession::class,
                        'bookable_id' => $otherSession->id,
                        'status' => Booking::STATUS_CONFIRMED,
                        'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                        'capacity_override' => true,
                        'created_by_user_id' => auth()->id(),
                        'payment_method' => $bookingType === 'period' ? 'series' : ($validated['payment_method'] ?? 'manual'),
                        'price_paid' => 0, // Covered by series payment
                        'booked_at' => now(),
                    ]);
                    $seriesBookedCount++;
                }
            }

            $successMsg = "Walk-in booking confirmed for {$client->full_name}!";
            if ($seriesBookedCount > 0) {
                $successMsg = "Series booking confirmed for {$client->full_name}! Booked into " . ($seriesBookedCount + 1) . " sessions.";
            }

            return redirect()
                ->route('schedule.calendar')
                ->with('success', $successMsg);

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
            'payment_method' => 'required|in:membership,pack,manual,comp,billing_credit',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'offer_id' => 'nullable|integer|exists:offers,id',
            'promo_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'price_override_code' => 'nullable|string|max:20',
            'price_override_amount' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|integer|in:1,3,6,9,12',
            'billing_discount_percent' => 'nullable|numeric|min:0',
            'billing_credit_id' => 'nullable|integer|exists:billing_credits,id',
            'include_registration_fee' => 'nullable|in:0,1',
            'booking_type' => 'nullable|in:single,period,trial',
            'series_slot_ids' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Handle offer/discount
        $offer = null;
        $discountAmount = 0;
        $originalPrice = $serviceSlot->price ?? $serviceSlot->servicePlan?->price ?? 0;
        $priceOverrideRequest = null;

        // Handle price override (takes precedence over promo codes)
        if (!empty($validated['price_override_code'])) {
            $overrideCode = strtoupper($validated['price_override_code']);
            $priceOverrideService = app(\App\Services\PriceOverrideService::class);

            // Check if this is a direct override (manager/owner editing price directly)
            if ($overrideCode === 'DIRECT' && !empty($validated['price_override_amount'])) {
                $overridePrice = floatval($validated['price_override_amount']);
                $discountAmount = $originalPrice - $overridePrice;

                // Log the direct override
                $priceOverrideRequest = $priceOverrideService->logDirectOverride([
                    'host_id' => $host->id,
                    'location_id' => $serviceSlot->location_id ?? null,
                    'user_id' => auth()->id(),
                    'bookable_type' => get_class($serviceSlot),
                    'bookable_id' => $serviceSlot->id,
                    'client_id' => $client->id,
                    'original_price' => $originalPrice,
                    'new_price' => $overridePrice,
                    'metadata' => [
                        'service_name' => $serviceSlot->servicePlan?->name ?? 'Service',
                    ],
                ]);

                $validated['price_paid'] = $overridePrice;
            }
            // Check if this is a personal code (MY-XXXXX)
            elseif ($priceOverrideService->isPersonalCode($overrideCode)) {
                $codeOwner = $priceOverrideService->verifyPersonalCode($overrideCode, $host);

                if ($codeOwner && !empty($validated['price_override_amount'])) {
                    $overridePrice = floatval($validated['price_override_amount']);
                    $discountAmount = $originalPrice - $overridePrice;

                    // Log the personal override
                    $priceOverrideRequest = $priceOverrideService->logPersonalOverride([
                        'host_id' => $host->id,
                        'location_id' => $serviceSlot->location_id ?? null,
                        'requested_by' => auth()->id(),
                        'personal_code' => $overrideCode,
                        'bookable_type' => get_class($serviceSlot),
                        'bookable_id' => $serviceSlot->id,
                        'client_id' => $client->id,
                        'original_price' => $originalPrice,
                        'new_price' => $overridePrice,
                    ]);

                    $validated['price_paid'] = $overridePrice;
                }
            } else {
                // Regular override request code (PO-XXXXX)
                $priceOverrideRequest = \App\Models\PriceOverrideRequest::where('host_id', $host->id)
                    ->where('confirmation_code', $overrideCode)
                    ->where('status', \App\Models\PriceOverrideRequest::STATUS_APPROVED)
                    ->first();

                if ($priceOverrideRequest) {
                    $discountAmount = $priceOverrideRequest->discount_amount;
                    $validated['price_paid'] = $priceOverrideRequest->requested_price;
                }
            }
        }

        // Only process promo code offer if no price override was applied
        if (!$priceOverrideRequest && !empty($validated['offer_id'])) {
            $offer = \App\Models\Offer::where('id', $validated['offer_id'])
                ->where('host_id', $host->id)
                ->first();

            if ($offer) {
                $validation = $this->offerService->validateOffer($offer, $client, 'services', $originalPrice);
                if ($validation['valid']) {
                    $discountAmount = $validation['discount_amount'];
                }
            }
        }

        // For series bookings, check if ALL slots are already booked (block only if nothing new to add)
        $bookingType = $validated['booking_type'] ?? 'single';
        if ($bookingType === 'period' && !empty($validated['series_slot_ids'])) {
            $slotIds = array_filter(explode(',', $validated['series_slot_ids']));
            $existingCount = Booking::where('client_id', $client->id)
                ->where('bookable_type', ServiceSlot::class)
                ->whereIn('bookable_id', $slotIds)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->count();

            if ($existingCount >= count($slotIds)) {
                return back()
                    ->withInput()
                    ->with('error', "{$client->full_name} is already booked into all slots in this schedule.");
            }
        }

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

            // Record offer redemption if applicable
            if ($offer && $discountAmount > 0) {
                $this->offerService->recordRedemption(
                    $offer,
                    $client,
                    $originalPrice,
                    $discountAmount,
                    'front_desk',
                    $validated['promo_code'] ?? null,
                    auth()->id(),
                    get_class($booking),
                    $booking->id
                );
            }

            // Record price override usage if applicable
            if ($priceOverrideRequest && $priceOverrideRequest->status === \App\Models\PriceOverrideRequest::STATUS_APPROVED) {
                $metadata = $priceOverrideRequest->metadata ?? [];
                $metadata['applied_at'] = now()->toIso8601String();
                $metadata['booking_id'] = $booking->id;
                $metadata['booking_type'] = get_class($booking);
                $priceOverrideRequest->update(['metadata' => $metadata]);
            }

            // Create billing credit if a billing period was selected
            if (!empty($validated['billing_period']) && !empty($validated['billing_discount_percent'])) {
                $billingPeriod = (int) $validated['billing_period'];
                $totalAmount = (float) $validated['billing_discount_percent']; // Total amount for entire period
                $servicePlan = $serviceSlot->servicePlan;
                $baseMonthly = (float) $originalPrice;
                $monthlyRate = $billingPeriod > 0 ? $totalAmount / $billingPeriod : 0;
                $totalWithout = $baseMonthly * $billingPeriod;
                $discountPct = $totalWithout > 0 ? round((1 - $totalAmount / $totalWithout) * 100, 2) : 0;
                $includeRegFee = ($validated['include_registration_fee'] ?? '1') === '1';
                $registrationFee = $includeRegFee ? (float) ($servicePlan?->registration_fee ?? 0) : 0;

                \App\Models\BillingCredit::create([
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'source_type' => 'service_plan',
                    'source_id' => $servicePlan?->id ?? 0,
                    'booking_id' => $booking->id,
                    'billing_period' => $billingPeriod,
                    'discount_percent' => $discountPct,
                    'amount_paid' => $totalAmount,
                    'monthly_rate' => $monthlyRate,
                    'original_monthly_rate' => $baseMonthly,
                    'credit_remaining' => $totalAmount,
                    'registration_fee_paid' => $registrationFee,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths($billingPeriod)->toDateString(),
                    'status' => \App\Models\BillingCredit::STATUS_ACTIVE,
                    'created_by' => auth()->id(),
                ]);
            }

            // Deduct from billing credit if used as payment method
            if ($validated['payment_method'] === 'billing_credit' && !empty($validated['billing_credit_id'])) {
                $billingCredit = \App\Models\BillingCredit::where('id', $validated['billing_credit_id'])
                    ->where('host_id', $host->id)
                    ->where('client_id', $client->id)
                    ->first();

                if ($billingCredit && $billingCredit->isUsable()) {
                    $billingCredit->deduct($originalPrice);
                    $booking->update(['billing_credit_id' => $billingCredit->id]);
                }
            }

            // For series bookings, book client into all other slots in the series
            $seriesBookedCount = 0;
            if ($bookingType === 'period' && !empty($validated['series_slot_ids'])) {
                $slotIds = array_map('intval', array_filter(explode(',', $validated['series_slot_ids'])));
                // Remove the primary slot (already booked above)
                $slotIds = array_values(array_diff($slotIds, [(int) $serviceSlot->id]));

                if (!empty($slotIds)) {
                    $otherSlots = ServiceSlot::where('host_id', $host->id)
                        ->whereIn('id', $slotIds)
                        ->where('status', ServiceSlot::STATUS_AVAILABLE)
                        ->get();

                    foreach ($otherSlots as $otherSlot) {
                        // Skip if client already has a booking for this slot
                        $existingBooking = Booking::where('client_id', $client->id)
                            ->where('bookable_type', ServiceSlot::class)
                            ->where('bookable_id', $otherSlot->id)
                            ->where('status', '!=', Booking::STATUS_CANCELLED)
                            ->exists();

                        if ($existingBooking) continue;

                        Booking::create([
                            'host_id' => $host->id,
                            'client_id' => $client->id,
                            'bookable_type' => ServiceSlot::class,
                            'bookable_id' => $otherSlot->id,
                            'status' => Booking::STATUS_CONFIRMED,
                            'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                            'capacity_override' => true,
                            'created_by_user_id' => auth()->id(),
                            'payment_method' => 'series',
                            'price_paid' => 0, // Covered by series payment
                            'booked_at' => now(),
                        ]);

                        // Mark slot as booked
                        $otherSlot->update(['status' => ServiceSlot::STATUS_BOOKED]);
                        $seriesBookedCount++;
                    }
                }
            }

            $successMsg = "Slot Booked for {$client->full_name}!";
            if ($seriesBookedCount > 0) {
                $successMsg = "Series booking confirmed for {$client->full_name}! Booked into " . ($seriesBookedCount + 1) . " slots.";
            }

            return redirect()
                ->route('service-slots.index')
                ->with('success', $successMsg);

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
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
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
            'status' => 'client',
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
            'billing_discounts' => $classPlan->billing_discounts ?? null,
            'registration_fee' => (float) ($classPlan->registration_fee ?? 0),
            'cancellation_fee' => (float) ($classPlan->cancellation_fee ?? 0),
            'cancellation_grace_hours' => $classPlan->cancellation_grace_hours ?? 48,
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

    /**
     * Show service slot selection page (walk-in services)
     */
    public function selectServiceSlot(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost();

        // Get active service plans
        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get active instructors
        $instructors = Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Preload slot if provided
        $preloadSlot = null;
        if ($request->has('slot')) {
            $preloadSlot = ServiceSlot::where('host_id', $host->id)
                ->where('id', $request->get('slot'))
                ->first();
        }

        // Selected date - use preload slot date if available, otherwise request date or today
        $selectedDate = $preloadSlot
            ? $preloadSlot->start_time->format('Y-m-d')
            : $request->get('date', now()->format('Y-m-d'));

        // Price override permissions - only if feature is enabled
        $hasFeature = $host->hasFeature('price-override');
        $canOverridePrice = $hasFeature && $user->canApprovePriceOverride($host);
        $canRequestOverride = $hasFeature && !$user->canApprovePriceOverride($host);

        return view('host.walk-in.select-service-slot', compact(
            'servicePlans',
            'instructors',
            'selectedDate',
            'preloadSlot',
            'host',
            'canOverridePrice',
            'canRequestOverride'
        ));
    }

    /**
     * Get service slots by date (AJAX)
     */
    public function getServiceSlotsByDate(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'date' => 'required|date',
            'service_plan_id' => 'nullable|exists:service_plans,id',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);

        $query = ServiceSlot::where('host_id', $host->id)
            ->whereDate('start_time', $date)
            ->where('status', ServiceSlot::STATUS_AVAILABLE)
            ->with(['servicePlan', 'instructor', 'location']);

        if (!empty($validated['service_plan_id'])) {
            $query->where('service_plan_id', $validated['service_plan_id']);
        }

        $slots = $query->orderBy('start_time')->get();

        $slotsData = $slots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'time' => $slot->start_time->format('g:i A') . ' - ' . $slot->end_time->format('g:i A'),
                'time_raw' => $slot->start_time->format('H:i'),
                'service' => $slot->servicePlan->name ?? 'Unknown Service',
                'instructor' => $slot->instructor->name ?? 'TBD',
                'location' => $slot->location->name ?? null,
                'duration' => $slot->duration_minutes,
                'price' => $slot->getEffectivePrice(),
                'formatted_price' => $slot->formatted_price,
                'start_time_iso' => $slot->start_time->toIso8601String(),
                'end_time_iso' => $slot->end_time->toIso8601String(),
                'billing_discounts' => $slot->servicePlan?->billing_discounts ?? null,
                'service_plan_id' => $slot->service_plan_id,
                'registration_fee' => (float) ($slot->servicePlan?->registration_fee ?? 0),
                'cancellation_fee' => (float) ($slot->servicePlan?->cancellation_fee ?? 0),
                'cancellation_grace_hours' => $slot->servicePlan?->cancellation_grace_hours ?? 48,
            ];
        });

        // Find next available dates if no slots
        $nextAvailable = [];
        if ($slots->isEmpty()) {
            $nextAvailable = ServiceSlot::where('host_id', $host->id)
                ->where('status', ServiceSlot::STATUS_AVAILABLE)
                ->where('start_time', '>', $date->endOfDay())
                ->when(!empty($validated['service_plan_id']), function ($q) use ($validated) {
                    $q->where('service_plan_id', $validated['service_plan_id']);
                })
                ->orderBy('start_time')
                ->limit(5)
                ->get()
                ->groupBy(fn($s) => $s->start_time->format('Y-m-d'))
                ->take(3)
                ->map(function ($group, $dateStr) {
                    $dateObj = \Carbon\Carbon::parse($dateStr);
                    return [
                        'date' => $dateStr,
                        'formatted_date' => $dateObj->format('M j'),
                        'slot_count' => $group->count(),
                    ];
                })
                ->values()
                ->all();
        }

        return response()->json([
            'slots' => $slotsData,
            'next_available' => $nextAvailable,
        ]);
    }

    /**
     * Get service plan defaults (AJAX)
     */
    public function getServicePlanDefaults(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'service_plan_id' => 'required|exists:service_plans,id',
        ]);

        $servicePlan = ServicePlan::where('host_id', $host->id)
            ->findOrFail($validated['service_plan_id']);

        return response()->json([
            'duration_minutes' => $servicePlan->duration_minutes,
            'price' => $servicePlan->price,
            'billing_discounts' => $servicePlan->billing_discounts ?? null,
            'registration_fee' => (float) ($servicePlan->registration_fee ?? 0),
            'cancellation_fee' => (float) ($servicePlan->cancellation_fee ?? 0),
            'cancellation_grace_hours' => $servicePlan->cancellation_grace_hours ?? 48,
        ]);
    }

    /**
     * Quick create service slot (AJAX)
     */
    public function quickCreateServiceSlot(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'service_plan_id' => 'required|exists:service_plans,id',
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
        ]);

        // Verify service plan and instructor belong to host
        $servicePlan = ServicePlan::where('host_id', $host->id)
            ->findOrFail($validated['service_plan_id']);
        $instructor = Instructor::where('host_id', $host->id)
            ->findOrFail($validated['instructor_id']);

        $date = \Carbon\Carbon::parse($validated['date']);
        $startTime = $date->copy()->setTimeFromTimeString($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($servicePlan->duration_minutes);

        // Check for conflicts
        $conflicting = ServiceSlot::where('host_id', $host->id)
            ->where('instructor_id', $instructor->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($conflicting) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot conflicts with an existing appointment.',
            ], 422);
        }

        // Create slot
        $slot = ServiceSlot::create([
            'host_id' => $host->id,
            'service_plan_id' => $servicePlan->id,
            'instructor_id' => $instructor->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => ServiceSlot::STATUS_AVAILABLE,
        ]);

        return response()->json([
            'success' => true,
            'slot' => [
                'id' => $slot->id,
                'time' => $slot->start_time->format('g:i A') . ' - ' . $slot->end_time->format('g:i A'),
                'service' => $servicePlan->name,
                'instructor' => $instructor->name,
                'duration' => $servicePlan->duration_minutes,
                'price' => $slot->getEffectivePrice(),
                'formatted_price' => $slot->formatted_price,
                'start_time_iso' => $slot->start_time->toIso8601String(),
                'end_time_iso' => $slot->end_time->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show membership selection page for walk-in booking
     */
    public function selectMembership(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost();

        // Get active membership plans
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        // Pre-selected class session (from membership schedules)
        $selectedClassSession = null;
        $preselectedMembershipPlanId = null;
        if ($request->has('class_session_id')) {
            $selectedClassSession = ClassSession::where('host_id', $host->id)
                ->where('id', $request->get('class_session_id'))
                ->with(['classPlan', 'primaryInstructor', 'location', 'membershipPlans'])
                ->first();

            // Pre-select the first membership plan associated with the class session
            if ($selectedClassSession && $selectedClassSession->membershipPlans->isNotEmpty()) {
                $preselectedMembershipPlanId = $selectedClassSession->membershipPlans->first()->id;
            }
        }

        // Price override permissions - only if feature is enabled
        $hasFeature = $host->hasFeature('price-override');
        $canOverridePrice = $hasFeature && $user->canApprovePriceOverride($host);
        $canRequestOverride = $hasFeature && !$user->canApprovePriceOverride($host);

        return view('host.walk-in.select-membership', compact(
            'membershipPlans',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols',
            'selectedClassSession',
            'preselectedMembershipPlanId',
            'host',
            'canOverridePrice',
            'canRequestOverride'
        ));
    }

    /**
     * Show walk-in class pass sell page (lists all active passes)
     */
    public function selectClassPass(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost();

        $classPasses = ClassPass::where('host_id', $host->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.walk-in.select-classpass', compact(
            'classPasses',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    /**
     * Process walk-in class pass sale
     */
    public function sellClassPass(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'class_pass_id' => 'required|exists:class_passes,id',
            'payment_method' => 'required|in:cash,card,check,other,comp',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $client = Client::where('id', $validated['client_id'])
            ->where('host_id', $host->id)
            ->firstOrFail();

        $classPass = ClassPass::where('id', $validated['class_pass_id'])
            ->where('host_id', $host->id)
            ->firstOrFail();

        $classPassService = app(\App\Services\ClassPassService::class);

        $paymentMethod = $validated['payment_method'];
        $amountPaid = $validated['amount_paid'] ?? $classPass->price;

        $purchase = $classPassService->purchasePass($host, $client, $classPass, [
            'manual_method' => $paymentMethod !== 'comp' ? $paymentMethod : null,
            'payment_method' => $paymentMethod,
            'amount_paid' => $amountPaid,
            'payment_notes' => $validated['notes'],
        ]);

        // Create transaction for class pass purchase
        \App\Models\Transaction::create([
            'host_id' => $host->id,
            'client_id' => $client->id,
            'type' => \App\Models\Transaction::TYPE_CLASS_PACK_PURCHASE,
            'purchasable_type' => \App\Models\ClassPass::class,
            'purchasable_id' => $classPass->id,
            'subtotal' => (float) $amountPaid,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => (float) $amountPaid,
            'currency' => $host->default_currency ?? 'USD',
            'status' => \App\Models\Transaction::STATUS_PAID,
            'payment_method' => $paymentMethod === 'comp' ? \App\Models\Transaction::METHOD_COMP : \App\Models\Transaction::METHOD_MANUAL,
            'manual_method' => $paymentMethod !== 'comp' ? $paymentMethod : null,
            'paid_at' => now(),
            'metadata' => [
                'item_name' => $classPass->name,
                'credits' => $classPass->class_count,
                'source' => 'walk_in',
            ],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('schedule.calendar')
            ->with('success', "Class pass \"{$classPass->name}\" sold to {$client->full_name}. They now have {$purchase->classes_remaining} credits.");
    }

    /**
     * Get membership plans (AJAX)
     */
    public function getMembershipPlans(Request $request)
    {
        $host = auth()->user()->currentHost();

        $plans = MembershipPlan::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($plan) use ($host) {
                $defaultCurrency = $host->default_currency ?? 'USD';
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'type' => $plan->type,
                    'formatted_type' => $plan->formatted_type,
                    'interval' => $plan->interval,
                    'formatted_interval' => $plan->formatted_interval,
                    'price' => $plan->getPriceForCurrency($defaultCurrency),
                    'formatted_price' => $plan->getFormattedPriceForCurrency($defaultCurrency),
                    'formatted_price_with_interval' => $plan->formatted_price_with_interval,
                    'credits_per_cycle' => $plan->credits_per_cycle,
                    'color' => $plan->color,
                ];
            });

        return response()->json(['plans' => $plans]);
    }

    /**
     * Process walk-in membership booking
     */
    public function bookMembership(Request $request)
    {
        $host = auth()->user()->currentHost();

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|in:manual,comp',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'offer_id' => 'nullable|integer|exists:offers,id',
            'promo_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'book_into_class' => 'nullable|boolean',
            'price_override_code' => 'nullable|string|max:20',
            'price_override_amount' => 'nullable|numeric|min:0',
        ]);

        // Verify client belongs to host
        $client = Client::where('host_id', $host->id)
            ->findOrFail($validated['client_id']);

        // Verify membership plan belongs to host
        $membershipPlan = MembershipPlan::where('host_id', $host->id)
            ->findOrFail($validated['membership_plan_id']);

        // Handle offer/discount
        $offer = null;
        $discountAmount = 0;
        $defaultCurrency = $host->default_currency ?? 'USD';
        $originalPrice = $membershipPlan->getPriceForCurrency($defaultCurrency) ?? 0;
        $priceOverrideRequest = null;

        // Handle price override (takes precedence over promo codes)
        if (!empty($validated['price_override_code'])) {
            $overrideCode = strtoupper($validated['price_override_code']);
            $priceOverrideService = app(\App\Services\PriceOverrideService::class);

            // Check if this is a direct override (manager/owner editing price directly)
            if ($overrideCode === 'DIRECT' && !empty($validated['price_override_amount'])) {
                $overridePrice = floatval($validated['price_override_amount']);
                $discountAmount = $originalPrice - $overridePrice;

                // Log the direct override
                $priceOverrideRequest = $priceOverrideService->logDirectOverride([
                    'host_id' => $host->id,
                    'location_id' => null,
                    'user_id' => auth()->id(),
                    'bookable_type' => MembershipPlan::class,
                    'bookable_id' => $membershipPlan->id,
                    'client_id' => $client->id,
                    'original_price' => $originalPrice,
                    'new_price' => $overridePrice,
                    'metadata' => [
                        'membership_name' => $membershipPlan->name ?? 'Membership',
                    ],
                ]);

                $validated['price_paid'] = $overridePrice;
            }
            // Check if this is a personal code (MY-XXXXX)
            elseif ($priceOverrideService->isPersonalCode($overrideCode)) {
                $codeOwner = $priceOverrideService->verifyPersonalCode($overrideCode, $host);

                if ($codeOwner && !empty($validated['price_override_amount'])) {
                    $overridePrice = floatval($validated['price_override_amount']);
                    $discountAmount = $originalPrice - $overridePrice;

                    // Log the personal override
                    $priceOverrideRequest = $priceOverrideService->logPersonalOverride([
                        'host_id' => $host->id,
                        'location_id' => null,
                        'requested_by' => auth()->id(),
                        'personal_code' => $overrideCode,
                        'bookable_type' => MembershipPlan::class,
                        'bookable_id' => $membershipPlan->id,
                        'client_id' => $client->id,
                        'original_price' => $originalPrice,
                        'new_price' => $overridePrice,
                    ]);

                    $validated['price_paid'] = $overridePrice;
                }
            } else {
                // Regular override request code (PO-XXXXX)
                $priceOverrideRequest = \App\Models\PriceOverrideRequest::where('host_id', $host->id)
                    ->where('confirmation_code', $overrideCode)
                    ->where('status', \App\Models\PriceOverrideRequest::STATUS_APPROVED)
                    ->first();

                if ($priceOverrideRequest) {
                    $discountAmount = $priceOverrideRequest->discount_amount;
                    $validated['price_paid'] = $priceOverrideRequest->requested_price;
                }
            }
        }

        // Only process promo code offer if no price override was applied
        if (!$priceOverrideRequest && !empty($validated['offer_id'])) {
            $offer = Offer::where('id', $validated['offer_id'])
                ->where('host_id', $host->id)
                ->first();

            if ($offer) {
                $validation = $this->offerService->validateOffer($offer, $client, 'memberships', $originalPrice);
                if ($validation['valid']) {
                    $discountAmount = $validation['discount_amount'];
                }
            }
        }

        try {
            // Determine start date
            $startDate = !empty($validated['start_date'])
                ? \Carbon\Carbon::parse($validated['start_date'])
                : now();

            // Calculate end date based on interval
            $endDate = match ($membershipPlan->interval) {
                'weekly' => $startDate->copy()->addWeek(),
                'monthly' => $startDate->copy()->addMonth(),
                'quarterly' => $startDate->copy()->addMonths(3),
                'yearly' => $startDate->copy()->addYear(),
                default => $startDate->copy()->addMonth(),
            };

            // Calculate price paid
            $pricePaid = $validated['payment_method'] === 'comp'
                ? 0
                : ($validated['price_paid'] ?? max(0, $originalPrice - $discountAmount));

            // Create customer membership
            $customerMembership = CustomerMembership::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'membership_plan_id' => $membershipPlan->id,
                'status' => CustomerMembership::STATUS_ACTIVE,
                'started_at' => $startDate,
                'expires_at' => $endDate,
                'current_period_start' => $startDate,
                'current_period_end' => $endDate,
                'credits_remaining' => $membershipPlan->type === 'credits' ? $membershipPlan->credits_per_cycle : null,
                'credits_per_period' => $membershipPlan->type === 'credits' ? $membershipPlan->credits_per_cycle : null,
                'payment_method' => $validated['payment_method'] === 'comp' ? 'comp' : 'manual',
                'created_by_user_id' => auth()->id(),
            ]);

            // Record offer redemption if applicable
            if ($offer && $discountAmount > 0) {
                $this->offerService->recordRedemption(
                    $offer,
                    $client,
                    $originalPrice,
                    $discountAmount,
                    'front_desk',
                    $validated['promo_code'] ?? null,
                    auth()->id(),
                    CustomerMembership::class,
                    $customerMembership->id
                );
            }

            // Record price override usage if applicable
            if ($priceOverrideRequest && $priceOverrideRequest->status === \App\Models\PriceOverrideRequest::STATUS_APPROVED) {
                $metadata = $priceOverrideRequest->metadata ?? [];
                $metadata['applied_at'] = now()->toIso8601String();
                $metadata['booking_id'] = $customerMembership->id;
                $metadata['booking_type'] = CustomerMembership::class;
                $priceOverrideRequest->update(['metadata' => $metadata]);
            }

            // If a class session was specified and user wants to book into it
            if (!empty($validated['class_session_id']) && !empty($validated['book_into_class'])) {
                $classSession = ClassSession::where('host_id', $host->id)
                    ->find($validated['class_session_id']);

                if ($classSession) {
                    try {
                        $this->bookingService->createWalkInClassBooking(
                            host: $host,
                            client: $client,
                            session: $classSession,
                            options: [
                                'payment_method' => 'membership',
                                'customer_membership_id' => $customerMembership->id,
                                'check_in_now' => false,
                                'capacity_override' => true,
                            ]
                        );

                        return redirect()
                            ->route('membership-schedules.index')
                            ->with('success', "Membership '{$membershipPlan->name}' sold and {$client->full_name} booked into {$classSession->display_title}!");
                    } catch (\Exception $e) {
                        // Membership was sold but booking failed - still redirect with partial success
                        return redirect()
                            ->route('membership-schedules.index')
                            ->with('warning', "Membership sold but could not book into class: {$e->getMessage()}");
                    }
                }
            }

            // Create transaction for membership purchase
            \App\Models\Transaction::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'type' => \App\Models\Transaction::TYPE_MEMBERSHIP_PURCHASE,
                'purchasable_type' => MembershipPlan::class,
                'purchasable_id' => $membershipPlan->id,
                'subtotal' => (float) $pricePaid,
                'tax_amount' => 0,
                'discount_amount' => (float) $discountAmount,
                'total_amount' => (float) $pricePaid,
                'currency' => $defaultCurrency,
                'status' => \App\Models\Transaction::STATUS_PAID,
                'payment_method' => $validated['payment_method'] === 'comp' ? \App\Models\Transaction::METHOD_COMP : \App\Models\Transaction::METHOD_MANUAL,
                'manual_method' => $validated['payment_method'] !== 'comp' ? ($validated['manual_method'] ?? null) : null,
                'paid_at' => now(),
                'metadata' => [
                    'item_name' => $membershipPlan->name,
                    'membership_id' => $customerMembership->id,
                    'source' => 'walk_in',
                ],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Determine redirect based on where we came from
            $redirectRoute = !empty($validated['class_session_id']) ? 'membership-schedules.index' : 'schedule.calendar';

            return redirect()
                ->route($redirectRoute)
                ->with('success', "Membership '{$membershipPlan->name}' sold to {$client->full_name}!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show event registration form
     */
    public function event(Event $event)
    {
        $host = auth()->user()->host;

        // Verify event belongs to this host
        if ($event->host_id !== $host->id) {
            abort(404);
        }

        // Check if event can accept attendees
        if (!$event->canAddAttendees()) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'This event cannot accept new registrations.');
        }

        // Get recent clients
        $recentClients = Client::where('host_id', $host->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate spots remaining
        $registeredCount = $event->registeredAttendees()->count();
        $spotsRemaining = $event->capacity ? max(0, $event->capacity - $registeredCount) : null;

        $trans = session('trans', []);

        return view('host.walk-in.event', compact(
            'event',
            'recentClients',
            'spotsRemaining',
            'trans'
        ));
    }

    /**
     * Register a client for an event
     */
    public function registerEvent(Request $request, Event $event)
    {
        $host = auth()->user()->host;

        // Verify event belongs to this host
        if ($event->host_id !== $host->id) {
            abort(404);
        }

        // Validate request
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:registered,confirmed,waitlisted',
            'check_in_now' => 'boolean',
            'send_confirmation' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get client and verify it belongs to this host
        $client = Client::where('host_id', $host->id)->findOrFail($validated['client_id']);

        // Check if client is already registered
        if ($event->isClientRegistered($client->id)) {
            return back()
                ->withInput()
                ->with('error', 'This client is already registered for this event.');
        }

        // Check capacity for non-waitlisted registrations
        if ($validated['status'] !== 'waitlisted' && $event->capacity) {
            $registeredCount = $event->registeredAttendees()->count();
            if ($registeredCount >= $event->capacity) {
                // Auto-switch to waitlist if at capacity
                $validated['status'] = 'waitlisted';
            }
        }

        try {
            // Add client to event
            $pivotData = [
                'status' => $validated['status'],
                'registered_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ];

            // Handle waitlist position
            if ($validated['status'] === 'waitlisted') {
                $maxPosition = $event->clients()
                    ->wherePivot('status', 'waitlisted')
                    ->max('event_attendees.waitlist_position') ?? 0;
                $pivotData['waitlist_position'] = $maxPosition + 1;
            }

            // Check in if requested
            if (!empty($validated['check_in_now']) && $validated['status'] !== 'waitlisted') {
                $pivotData['checked_in_at'] = now();
                $pivotData['status'] = 'attended';
            }

            $event->clients()->attach($client->id, $pivotData);

            // TODO: Send confirmation email if requested
            // if (!empty($validated['send_confirmation'])) {
            //     Mail::to($client->email)->send(new EventRegistrationConfirmation($event, $client));
            // }

            $statusMessage = $validated['status'] === 'waitlisted'
                ? 'added to waitlist'
                : 'registered';

            return redirect()
                ->route('events.show', $event)
                ->with('success', "{$client->full_name} has been {$statusMessage} for {$event->title}!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to register attendee: ' . $e->getMessage());
        }
    }

    /**
     * Get service slots by date range (for series booking)
     */
    public function getServiceSlotsByDateRange(Request $request)
    {
        $host = auth()->user()->currentHost();
        $servicePlanId = $request->get('service_plan_id');
        $months = (int) $request->get('months', 1);

        if (!$servicePlanId) {
            return response()->json(['slots' => []]);
        }

        $startDate = now();
        $endDate = now()->addMonths($months);

        $slots = ServiceSlot::where('host_id', $host->id)
            ->where('service_plan_id', $servicePlanId)
            ->where('status', ServiceSlot::STATUS_AVAILABLE)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->select(['id', 'service_plan_id', 'instructor_id', 'location_id', 'start_time', 'end_time', 'price', 'recurrence_parent_id'])
            ->with([
                'servicePlan:id,name,price,billing_discounts,registration_fee,cancellation_fee,cancellation_grace_hours',
                'instructor:id,name',
                'location:id,name'
            ])
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'title' => $slot->servicePlan?->name,
                    'date' => $slot->start_time->format('D, M d'),
                    'time' => $slot->start_time->format('g:i A') . ' - ' . $slot->end_time->format('g:i A'),
                    'start_time_iso' => $slot->start_time->toIso8601String(),
                    'end_time_iso' => $slot->end_time->toIso8601String(),
                    'instructor' => $slot->instructor?->name ?? 'TBD',
                    'location' => $slot->location?->name ?? null,
                    'price' => (float) $slot->price > 0 ? $slot->price : ($slot->servicePlan?->price ?? 0),
                    'billing_discounts' => $slot->servicePlan?->billing_discounts ?? null,
                    'registration_fee' => (float) ($slot->servicePlan?->registration_fee ?? 0),
                    'cancellation_fee' => (float) ($slot->servicePlan?->cancellation_fee ?? 0),
                    'cancellation_grace_hours' => $slot->servicePlan?->cancellation_grace_hours ?? 48,
                    'recurrence_parent_id' => $slot->recurrence_parent_id,
                ];
            });

        return response()->json([
            'slots' => $slots,
            'total' => $slots->count(),
            'period_start' => $startDate->format('M d, Y'),
            'period_end' => $endDate->format('M d, Y'),
        ]);
    }

    /**
     * Get schedule options for a service plan (for series booking schedule picker)
     */
    public function getServiceSchedules(Request $request)
    {
        $host = auth()->user()->currentHost();
        $servicePlanId = $request->get('service_plan_id');
        $months = (int) $request->get('months', 1);

        if (!$servicePlanId) {
            return response()->json(['schedules' => []]);
        }

        $startDate = now();
        $endDate = now()->addMonths($months);

        $slots = ServiceSlot::where('host_id', $host->id)
            ->where('service_plan_id', $servicePlanId)
            ->where('status', ServiceSlot::STATUS_AVAILABLE)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->select(['id', 'title', 'recurrence_parent_id', 'recurrence_rule', 'instructor_id', 'location_id', 'start_time', 'end_time'])
            ->with(['instructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        $grouped = $slots->groupBy(function ($slot) {
            return $slot->recurrence_parent_id ?? ($slot->recurrence_rule ? $slot->id : 'oneoff');
        });

        $parentIds = $grouped->keys()->filter(fn($k) => is_numeric($k))->values()->toArray();
        $parents = ServiceSlot::whereIn('id', $parentIds)
            ->select(['id', 'title', 'recurrence_rule', 'instructor_id', 'location_id', 'start_time', 'end_time'])
            ->with(['instructor:id,name', 'location:id,name'])
            ->get()
            ->keyBy('id');

        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

        $schedules = [];
        foreach ($grouped as $key => $groupSlots) {
            if ($groupSlots->isEmpty()) continue;

            $first = $groupSlots->first();
            $parent = is_numeric($key) ? ($parents->get($key) ?? $first) : $first;

            $label = 'One-off Slots';
            if ($key !== 'oneoff' && $parent->recurrence_rule) {
                $recurrenceService = app(\App\Services\Schedule\RecurrenceService::class);
                $parsed = $recurrenceService->parseRecurrenceRule($parent->recurrence_rule);
                if (!empty($parsed['days_of_week'])) {
                    $label = collect($parsed['days_of_week'])
                        ->map(fn($d) => $dayNames[(int) $d] ?? $d)
                        ->implode(', ');
                }
            } elseif ($key !== 'oneoff') {
                $label = $parent->start_time->format('l');
            }

            $lastSlot = $groupSlots->last();

            $schedules[] = [
                'parent_id' => $key,
                'title' => $parent->title ?? null,
                'label' => $label,
                'time' => $parent->start_time->format('g:i A') . ' - ' . $parent->end_time->format('g:i A'),
                'instructor' => $parent->instructor?->name ?? 'TBD',
                'location' => $parent->location?->name ?? null,
                'last_slot_date' => $lastSlot?->start_time->format('M d, Y') ?? null,
                'slot_count' => $groupSlots->count(),
                'slot_ids' => $groupSlots->pluck('id')->toArray(),
            ];
        }

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * Check if a client already has bookings for service slots in a series
     */
    public function checkServiceSeriesConflict(Request $request)
    {
        $host = auth()->user()->currentHost();
        $clientId = $request->get('client_id');
        $slotIds = array_filter(explode(',', $request->get('slot_ids', '')));

        if (!$clientId || empty($slotIds)) {
            return response()->json(['has_conflict' => false]);
        }

        $client = Client::where('id', $clientId)->where('host_id', $host->id)->first();
        if (!$client) {
            return response()->json(['has_conflict' => false]);
        }

        $existingCount = Booking::where('client_id', $client->id)
            ->where('bookable_type', ServiceSlot::class)
            ->whereIn('bookable_id', $slotIds)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->count();

        if ($existingCount > 0) {
            $totalSlots = count($slotIds);
            $newSlots = $totalSlots - $existingCount;
            return response()->json([
                'has_conflict' => true,
                'existing_count' => $existingCount,
                'total_slots' => $totalSlots,
                'new_slots' => $newSlots,
                'client_name' => $client->full_name,
                'message' => $newSlots <= 0
                    ? "{$client->full_name} is already booked into all {$existingCount} slot(s) in this schedule."
                    : "{$client->full_name} is already booked into {$existingCount} of {$totalSlots} slot(s).",
            ]);
        }

        return response()->json(['has_conflict' => false]);
    }
}
