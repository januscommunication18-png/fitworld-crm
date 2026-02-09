<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMustChangePassword
{
    /**
     * Routes that should be accessible even when password change is required.
     */
    protected array $except = [
        'backoffice/password/change',
        'backoffice/logout',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->must_change_password) {
            // Allow access to password change and logout routes
            foreach ($this->except as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }

            return redirect()->route('backoffice.password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        return $next($request);
    }
}
