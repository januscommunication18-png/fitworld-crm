<?php

use App\Http\Controllers\Api\V1\Auth\AuthController as MobileAuthController;
use App\Http\Controllers\Api\V1\BookingController as MobileBookingController;
use App\Http\Controllers\Api\V1\BookingCreateController as MobileBookingCreateController;
use App\Http\Controllers\Api\V1\ClassPassController as MobileClassPassController;
use App\Http\Controllers\Api\V1\ClassSessionController as MobileClassSessionController;
use App\Http\Controllers\Api\V1\ClientController as MobileClientController;
use App\Http\Controllers\Api\V1\DashboardController as MobileDashboardController;
use App\Http\Controllers\Api\V1\DigitalCheckinController as MobileDigitalCheckinController;
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

    // Add Booking (class / service / space rental) + form option feeds.
    Route::get('/bookings/form-options', [MobileBookingCreateController::class, 'formOptions']);
    // NB: not `{client}` — backoffice.php registers a global `client` → Host binding.
    Route::get('/bookings/payment-options/{id}', [MobileBookingCreateController::class, 'paymentOptions'])->whereNumber('id');
    Route::post('/bookings/quick-add-client', [MobileBookingCreateController::class, 'quickAddClient']);
    Route::get('/bookings/class-options', [MobileBookingCreateController::class, 'classOptions']);
    Route::get('/bookings/service-options', [MobileBookingCreateController::class, 'serviceOptions']);
    Route::post('/class-sessions/{id}/bookings', [MobileBookingCreateController::class, 'storeClassBooking'])->whereNumber('id');
    Route::post('/service-slots/{id}/bookings', [MobileBookingCreateController::class, 'storeServiceBooking'])->whereNumber('id');
    Route::get('/space-rentals/quote', [MobileBookingCreateController::class, 'spaceRentalQuote']);
    Route::post('/space-rentals', [MobileBookingCreateController::class, 'storeSpaceRental']);

    // Schedule — unified feed + per-type detail.
    Route::get('/schedule', [MobileScheduleController::class, 'index']);
    Route::get('/class-sessions', [MobileClassSessionController::class, 'index']);
    Route::get('/class-sessions/{id}', [MobileClassSessionController::class, 'show'])->whereNumber('id');
    Route::get('/class-sessions/{id}/bookings', [MobileClassSessionController::class, 'bookings'])->whereNumber('id');
    Route::get('/service-slots/{id}', [MobileServiceSlotController::class, 'show'])->whereNumber('id');
    Route::get('/service-slots/{id}/bookings', [MobileServiceSlotController::class, 'bookings'])->whereNumber('id');
    Route::get('/space-rentals/{id}', [MobileSpaceRentalController::class, 'show'])->whereNumber('id');

    // Digital Check-In (QR scanner + manual lookup)
    Route::post('/digital-checkin/resolve', [MobileDigitalCheckinController::class, 'resolve']);
    Route::post('/digital-checkin/confirm', [MobileDigitalCheckinController::class, 'confirm']);
    Route::get('/digital-checkin/search', [MobileDigitalCheckinController::class, 'search']);

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