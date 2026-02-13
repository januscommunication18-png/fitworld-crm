<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalkInClassBookingRequest;
use App\Http\Requests\Api\WalkInServiceBookingRequest;
use App\Http\Requests\Api\ClientQuickAddRequest;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\ServiceSlot;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalkInBookingController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;

    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
    }

    /**
     * Book a client into a class session (walk-in)
     */
    public function bookClass(WalkInClassBookingRequest $request, ClassSession $session): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;
        $client = Client::where('host_id', $host->id)
            ->findOrFail($request->client_id);

        // Verify session belongs to this host
        if ($session->host_id !== $host->id) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        try {
            $booking = $this->bookingService->createWalkInClassBooking(
                $host,
                $client,
                $session,
                $this->buildBookingOptions($request)
            );

            return response()->json([
                'success' => true,
                'message' => 'Walk-in booking created successfully.',
                'booking' => [
                    'id' => $booking->id,
                    'client' => [
                        'id' => $booking->client->id,
                        'name' => $booking->client->full_name,
                    ],
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'checked_in' => $booking->isCheckedIn(),
                    'intake_status' => $booking->intake_status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Book a client into a service slot (walk-in)
     */
    public function bookService(WalkInServiceBookingRequest $request, ServiceSlot $slot): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;
        $client = Client::where('host_id', $host->id)
            ->findOrFail($request->client_id);

        // Verify slot belongs to this host
        if ($slot->host_id !== $host->id) {
            return response()->json(['message' => 'Service slot not found.'], 404);
        }

        try {
            $booking = $this->bookingService->createWalkInServiceBooking(
                $host,
                $client,
                $slot,
                $this->buildBookingOptions($request)
            );

            return response()->json([
                'success' => true,
                'message' => 'Walk-in service booking created successfully.',
                'booking' => [
                    'id' => $booking->id,
                    'client' => [
                        'id' => $booking->client->id,
                        'name' => $booking->client->full_name,
                    ],
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'checked_in' => $booking->isCheckedIn(),
                    'intake_status' => $booking->intake_status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Quick add a new client
     */
    public function quickAddClient(ClientQuickAddRequest $request): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;

        // Check for existing client by email
        if ($request->email) {
            $existingClient = Client::where('host_id', $host->id)
                ->where('email', $request->email)
                ->first();

            if ($existingClient) {
                return response()->json([
                    'success' => true,
                    'message' => 'Existing client found.',
                    'client' => [
                        'id' => $existingClient->id,
                        'first_name' => $existingClient->first_name,
                        'last_name' => $existingClient->last_name,
                        'email' => $existingClient->email,
                        'phone' => $existingClient->phone,
                        'full_name' => $existingClient->full_name,
                    ],
                    'existing' => true,
                ]);
            }
        }

        // Create new client
        $client = Client::create([
            'host_id' => $host->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => Client::STATUS_CLIENT,
            'lead_source' => 'manual',
            'email_opt_in' => $request->boolean('send_emails', false),
            'sms_opt_in' => $request->boolean('send_sms', false),
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully.',
            'client' => [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'full_name' => $client->full_name,
            ],
            'existing' => false,
        ], 201);
    }

    /**
     * Search clients
     */
    public function searchClients(Request $request): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json(['clients' => []]);
        }

        $clients = Client::where('host_id', $host->id)
            ->active()
            ->search($search)
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'full_name' => $client->full_name,
                    'status' => $client->status,
                    'is_member' => $client->is_member,
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    /**
     * Get recent clients
     */
    public function recentClients(Request $request): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;

        $clients = Client::where('host_id', $host->id)
            ->active()
            ->orderByDesc('last_visit_at')
            ->limit(5)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'full_name' => $client->full_name,
                    'last_visit' => $client->last_visit_at?->diffForHumans(),
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    /**
     * Get available payment methods for a client
     */
    public function getPaymentMethods(Request $request, Client $client): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;

        // Verify client belongs to this host
        if ($client->host_id !== $host->id) {
            return response()->json(['message' => 'Client not found.'], 404);
        }

        $classPlanId = $request->get('class_plan_id');

        $methods = $this->paymentService->getAvailablePaymentMethods(
            $host,
            $client,
            $classPlanId
        );

        return response()->json(['payment_methods' => $methods]);
    }

    /**
     * Check availability for a class session
     */
    public function checkClassAvailability(Request $request, ClassSession $session): JsonResponse
    {
        $host = $request->user()->currentHost() ?? $request->user()->host;

        // Verify session belongs to this host
        if ($session->host_id !== $host->id) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $availability = $this->bookingService->validateCapacity($session);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'class_plan' => $session->classPlan->name,
                'start_time' => $session->start_time->format('g:i A'),
                'date' => $session->date->format('M j, Y'),
            ],
            'availability' => $availability,
        ]);
    }

    /**
     * Build booking options from request
     */
    protected function buildBookingOptions(Request $request): array
    {
        return [
            'payment_method' => $request->payment_method,
            'manual_method' => $request->manual_method,
            'price_paid' => $request->price_paid,
            'payment_notes' => $request->payment_notes,
            'customer_membership_id' => $request->customer_membership_id,
            'class_pack_purchase_id' => $request->class_pack_purchase_id,
            'intake_status' => $request->intake_status,
            'intake_waived_by' => $request->intake_waived ? $request->user()->id : null,
            'intake_waived_reason' => $request->intake_waived_reason,
            'capacity_override' => $request->boolean('capacity_override', false),
            'capacity_override_reason' => $request->capacity_override_reason,
            'check_in_now' => $request->boolean('check_in_now', false),
        ];
    }
}
