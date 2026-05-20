<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Maps URL prefixes to nav.* permissions. If the authenticated user lacks the
 * permission for the matched prefix, return 403. Owners and unauthenticated
 * requests are passed through.
 *
 * Sidebar visibility (handled in Blade) uses the same nav.* keys, so when an
 * admin un-checks a nav permission, the menu hides AND direct URL access is
 * blocked.
 */
class EnforceNavPermission
{
    /**
     * Ordered list of [path_prefix, nav_permission].
     * First match wins. Longer prefixes must come BEFORE their shorter parents.
     */
    protected array $map = [
        // Settings sub-sections (specific paths first)
        ['settings/team/permissions', 'nav.settings.permissions'],
        ['settings/team/users', 'nav.settings.users'],
        ['settings/team/invite', 'nav.settings.users'],
        ['settings/team/invitations', 'nav.settings.users'],
        ['settings/team', 'nav.settings.users'],
        ['settings/billing', 'nav.settings.billing'],
        ['settings/payments', 'nav.settings.payments'],
        ['settings/notifications', 'nav.settings.communication'],
        ['settings/communication', 'nav.settings.communication'],
        ['settings/integrations', 'nav.settings.integrations'],
        ['settings/member-portal', 'nav.settings.client_portal'],
        ['settings/clients', 'nav.settings.client_portal'],
        ['settings/locations', 'nav.settings.studio'],
        ['settings/studio', 'nav.settings.studio'],
        // Settings root falls back to nav.settings
        ['settings', 'nav.settings'],

        // Main sidebar areas
        ['instructors', 'nav.instructors'],
        ['class-sessions', 'nav.schedule'],
        ['schedule', 'nav.schedule'],
        ['schedule-planner', 'nav.schedule'],
        ['service-slots', 'nav.schedule'],
        ['membership-schedules', 'nav.schedule'],
        ['scheduled-membership', 'nav.schedule'],
        ['space-rentals', 'nav.schedule'],
        ['catalog', 'nav.classes_services'],
        ['class-plans', 'nav.classes_services'],
        ['service-plans', 'nav.classes_services'],
        ['bookings', 'nav.bookings'],
        ['class-requests', 'nav.bookings'],
        ['waitlist', 'nav.bookings'],
        ['rentals/fulfillment', 'nav.bookings'],
        ['clients', 'nav.clients'],
        ['helpdesk', 'nav.helpdesk'],
        ['segments', 'nav.marketing'],
        ['offers', 'nav.marketing'],
        ['reports', 'nav.insights'],
        ['payments', 'nav.payments'],
    ];

    /**
     * Paths under the matched prefix that should always be accessible (e.g.
     * settings/profile is a personal page available to everyone).
     */
    protected array $whitelist = [
        'settings/profile',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Owner bypasses all checks
        if ($user->isOwner()) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        foreach ($this->whitelist as $allow) {
            if ($path === $allow || str_starts_with($path, $allow . '/')) {
                return $next($request);
            }
        }

        foreach ($this->map as [$prefix, $permission]) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                if (!$user->hasPermission($permission)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'You do not have permission to access this area.',
                        ], 403);
                    }
                    return redirect()->route('dashboard')
                        ->with('error', 'You do not have permission to access that area.');
                }
                break;
            }
        }

        return $next($request);
    }
}
