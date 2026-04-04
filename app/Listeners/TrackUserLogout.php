<?php

namespace App\Listeners;

use App\Services\SessionTrackingService;
use Illuminate\Auth\Events\Logout;

class TrackUserLogout
{
    public function __construct(
        protected SessionTrackingService $sessionTrackingService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user instanceof \App\Models\User && $user->host_id) {
            $this->sessionTrackingService->recordLogout($user);
        }
    }
}
