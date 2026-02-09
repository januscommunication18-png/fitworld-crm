<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminHasPermission;
use App\Http\Middleware\AdminMustChangePassword;
use App\Http\Middleware\AdminOtpVerified;
use App\Http\Middleware\CheckSecurityCode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Register backoffice routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/backoffice.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Security code check (same as NewDone - append, not prepend)
        $middleware->appendToGroup('web', CheckSecurityCode::class);

        // Register admin middleware aliases
        $middleware->alias([
            'auth.admin' => AdminAuthenticate::class,
            'admin.otp.verified' => AdminOtpVerified::class,
            'admin.must.change.password' => AdminMustChangePassword::class,
            'admin.permission' => AdminHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
