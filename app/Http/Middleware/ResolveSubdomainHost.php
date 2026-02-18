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

        // Try exact match first (e.g., 'pulse-pilates')
        $host = Host::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        // If not found, try with full domain suffix (e.g., 'pulse-pilates.fitcrm.biz')
        if (!$host) {
            $bookingDomain = config('app.booking_domain', 'fitcrm.biz');
            $fullSubdomain = $subdomain . '.' . $bookingDomain;

            $host = Host::where('subdomain', $fullSubdomain)
                ->where('status', 'active')
                ->first();
        }

        if (!$host) {
            return response()->view('subdomain.errors.studio-not-found', [
                'subdomain' => $subdomain,
            ], 404);
        }

        $request->attributes->set('subdomain_host', $host);

        return $next($request);
    }
}
