<?php

namespace App\Listeners;

use App\Events\ClassSessionPublished;
use App\Services\ScheduledMembershipService;
use Illuminate\Support\Facades\Log;

class EnrollScheduledMembersIntoSession
{
    protected ScheduledMembershipService $scheduledMembershipService;

    public function __construct(ScheduledMembershipService $scheduledMembershipService)
    {
        $this->scheduledMembershipService = $scheduledMembershipService;
    }

    public function handle(ClassSessionPublished $event): void
    {
        $session = $event->session;

        // Only process if session is upcoming
        if ($session->start_time->isPast()) {
            return;
        }

        $results = $this->scheduledMembershipService->enrollScheduledMembersIntoSession($session);

        if (count($results['enrolled']) > 0) {
            Log::info('Auto-enrolled members into scheduled class session', [
                'session_id' => $session->id,
                'session_title' => $session->display_title,
                'enrolled_count' => count($results['enrolled']),
                'skipped_count' => count($results['skipped']),
            ]);
        }
    }
}
