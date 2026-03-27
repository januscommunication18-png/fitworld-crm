<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Location;
use App\Models\PriceOverrideRequest;
use App\Services\PriceOverrideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PriceOverrideController extends Controller
{
    public function __construct(
        private PriceOverrideService $priceOverrideService
    ) {}

    /**
     * Display the price override management page
     */
    public function index()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Check if user can action overrides (owner, admin, or manager)
        $canApprove = $user->canApprovePriceOverride($host);

        // Get pending requests
        if ($user->isOwner($host) || $user->isAdmin($host)) {
            $pendingRequests = $this->priceOverrideService->getPendingForHost($host);
        } else {
            $pendingRequests = $this->priceOverrideService->getPendingForManager($user, $host);
        }

        // Get history
        $history = $this->priceOverrideService->getHistory($host, 50);

        // Get stats
        $stats = $this->priceOverrideService->getStats($host);

        // Get personal override code if user can approve
        $personalOverrideCode = $canApprove ? $user->getPersonalOverrideCode($host) : null;

        return view('host.price-override.index', compact(
            'pendingRequests',
            'history',
            'stats',
            'canApprove',
            'personalOverrideCode'
        ));
    }

    /**
     * Review a specific price override request (from email link)
     */
    public function review(string $code)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $request = PriceOverrideRequest::where('host_id', $host->id)
            ->where('confirmation_code', strtoupper($code))
            ->with(['requester', 'manager', 'client', 'location', 'bookable'])
            ->first();

        if (!$request) {
            return redirect()->route('price-override.index')
                ->with('error', 'Override request not found.');
        }

        // Check if user can action this request
        $canAction = $this->priceOverrideService->canAction($request, $user);

        return view('host.price-override.review', compact('request', 'canAction'));
    }

    /**
     * Create a new price override request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Check if user can request override
        if (!$this->priceOverrideService->canRequestOverride($host, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to request price overrides.',
            ], 403);
        }

        $validated = $request->validate([
            'original_price' => 'required|numeric|min:0',
            'requested_price' => 'required|numeric|min:0|lt:original_price',
            'location_id' => 'nullable|exists:locations,id',
            'client_id' => 'nullable|exists:clients,id',
            'discount_code' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:500',
            'bookable_type' => 'nullable|string',
            'bookable_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        try {
            $location = !empty($validated['location_id'])
                ? Location::where('host_id', $host->id)->find($validated['location_id'])
                : null;

            $client = !empty($validated['client_id'])
                ? Client::where('host_id', $host->id)->find($validated['client_id'])
                : null;

            // Resolve bookable if provided
            $bookable = null;
            if (!empty($validated['bookable_type']) && !empty($validated['bookable_id'])) {
                $bookableClass = $validated['bookable_type'];
                if (class_exists($bookableClass)) {
                    $bookable = $bookableClass::find($validated['bookable_id']);
                }
            }

            $overrideRequest = $this->priceOverrideService->createRequest(
                host: $host,
                requester: $user,
                originalPrice: $validated['original_price'],
                requestedPrice: $validated['requested_price'],
                location: $location,
                bookable: $bookable,
                client: $client,
                discountCode: $validated['discount_code'] ?? null,
                reason: $validated['reason'] ?? null,
                metadata: $validated['metadata'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Price override request submitted successfully.',
                'data' => [
                    'id' => $overrideRequest->id,
                    'confirmation_code' => $overrideRequest->confirmation_code,
                    'status' => $overrideRequest->status,
                    'expires_at' => $overrideRequest->expires_at->toIso8601String(),
                    'manager' => $overrideRequest->manager ? [
                        'id' => $overrideRequest->manager->id,
                        'name' => $overrideRequest->manager->name,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create price override request', [
                'user_id' => $user->id,
                'host_id' => $host->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create override request. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify a confirmation code (either personal code or override request code)
     */
    public function verify(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'new_price' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($validated['code']));

        // Check if this is a personal override code (MY-XXXXX)
        if ($this->priceOverrideService->isPersonalCode($code)) {
            $codeOwner = $this->priceOverrideService->verifyPersonalCode($code, $host);

            if (!$codeOwner) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Invalid personal override code.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'valid' => true,
                'is_personal_code' => true,
                'code' => $code,
                'data' => [
                    'code' => $code,
                    'is_personal_code' => true,
                    'authorized_by' => [
                        'id' => $codeOwner->id,
                        'name' => $codeOwner->name,
                    ],
                    'message' => 'Personal override code verified. Price can be changed by ' . $codeOwner->name,
                ],
            ]);
        }

        // Otherwise, check as regular override request code
        $overrideRequest = PriceOverrideRequest::where('host_id', $host->id)
            ->where('confirmation_code', $code)
            ->with(['requester', 'manager', 'actionedBy', 'client', 'location'])
            ->first();

        if (!$overrideRequest) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid confirmation code.',
            ], 404);
        }

        // Check status
        if ($overrideRequest->status === PriceOverrideRequest::STATUS_EXPIRED || $overrideRequest->is_expired) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'This override request has expired.',
                'status' => 'expired',
            ], 400);
        }

        if ($overrideRequest->status === PriceOverrideRequest::STATUS_REJECTED) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'This override request was rejected.',
                'status' => 'rejected',
                'rejection_reason' => $overrideRequest->rejection_reason,
            ], 400);
        }

        if ($overrideRequest->status === PriceOverrideRequest::STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'This override request was cancelled.',
                'status' => 'cancelled',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'is_personal_code' => false,
            'code' => $overrideRequest->confirmation_code,
            'requested_price' => $overrideRequest->requested_price,
            'data' => [
                'id' => $overrideRequest->id,
                'confirmation_code' => $overrideRequest->confirmation_code,
                'status' => $overrideRequest->status,
                'status_label' => $overrideRequest->status_label,
                'original_price' => $overrideRequest->original_price,
                'requested_price' => $overrideRequest->requested_price,
                'discount_amount' => $overrideRequest->discount_amount,
                'discount_percentage' => $overrideRequest->discount_percentage,
                'discount_code' => $overrideRequest->discount_code,
                'reason' => $overrideRequest->reason,
                'expires_at' => $overrideRequest->expires_at?->toIso8601String(),
                'is_pending' => $overrideRequest->is_pending,
                'is_approved' => $overrideRequest->is_approved,
                'requester' => [
                    'id' => $overrideRequest->requester->id,
                    'name' => $overrideRequest->requester->name,
                ],
                'manager' => $overrideRequest->manager ? [
                    'id' => $overrideRequest->manager->id,
                    'name' => $overrideRequest->manager->name,
                ] : null,
                'actioned_by' => $overrideRequest->actionedBy ? [
                    'id' => $overrideRequest->actionedBy->id,
                    'name' => $overrideRequest->actionedBy->name,
                ] : null,
                'client' => $overrideRequest->client ? [
                    'id' => $overrideRequest->client->id,
                    'name' => $overrideRequest->client->full_name,
                ] : null,
                'location' => $overrideRequest->location ? [
                    'id' => $overrideRequest->location->id,
                    'name' => $overrideRequest->location->name,
                ] : null,
                'approved_at' => $overrideRequest->approved_at?->toIso8601String(),
                'created_at' => $overrideRequest->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get current user's personal override code (if they have one)
     */
    public function getPersonalCode()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Check if user can approve overrides
        if (!$user->canApprovePriceOverride($host)) {
            return response()->json([
                'success' => false,
                'has_code' => false,
                'message' => 'You do not have permission to use personal override codes.',
            ]);
        }

        $code = $user->getPersonalOverrideCode($host);

        // If no code exists, generate one
        if (!$code) {
            $code = $this->priceOverrideService->assignPersonalCode($user, $host);
        }

        return response()->json([
            'success' => true,
            'has_code' => (bool) $code,
            'code' => $code,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ]);
    }

    /**
     * Approve a price override request
     */
    public function approve(Request $request, PriceOverrideRequest $priceOverrideRequest)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify request belongs to this host
        if ($priceOverrideRequest->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found.',
            ], 404);
        }

        // Check if user can approve
        if (!$this->priceOverrideService->canAction($priceOverrideRequest, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve this request.',
            ], 403);
        }

        $result = $this->priceOverrideService->approve($priceOverrideRequest, $user);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to approve request. It may have expired or already been processed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price override approved successfully.',
            'data' => [
                'confirmation_code' => $priceOverrideRequest->confirmation_code,
                'approved_price' => $priceOverrideRequest->requested_price,
                'discount_amount' => $priceOverrideRequest->discount_amount,
            ],
        ]);
    }

    /**
     * Reject a price override request
     */
    public function reject(Request $request, PriceOverrideRequest $priceOverrideRequest)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify request belongs to this host
        if ($priceOverrideRequest->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found.',
            ], 404);
        }

        // Check if user can reject
        if (!$this->priceOverrideService->canAction($priceOverrideRequest, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject this request.',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->priceOverrideService->reject(
            $priceOverrideRequest,
            $user,
            $validated['reason'] ?? null
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to reject request. It may have already been processed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price override rejected.',
        ]);
    }

    /**
     * Cancel a price override request (by requester)
     */
    public function cancel(PriceOverrideRequest $priceOverrideRequest)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify request belongs to this host
        if ($priceOverrideRequest->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found.',
            ], 404);
        }

        // Only requester, owner, or admin can cancel
        if ($priceOverrideRequest->requested_by !== $user->id
            && !$user->isOwner($host)
            && !$user->isAdmin($host)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this request.',
            ], 403);
        }

        $result = $priceOverrideRequest->cancel();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to cancel request. It may have already been processed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price override request cancelled.',
        ]);
    }

    /**
     * Get the current status of a price override request
     */
    public function status(PriceOverrideRequest $priceOverrideRequest)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify request belongs to this host
        if ($priceOverrideRequest->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found.',
            ], 404);
        }

        // Check if expired
        if ($priceOverrideRequest->status === PriceOverrideRequest::STATUS_PENDING && $priceOverrideRequest->is_expired) {
            $priceOverrideRequest->markExpired();
        }

        return response()->json([
            'success' => true,
            'id' => $priceOverrideRequest->id,
            'code' => $priceOverrideRequest->confirmation_code,
            'status' => $priceOverrideRequest->status,
            'status_label' => $priceOverrideRequest->status_label,
            'original_price' => $priceOverrideRequest->original_price,
            'requested_price' => $priceOverrideRequest->requested_price,
            'is_pending' => $priceOverrideRequest->is_pending,
            'is_approved' => $priceOverrideRequest->is_approved,
            'is_rejected' => $priceOverrideRequest->is_rejected,
            'is_expired' => $priceOverrideRequest->is_expired,
            'rejection_reason' => $priceOverrideRequest->rejection_reason,
        ]);
    }

    /**
     * Get pending requests for approval (manager view)
     */
    public function pending()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Owner/Admin see all pending
        if ($user->isOwner($host) || $user->isAdmin($host)) {
            $requests = $this->priceOverrideService->getPendingForHost($host);
        } else {
            $requests = $this->priceOverrideService->getPendingForManager($user, $host);
        }

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'confirmation_code' => $request->confirmation_code,
                    'status' => $request->status,
                    'status_label' => $request->status_label,
                    'original_price' => $request->original_price,
                    'requested_price' => $request->requested_price,
                    'discount_amount' => $request->discount_amount,
                    'discount_percentage' => $request->discount_percentage,
                    'discount_code' => $request->discount_code,
                    'reason' => $request->reason,
                    'expires_at' => $request->expires_at?->toIso8601String(),
                    'time_remaining' => $request->expires_at?->diffForHumans(),
                    'requester' => [
                        'id' => $request->requester->id,
                        'name' => $request->requester->name,
                    ],
                    'client' => $request->client ? [
                        'id' => $request->client->id,
                        'name' => $request->client->full_name,
                    ] : null,
                    'location' => $request->location ? [
                        'id' => $request->location->id,
                        'name' => $request->location->name,
                    ] : null,
                    'created_at' => $request->created_at->toIso8601String(),
                ];
            }),
            'count' => $requests->count(),
        ]);
    }

    /**
     * Get request history
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $limit = $request->input('limit', 50);
        $requests = $this->priceOverrideService->getHistory($host, $limit);

        return response()->json([
            'success' => true,
            'data' => $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'confirmation_code' => $request->confirmation_code,
                    'status' => $request->status,
                    'status_label' => $request->status_label,
                    'status_badge_class' => $request->status_badge_class,
                    'original_price' => $request->original_price,
                    'requested_price' => $request->requested_price,
                    'discount_amount' => $request->discount_amount,
                    'discount_percentage' => $request->discount_percentage,
                    'discount_code' => $request->discount_code,
                    'reason' => $request->reason,
                    'rejection_reason' => $request->rejection_reason,
                    'requester' => [
                        'id' => $request->requester->id,
                        'name' => $request->requester->name,
                    ],
                    'manager' => $request->manager ? [
                        'id' => $request->manager->id,
                        'name' => $request->manager->name,
                    ] : null,
                    'actioned_by' => $request->actionedBy ? [
                        'id' => $request->actionedBy->id,
                        'name' => $request->actionedBy->name,
                    ] : null,
                    'client' => $request->client ? [
                        'id' => $request->client->id,
                        'name' => $request->client->full_name,
                    ] : null,
                    'location' => $request->location ? [
                        'id' => $request->location->id,
                        'name' => $request->location->name,
                    ] : null,
                    'approved_at' => $request->approved_at?->toIso8601String(),
                    'rejected_at' => $request->rejected_at?->toIso8601String(),
                    'created_at' => $request->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Get stats for dashboard widget
     */
    public function stats()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $stats = $this->priceOverrideService->getStats($host);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Check if user can request override (for UI)
     */
    public function canRequest()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $canRequest = $this->priceOverrideService->canRequestOverride($host, $user);

        return response()->json([
            'success' => true,
            'can_request' => $canRequest,
            'feature_enabled' => $host->hasFeature('price-override'),
            'has_permission' => $user->hasHostPermission($host, 'pricing.override'),
        ]);
    }

    /**
     * Fetch approved override for a specific booking (for current user)
     */
    public function fetchApproved(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $validated = $request->validate([
            'bookable_type' => 'required|string',
            'bookable_id' => 'required|integer',
        ]);

        // Find approved override for this user and booking
        $overrideRequest = PriceOverrideRequest::where('host_id', $host->id)
            ->where('requested_by', $user->id)
            ->where('bookable_type', $validated['bookable_type'])
            ->where('bookable_id', $validated['bookable_id'])
            ->where('status', PriceOverrideRequest::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNull('used_at') // Not yet used
            ->orderBy('approved_at', 'desc')
            ->first();

        if (!$overrideRequest) {
            return response()->json([
                'success' => false,
                'message' => 'No approved override found for this booking.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $overrideRequest->id,
                'confirmation_code' => $overrideRequest->confirmation_code,
                'status' => $overrideRequest->status,
                'original_price' => $overrideRequest->original_price,
                'requested_price' => $overrideRequest->requested_price,
                'discount_amount' => $overrideRequest->discount_amount,
                'discount_percentage' => $overrideRequest->discount_percentage,
                'discount_code' => $overrideRequest->discount_code,
                'is_approved' => true,
                'approved_at' => $overrideRequest->approved_at?->toIso8601String(),
                'expires_at' => $overrideRequest->expires_at?->toIso8601String(),
            ],
        ]);
    }
}
