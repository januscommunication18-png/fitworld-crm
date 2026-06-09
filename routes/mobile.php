<?php

use App\Http\Controllers\Api\V1\Auth\AuthController as MobileAuthController;
use App\Http\Controllers\Api\V1\BookingController as MobileBookingController;
use App\Http\Controllers\Api\V1\ClassPassController as MobileClassPassController;
use App\Http\Controllers\Api\V1\ClassSessionController as MobileClassSessionController;
use App\Http\Controllers\Api\V1\ClientController as MobileClientController;
use App\Http\Controllers\Api\V1\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\V1\MembershipPlanController as MobileMembershipPlanController;
use App\Http\Controllers\Api\V1\PaymentController as MobilePaymentController;
use App\Http\Controllers\Api\V1\ScheduleController as MobileScheduleController;
use App\Http\Controllers\Api\V1\ServiceSlotController as MobileServiceSlotController;
use App\Http\Controllers\Api\V1\SpaceRentalController as MobileSpaceRentalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FitCRM Mobile App API (v1)
|--------------------------------------------------------------------------
|
| Routes consumed by the FitCRM staff/owner mobile app (fitcrm-app), using
| Sanctum bearer tokens. Registered in bootstrap/app.php with the `api`
| middleware group and the `api/v1` prefix, so every route here lives under
| `/api/v1/...`.
|
*/

// Staff/owner mobile auth.
Route::prefix('auth')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me']);
        Route::get('/studios', [MobileAuthController::class, 'studios']);
        Route::post('/switch-studio/{host}', [MobileAuthController::class, 'switchStudio']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});

// Studio-scoped endpoints (require a selected studio via the X-Studio-Id header).
Route::middleware(['auth:sanctum', 'studio.context'])->group(function () {
    Route::get('/dashboard', [MobileDashboardController::class, 'index']);

    // Bookings
    Route::get('/bookings', [MobileBookingController::class, 'index']);
    Route::get('/bookings/series', [MobileBookingController::class, 'series']);
    Route::get('/bookings/{id}', [MobileBookingController::class, 'show'])->whereNumber('id');

    // Schedule — unified feed + per-type detail.
    Route::get('/schedule', [MobileScheduleController::class, 'index']);
    Route::get('/class-sessions', [MobileClassSessionController::class, 'index']);
    Route::get('/class-sessions/{id}', [MobileClassSessionController::class, 'show'])->whereNumber('id');
    Route::get('/class-sessions/{id}/bookings', [MobileClassSessionController::class, 'bookings'])->whereNumber('id');
    Route::get('/service-slots/{id}', [MobileServiceSlotController::class, 'show'])->whereNumber('id');
    Route::get('/service-slots/{id}/bookings', [MobileServiceSlotController::class, 'bookings'])->whereNumber('id');
    Route::get('/space-rentals/{id}', [MobileSpaceRentalController::class, 'show'])->whereNumber('id');

    // Clients
    Route::get('/clients', [MobileClientController::class, 'index']);
    Route::get('/clients/form-options', [MobileClientController::class, 'formOptions']);
    Route::post('/clients', [MobileClientController::class, 'store']);
    Route::get('/clients/{id}', [MobileClientController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/clients/{id}/notes', [MobileClientController::class, 'storeNote'])->where('id', '[0-9]+');

    // Offers & payments
    Route::get('/membership-plans', [MobileMembershipPlanController::class, 'index']);
    Route::get('/class-passes', [MobileClassPassController::class, 'index']);
    Route::get('/payments/transactions', [MobilePaymentController::class, 'transactions']);
});