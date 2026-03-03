<?php

namespace App\Listeners;

use App\Services\SessionTrackingService;
use Illuminate\Auth\Events\Login;

class TrackUserLogin
{
    public function __construct(
        protected SessionTrackingService $sessionTrackingService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Only track for users with a host_id (studio staff)
        if ($user->host_id) {
            $this->sessionTrackingService->recordLogin($user, request());
        }
    }
}
