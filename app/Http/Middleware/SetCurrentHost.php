<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentHost = $user->currentHost();

            // Share current host with all views
            View::share('currentHost', $currentHost);

            // Store on request for controllers
            $request->attributes->set('currentHost', $currentHost);

            // Also update the user's host relationship dynamically for legacy code
            if ($currentHost) {
                $user->setRelation('host', $currentHost);
            }
        }

        return $next($request);
    }
}
