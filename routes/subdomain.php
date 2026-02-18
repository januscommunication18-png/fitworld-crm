<?php

use App\Http\Controllers\SubdomainSetupController;
use App\Http\Controllers\Subdomain\BookingController;
use App\Http\Controllers\Subdomain\ClassRequestController;
use App\Http\Controllers\Subdomain\ServiceRequestController;
use App\Http\Controllers\Subdomain\WaitlistClaimController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Subdomain Routes
|--------------------------------------------------------------------------
|
| These routes handle the public-facing booking pages for each studio.
| They are accessible via {subdomain}.{BOOKING_DOMAIN}
|
| Local:      faizans-studio.projectfit.local:8888
| Production: faizans-studio.fitcrm.biz
|
| Set BOOKING_DOMAIN in .env (without port - Laravel domain matching ignores port):
|   Local:      BOOKING_DOMAIN=projectfit.local
|   Production: BOOKING_DOMAIN=fitcrm.biz
|
*/

Route::domain('{subdomain}.' . config('app.booking_domain', 'fitcrm.biz'))
    ->middleware(['web', 'subdomain.host'])
    ->group(function () {
        // Public booking page - landing/home
        Route::get('/', [BookingController::class, 'index'])->name('subdomain.home');

        // Schedule view
        Route::get('/schedule', [BookingController::class, 'schedule'])->name('subdomain.schedule');

        // Class details
        Route::get('/class/{classSession}', [BookingController::class, 'classDetails'])->name('subdomain.class');

        // Instructor profiles
        Route::get('/instructors', [BookingController::class, 'instructors'])->name('subdomain.instructors');
        Route::get('/instructor/{instructor}', [BookingController::class, 'instructorProfile'])->name('subdomain.instructor');

        // Service request
        Route::post('/request-service', [ServiceRequestController::class, 'store'])->name('subdomain.request-service');

        // Class request
        Route::get('/class-request', [ClassRequestController::class, 'create'])->name('subdomain.class-request');
        Route::get('/class-request/success', [ClassRequestController::class, 'success'])->name('subdomain.class-request.success');
        Route::get('/class-request/{sessionId}', [ClassRequestController::class, 'create'])->name('subdomain.class-request.session')->where('sessionId', '[0-9]+');
        Route::post('/class-request', [ClassRequestController::class, 'store'])->name('subdomain.class-request.store');

        // Waitlist claim (for clients to claim offered spots)
        Route::get('/claim/{token}', [WaitlistClaimController::class, 'show'])->name('subdomain.waitlist-claim');
        Route::post('/claim/{token}', [WaitlistClaimController::class, 'claim'])->name('subdomain.waitlist-claim.post');
        Route::get('/claim/{token}/success', [WaitlistClaimController::class, 'success'])->name('subdomain.waitlist-claim.success');

        // Team invitation setup (branded experience)
        Route::get('/setup/invite/{token}', [SubdomainSetupController::class, 'showInvite'])->name('subdomain.invite.show');
        Route::post('/setup/invite/{token}', [SubdomainSetupController::class, 'acceptInvite'])->name('subdomain.invite.accept');
    });
