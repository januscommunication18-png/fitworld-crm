<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\Client;
use App\Models\Booking;
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

        // Get active class plans from catalog
        $classPlans = \App\Models\ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'default_price']);

        return view('host.walk-in.select-session', [
            'selectedDate' => $date,
            'classPlans' => $classPlans,
        ]);
    }

    /**
     * Get sessions by date (AJAX)
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

        $sessions = $query->with(['classPlan', 'primaryInstructor', 'location'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                $bookedCount = $session->bookings()
                    ->where('status', '!=', Booking::STATUS_CANCELLED)
                    ->count();

                return [
                    'id' => $session->id,
                    'title' => $session->display_title,
                    'time' => $session->start_time->format('g:i A') . ' - ' . $session->end_time->format('g:i A'),
                    'instructor' => $session->primaryInstructor?->name ?? 'TBD',
                    'location' => $session->location?->name ?? null,
                    'capacity' => $session->capacity,
                    'booked' => $bookedCount,
                    'spots_remaining' => $session->capacity - $bookedCount,
                    'color' => $session->classPlan->color ?? '#6366f1',
                    'price' => $session->price ?? $session->classPlan->default_price ?? 0,
                ];
            });

        // If no sessions found, get next available dates
        $nextAvailable = [];
        if ($sessions->isEmpty() && $classPlanId) {
            $nextSessions = ClassSession::where('host_id', $host->id)
                ->where('class_plan_id', $classPlanId)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>', $date)
                ->orderBy('start_time')
                ->limit(5)
                ->get()
                ->groupBy(function ($session) {
                    return $session->start_time->format('Y-m-d');
                })
                ->take(3);

            foreach ($nextSessions as $sessionDate => $dateSessions) {
                $nextAvailable[] = [
                    'date' => $sessionDate,
                    'formatted_date' => \Carbon\Carbon::parse($sessionDate)->format('D, M j'),
                    'session_count' => $dateSessions->count(),
                ];
            }
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
                ]
            );

            return redirect()
                ->route('class-sessions.show', $classSession)
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
}
