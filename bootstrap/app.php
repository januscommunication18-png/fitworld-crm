<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminHasPermission;
use App\Http\Middleware\AdminMustChangePassword;
use App\Http\Middleware\AdminOtpVerified;
use App\Http\Middleware\AuthenticateMember;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckSecurityCode;
use App\Http\Middleware\ResolveSubdomainHost;
use App\Http\Middleware\SetCurrentHost;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // IMPORTANT: Load subdomain routes FIRST (more specific domain pattern)
            // This must come before web.php so subdomain routes take precedence
            require base_path('routes/subdomain.php');

            // Then load web routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Register backoffice routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/backoffice.php'));

            // Load API routes with proper prefix and middleware
            \Illuminate\Support\Facades\Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Exclude public waitlist from CSRF (for embedded forms on external sites)
        $middleware->validateCsrfTokens(except: [
            'join-waitlist',
            'join-waitlist/*',
        ]);

        // Security code check (same as NewDone - append, not prepend)
        $middleware->appendToGroup('web', CheckSecurityCode::class);

        // Set current host context for multi-studio users
        $middleware->appendToGroup('web', SetCurrentHost::class);

        // Register admin middleware aliases
        $middleware->alias([
            'auth.admin' => AdminAuthenticate::class,
            'admin.otp.verified' => AdminOtpVerified::class,
            'admin.must.change.password' => AdminMustChangePassword::class,
            'admin.permission' => AdminHasPermission::class,
            'subdomain.host' => ResolveSubdomainHost::class,
            'permission' => CheckPermission::class,
            'auth.member' => AuthenticateMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
