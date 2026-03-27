<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Host;
use App\Models\Location;
use App\Models\PriceOverrideRequest;
use App\Models\User;
use App\Notifications\PriceOverrideRequestNotification;
use App\Notifications\PriceOverrideApprovedNotification;
use App\Notifications\PriceOverrideRejectedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceOverrideService
{
    /**
     * Create a new price override request
     */
    public function createRequest(
        Host $host,
        User $requester,
        float $originalPrice,
        float $requestedPrice,
        ?Location $location = null,
        ?Model $bookable = null,
        ?Client $client = null,
        ?string $discountCode = null,
        ?string $reason = null,
        ?array $metadata = null
    ): PriceOverrideRequest {
        // Find the appropriate manager for this request
        $manager = $this->findApprover($host, $location);

        $request = PriceOverrideRequest::create([
            'host_id' => $host->id,
            'location_id' => $location?->id,
            'requested_by' => $requester->id,
            'manager_id' => $manager?->id,
            'bookable_type' => $bookable ? get_class($bookable) : null,
            'bookable_id' => $bookable?->id,
            'client_id' => $client?->id,
            'original_price' => $originalPrice,
            'requested_price' => $requestedPrice,
            'discount_code' => $discountCode,
            'reason' => $reason,
            'status' => PriceOverrideRequest::STATUS_PENDING,
            'metadata' => $metadata,
        ]);

        // Send notification to manager
        if ($manager) {
            $this->notifyManager($request);
        }

        return $request;
    }

    /**
     * Find the appropriate approver for the request
     * Priority: Location Manager > Admin > Owner
     */
    public function findApprover(Host $host, ?Location $location = null): ?User
    {
        // First, check if location has assigned managers
        if ($location && !empty($location->manager_ids)) {
            $managerIds = $location->manager_ids;

            // Find a manager who has pricing.override permission or is owner/admin
            $manager = User::whereIn('id', $managerIds)
                ->where(function ($query) use ($host) {
                    $query->whereHas('hosts', function ($q) use ($host) {
                        $q->where('hosts.id', $host->id)
                            ->where(function ($inner) {
                                // Owner or Admin can approve
                                $inner->where('host_user.role', 'owner')
                                    ->orWhere('host_user.role', 'admin')
                                    ->orWhereJsonContains('host_user.permissions', 'pricing.override');
                            });
                    });
                })
                ->first();

            if ($manager) {
                return $manager;
            }
        }

        // Fall back to any admin
        $admin = User::whereHas('hosts', function ($q) use ($host) {
            $q->where('hosts.id', $host->id)
                ->where('host_user.role', 'admin');
        })->first();

        if ($admin) {
            return $admin;
        }

        // Fall back to owner
        return User::whereHas('hosts', function ($q) use ($host) {
            $q->where('hosts.id', $host->id)
                ->where('host_user.role', 'owner');
        })->first();
    }

    /**
     * Notify manager about new override request
     */
    public function notifyManager(PriceOverrideRequest $request): void
    {
        if (!$request->manager) {
            return;
        }

        try {
            $request->manager->notify(new PriceOverrideRequestNotification($request));
        } catch (\Exception $e) {
            Log::error('Failed to send price override notification', [
                'request_id' => $request->id,
                'manager_id' => $request->manager_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve an override request
     */
    public function approve(PriceOverrideRequest $request, User $approver): bool
    {
        if (!$this->canAction($request, $approver)) {
            return false;
        }

        $result = $request->approve($approver);

        if ($result) {
            // Notify the requester
            try {
                $request->requester->notify(new PriceOverrideApprovedNotification($request));
            } catch (\Exception $e) {
                Log::error('Failed to send approval notification', [
                    'request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * Reject an override request
     */
    public function reject(PriceOverrideRequest $request, User $rejecter, ?string $reason = null): bool
    {
        if (!$this->canAction($request, $rejecter)) {
            return false;
        }

        $result = $request->reject($rejecter, $reason);

        if ($result) {
            // Notify the requester
            try {
                $request->requester->notify(new PriceOverrideRejectedNotification($request));
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification', [
                    'request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * Check if user can action (approve/reject) a request
     */
    public function canAction(PriceOverrideRequest $request, User $user): bool
    {
        // Must be for same host
        $host = $request->host;

        // Owner can always approve
        if ($user->isOwner($host)) {
            return true;
        }

        // Admin can always approve
        if ($user->isAdmin($host)) {
            return true;
        }

        // Assigned manager can approve
        if ($request->manager_id === $user->id) {
            return true;
        }

        // Location manager can approve
        if ($request->location && in_array($user->id, $request->location->manager_ids ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can request price override
     * Any team member can REQUEST an override - only managers with pricing.override permission can APPROVE/REJECT
     */
    public function canRequestOverride(Host $host, User $user): bool
    {
        // Check if feature is enabled for host
        if (!$host->hasFeature('price-override')) {
            return false;
        }

        // Any team member of this host can request a price override
        // Check if user has any role in this host (meaning they're a team member)
        $role = $user->getRoleForHost($host);
        return $role !== null;
    }

    /**
     * Verify confirmation code and return the request if valid
     */
    public function verifyCode(string $code, Host $host): ?PriceOverrideRequest
    {
        $request = PriceOverrideRequest::where('host_id', $host->id)
            ->where('confirmation_code', strtoupper($code))
            ->where('status', PriceOverrideRequest::STATUS_APPROVED)
            ->first();

        if (!$request) {
            return null;
        }

        // Check if expired (approved requests may have different expiry rules)
        // For now, approved requests are valid until used
        return $request;
    }

    /**
     * Verify a personal override code
     * Personal codes start with "MY-" and belong to specific users with pricing.override permission
     * Returns the user if valid, null otherwise
     */
    public function verifyPersonalCode(string $code, Host $host): ?User
    {
        $code = strtoupper(trim($code));

        // Personal codes start with "MY-"
        if (!str_starts_with($code, 'MY-')) {
            return null;
        }

        // Find user with this personal code for this host
        $user = User::whereHas('hosts', function ($query) use ($host, $code) {
            $query->where('hosts.id', $host->id)
                  ->where('host_user.personal_override_code', $code);
        })->first();

        if (!$user) {
            return null;
        }

        // Verify the user still has permission to approve overrides
        if (!$user->canApprovePriceOverride($host)) {
            return null;
        }

        return $user;
    }

    /**
     * Check if a code is a personal override code format
     */
    public function isPersonalCode(string $code): bool
    {
        return str_starts_with(strtoupper(trim($code)), 'MY-');
    }

    /**
     * Generate a unique personal override code for a user
     */
    public function generatePersonalCode(): string
    {
        do {
            $code = 'MY-' . strtoupper(\Illuminate\Support\Str::random(5));
        } while (\DB::table('host_user')->where('personal_override_code', $code)->exists());

        return $code;
    }

    /**
     * Assign or regenerate personal override code for a user
     */
    public function assignPersonalCode(User $user, Host $host): ?string
    {
        // Only assign to users who can approve overrides
        if (!$user->canApprovePriceOverride($host)) {
            return null;
        }

        $code = $this->generatePersonalCode();

        \DB::table('host_user')
            ->where('user_id', $user->id)
            ->where('host_id', $host->id)
            ->update(['personal_override_code' => $code]);

        return $code;
    }

    /**
     * Log a direct override (when manager/owner uses direct price edit)
     */
    public function logDirectOverride(array $data): ?PriceOverrideRequest
    {
        $host = Host::find($data['host_id']);
        if (!$host) {
            return null;
        }

        // Generate a unique confirmation code for this usage
        $confirmationCode = 'DO-' . strtoupper(\Illuminate\Support\Str::random(5));

        return PriceOverrideRequest::create([
            'host_id' => $data['host_id'],
            'location_id' => $data['location_id'] ?? null,
            'requested_by' => $data['user_id'], // User who made the override
            'actioned_by' => $data['user_id'], // Same user approved it
            'bookable_type' => $data['bookable_type'] ?? null,
            'bookable_id' => $data['bookable_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'original_price' => $data['original_price'],
            'requested_price' => $data['new_price'],
            'confirmation_code' => $confirmationCode,
            'status' => PriceOverrideRequest::STATUS_DIRECT,
            'approved_at' => now(),
            'expires_at' => null, // Direct overrides don't expire
            'metadata' => array_merge($data['metadata'] ?? [], [
                'direct_override' => true,
                'applied_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Log a personal override usage (when manager's code is used directly)
     */
    public function logPersonalOverride(array $data): ?PriceOverrideRequest
    {
        $personalCode = strtoupper(trim($data['personal_code'] ?? ''));

        if (!$this->isPersonalCode($personalCode)) {
            return null;
        }

        $host = Host::find($data['host_id']);
        if (!$host) {
            return null;
        }

        // Find the code owner
        $codeOwner = $this->verifyPersonalCode($personalCode, $host);
        if (!$codeOwner) {
            return null;
        }

        // Generate a unique confirmation code for this usage
        // The personal code is stored in metadata, not as confirmation_code
        $confirmationCode = PriceOverrideRequest::generateConfirmationCode();

        return PriceOverrideRequest::create([
            'host_id' => $data['host_id'],
            'location_id' => $data['location_id'] ?? null,
            'requested_by' => $data['requested_by'], // Staff who applied the override
            'manager_id' => $codeOwner->id, // Code owner
            'actioned_by' => $codeOwner->id,
            'bookable_type' => $data['bookable_type'] ?? null,
            'bookable_id' => $data['bookable_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'original_price' => $data['original_price'],
            'requested_price' => $data['new_price'],
            'confirmation_code' => $confirmationCode,
            'status' => PriceOverrideRequest::STATUS_PERSONAL_APPROVED,
            'approved_at' => now(),
            'expires_at' => null, // Personal approvals don't expire
            'metadata' => [
                'personal_code_used' => true,
                'personal_code' => $personalCode,
                'applied_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Apply approved override to booking
     * Returns the discount amount applied
     */
    public function applyOverride(PriceOverrideRequest $request): float
    {
        if (!$request->is_approved) {
            return 0;
        }

        // Mark as used in metadata
        $metadata = $request->metadata ?? [];
        $metadata['applied_at'] = now()->toIso8601String();
        $request->update(['metadata' => $metadata]);

        return $request->discount_amount;
    }

    /**
     * Get pending requests for a manager
     */
    public function getPendingForManager(User $manager, Host $host): \Illuminate\Database\Eloquent\Collection
    {
        return PriceOverrideRequest::where('host_id', $host->id)
            ->where(function ($query) use ($manager) {
                $query->where('manager_id', $manager->id)
                    ->orWhereHas('location', function ($q) use ($manager) {
                        $q->whereJsonContains('manager_ids', $manager->id);
                    });
            })
            ->active()
            ->with(['requester', 'client', 'location', 'bookable'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all pending requests for a host (for owner/admin)
     */
    public function getPendingForHost(Host $host): \Illuminate\Database\Eloquent\Collection
    {
        return PriceOverrideRequest::where('host_id', $host->id)
            ->active()
            ->with(['requester', 'manager', 'client', 'location', 'bookable'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get request history for a host
     */
    public function getHistory(Host $host, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return PriceOverrideRequest::where('host_id', $host->id)
            ->with(['requester', 'manager', 'actionedBy', 'client', 'location', 'bookable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Expire old pending requests
     * This should be called by a scheduled command
     */
    public function expirePendingRequests(): int
    {
        $expired = PriceOverrideRequest::expired()->get();
        $count = 0;

        foreach ($expired as $request) {
            if ($request->markExpired()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats(Host $host): array
    {
        $baseQuery = PriceOverrideRequest::where('host_id', $host->id);

        // Include regular approvals, personal code approvals, and direct overrides
        $approvedStatuses = [
            PriceOverrideRequest::STATUS_APPROVED,
            PriceOverrideRequest::STATUS_PERSONAL_APPROVED,
            PriceOverrideRequest::STATUS_DIRECT,
        ];

        return [
            'pending' => (clone $baseQuery)->active()->count(),
            'approved_today' => (clone $baseQuery)
                ->whereIn('status', $approvedStatuses)
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => (clone $baseQuery)
                ->where('status', PriceOverrideRequest::STATUS_REJECTED)
                ->whereDate('rejected_at', today())
                ->count(),
            'total_discount_today' => (clone $baseQuery)
                ->whereIn('status', $approvedStatuses)
                ->whereDate('approved_at', today())
                ->selectRaw('SUM(original_price - requested_price) as total')
                ->value('total') ?? 0,
        ];
    }
}
