<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSecurityCode
{
    /**
     * The valid security codes.
     */
    protected const VALID_CODES = ['1000', '2000', '3000', '4000'];

    /**
     * Routes that should be exempt from security code check.
     */
    protected array $exemptRoutes = [
        'security-code',
        'security-code.verify',
        'login',
        'logout',
        'signup',
        'invitation.show',
        'invitation.accept',
    ];

    /**
     * Route name prefixes that should be exempt from security code check.
     */
    protected array $exemptRoutePrefixes = [
        'subdomain.',  // All public booking page routes
    ];

    /**
     * URI patterns that should be exempt from security code check.
     */
    protected array $exemptPatterns = [
        'security-code*',
        '_debugbar/*',
        'livewire/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip if security code is disabled via config
        if (!config('app.security_code_enabled', true)) {
            return $next($request);
        }

        // Skip exempt routes
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Check if security code has been verified
        if (!session()->has('security_code_verified')) {
            // Store intended URL for redirect after verification
            if (!$request->is('security-code*')) {
                session()->put('url.intended', $request->url());
            }

            return redirect()->route('security-code');
        }

        return $next($request);
    }

    /**
     * Check if the request should bypass security code check.
     */
    protected function shouldPassThrough(Request $request): bool
    {
        // Check if route name is exempt
        $routeName = $request->route()?->getName();
        if ($routeName) {
            if (in_array($routeName, $this->exemptRoutes)) {
                return true;
            }

            // Check if route name matches any exempt prefix
            foreach ($this->exemptRoutePrefixes as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            }
        }

        // Check if URI matches exempt patterns
        foreach ($this->exemptPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if the given code is correct.
     */
    public static function verifyCode(string $code): bool
    {
        return in_array(trim($code), self::VALID_CODES, true);
    }
}
