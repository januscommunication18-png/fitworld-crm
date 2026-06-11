<?php

namespace App\Http\Middleware;

use App\Models\Host;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the studio for branded client app (consumer) API requests.
 *
 * Every white-label app build ships with its studio's app token; the app
 * sends it on every request via the `X-Studio-App-Token` header. The token
 * identifies the studio — it is not a user credential (clients authenticate
 * with Sanctum bearer tokens on top of this).
 *
 * The resolved Host is exposed as the `currentHost` request attribute —
 * the same attribute name the staff API uses — so shared/host-scoped code
 * works unchanged. Alias: `client.app`.
 */
class ResolveClientAppHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->header('X-Studio-App-Token', '');

        if ($token === '') {
            return response()->json(['message' => 'Missing studio app token.'], 401);
        }

        $host = Host::where('client_app_token', $token)->first();

        if (! $host) {
            return response()->json(['message' => 'Invalid studio app token.'], 401);
        }

        if (! $host->isClientAppEnabled()) {
            return response()->json([
                'message' => 'The client app is not enabled for this studio.',
            ], 403);
        }

        $request->attributes->set('currentHost', $host);

        return $next($request);
    }
}
