<?php

use App\Http\Controllers\SubdomainSetupController;
use App\Http\Controllers\Subdomain\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Subdomain Routes
|--------------------------------------------------------------------------
|
| These routes handle the public-facing booking pages for each studio.
| They are accessible via {subdomain}.fitcrm.app
|
*/

Route::domain('{subdomain}.' . config('app.booking_domain', 'fitcrm.app'))
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

        // Team invitation setup (branded experience)
        Route::get('/setup/invite/{token}', [SubdomainSetupController::class, 'showInvite'])->name('subdomain.invite.show');
        Route::post('/setup/invite/{token}', [SubdomainSetupController::class, 'acceptInvite'])->name('subdomain.invite.accept');
    });
