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
     * Ordered list of [path_prefix, permission|permissions[]].
     * First match wins. Longer prefixes must come BEFORE their shorter parents.
     * If an array is given, the user passes when they have ANY of the listed permissions.
     */
    protected array $map = [
        // Settings sub-sections (specific paths first) — gated by feature permissions
        ['settings/team/permissions', 'team.permissions'],
        ['settings/team/users', ['team.view', 'team.manage']],
        ['settings/team/invite', ['team.manage']],
        ['settings/team/invitations', ['team.manage']],
        ['settings/team', ['team.view', 'team.manage', 'team.instructors']],
        ['settings/billing', ['billing.plan', 'billing.invoices', 'billing.payment']],
        ['settings/payments', ['payments.stripe', 'payments.view', 'payments.refunds', 'payments.payouts']],
        ['settings/notifications', 'communication.manage'],
        ['settings/communication', 'communication.manage'],
        ['settings/integrations', 'integrations.manage'],
        ['settings/member-portal', 'studio.client_settings'],
        ['settings/clients', 'studio.client_settings'],
        ['settings/locations', ['studio.locations', 'studio.rooms', 'studio.booking_page', 'studio.policies']],
        ['settings/studio', 'studio.profile'],
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
                $required = (array) $permission;
                $allowed = false;
                foreach ($required as $perm) {
                    if ($user->hasPermission($perm)) {
                        $allowed = true;
                        break;
                    }
                }
                if (!$allowed) {
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
