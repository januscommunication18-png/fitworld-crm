<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current studio for token (mobile) requests.
 *
 * Unlike the web flow — which keeps the current studio in the session
 * (see SetCurrentHost) — bearer-token clients are stateless, so the chosen
 * studio is carried per-request via the `X-Studio-Id` header (falling back to
 * a `studio_id` input, then the user's primary studio).
 *
 * The resolved Host is validated against the user's memberships and exposed as
 * the `currentHost` request attribute and as the user's `host` relation, so
 * controllers/services can host-scope queries consistently.
 *
 * Apply AFTER `auth:sanctum`. Alias: `studio.context`.
 */
class ResolveStudioContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $studioId = $request->header('X-Studio-Id') ?? $request->input('studio_id');

        if ($studioId) {
            $host = $user->hosts()->where('hosts.id', $studioId)->first();

            // Legacy single-host fallback (user.host_id without a pivot row).
            if (! $host && (int) $user->host_id === (int) $studioId) {
                $host = $user->host;
            }

            if (! $host) {
                return response()->json([
                    'message' => 'You do not have access to this studio.',
                ], 403);
            }
        } else {
            $host = $user->getPrimaryHost();
        }

        if (! $host) {
            return response()->json([
                'message' => 'No studio context available for this account.',
            ], 403);
        }

        $request->attributes->set('currentHost', $host);
        $user->setRelation('host', $host);

        // Keep session-based code paths working when a session is present.
        if ($request->hasSession()) {
            $request->session()->put('current_host_id', $host->id);
        }

        return $next($request);
    }
}
