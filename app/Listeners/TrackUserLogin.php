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

        // Only track for User model (studio staff), not Client (member portal)
        if ($user instanceof \App\Models\User && $user->host_id) {
            $this->sessionTrackingService->recordLogin($user, request());
        }
    }
}
