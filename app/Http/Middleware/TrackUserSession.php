<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $sessionId = session()->getId();

            // Update last activity for the session (throttled to every 5 minutes)
            $cacheKey = "session_activity_{$sessionId}";

            if (!cache()->has($cacheKey)) {
                UserSession::where('session_id', $sessionId)
                    ->where('is_active', true)
                    ->update(['last_activity_at' => now()]);

                cache()->put($cacheKey, true, now()->addMinutes(5));
            }
        }

        return $next($request);
    }
}
