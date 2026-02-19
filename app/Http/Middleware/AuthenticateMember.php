<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->attributes->get('subdomain_host');
        $subdomain = $host?->subdomain ?? $request->route('subdomain');

        // Check if member is authenticated
        if (!Auth::guard('member')->check()) {
            // Store intended URL for redirect after login
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            session()->put('url.intended', $request->url());

            return redirect()->route('member.login', ['subdomain' => $subdomain]);
        }

        $member = Auth::guard('member')->user();

        // Ensure member belongs to the current host
        if ($host && $member->host_id !== $host->id) {
            Auth::guard('member')->logout();
            return redirect()->route('member.login', ['subdomain' => $subdomain])
                ->with('error', 'Your account is not associated with this studio.');
        }

        // Check if member portal is enabled for this host
        if ($host && !$host->isMemberPortalEnabled()) {
            Auth::guard('member')->logout();
            return redirect()->route('subdomain.home', ['subdomain' => $subdomain])
                ->with('error', 'Member portal is not available at this time.');
        }

        // Check session timeout
        $sessionTimeout = $host?->getMemberPortalSetting('session_timeout_days', 30) ?? 30;
        $lastLogin = $member->portal_last_login_at;

        if ($lastLogin && $lastLogin->diffInDays(now()) > $sessionTimeout) {
            Auth::guard('member')->logout();
            return redirect()->route('member.login', ['subdomain' => $subdomain])
                ->with('info', 'Your session has expired. Please log in again.');
        }

        // Share member with views
        view()->share('member', $member);

        return $next($request);
    }
}
