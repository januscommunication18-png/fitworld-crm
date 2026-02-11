<?php

namespace App\Http\Middleware;

use App\Models\Host;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSubdomainHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        if (!$subdomain) {
            return response()->view('subdomain.errors.studio-not-found', [], 404);
        }

        $host = Host::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        if (!$host) {
            return response()->view('subdomain.errors.studio-not-found', [
                'subdomain' => $subdomain,
            ], 404);
        }

        $request->attributes->set('subdomain_host', $host);

        return $next($request);
    }
}
