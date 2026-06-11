<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenancy guard for the branded client app API. Apply AFTER `auth:sanctum`.
 *
 * Sanctum resolves whatever model a bearer token belongs to, so this guard
 * enforces both halves of the contract: the token must belong to a Client
 * (staff User tokens are rejected) and that client must belong to the studio
 * resolved from the app token (a client of studio A presenting their token
 * inside studio B's app is rejected). Alias: `client.scope`.
 */
class EnsureClientOfHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $host = $request->attributes->get('currentHost');

        if (! $user instanceof Client || ! $host || (int) $user->host_id !== (int) $host->id) {
            return response()->json(['message' => 'This account cannot access this studio.'], 403);
        }

        return $next($request);
    }
}
