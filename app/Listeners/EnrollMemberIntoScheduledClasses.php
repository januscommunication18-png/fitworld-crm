<?php

namespace App\Listeners;

use App\Events\MembershipActivated;
use App\Services\ScheduledMembershipService;
use Illuminate\Support\Facades\Log;

class EnrollMemberIntoScheduledClasses
{
    protected ScheduledMembershipService $scheduledMembershipService;

    public function __construct(ScheduledMembershipService $scheduledMembershipService)
    {
        $this->scheduledMembershipService = $scheduledMembershipService;
    }

    public function handle(MembershipActivated $event): void
    {
        $membership = $event->membership;

        // Only process if membership plan has scheduled classes
        if (!$membership->membershipPlan?->has_scheduled_class) {
            return;
        }

        $results = $this->scheduledMembershipService->enrollMemberIntoScheduledClasses($membership);

        if (count($results['enrolled']) > 0) {
            Log::info('Auto-enrolled member into scheduled class sessions', [
                'membership_id' => $membership->id,
                'client_id' => $membership->client_id,
                'enrolled_count' => count($results['enrolled']),
                'skipped_count' => count($results['skipped']),
            ]);
        }
    }
}
