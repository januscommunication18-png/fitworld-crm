<?php

use App\Http\Controllers\Api\Client\V1\AppConfigController;
use App\Http\Controllers\Api\Client\V1\AuthController;
use App\Http\Controllers\Api\Client\V1\BookingController;
use App\Http\Controllers\Api\Client\V1\CatalogController;
use App\Http\Controllers\Api\Client\V1\CheckinController;
use App\Http\Controllers\Api\Client\V1\MembershipController;
use App\Http\Controllers\Api\Client\V1\PaymentController;
use App\Http\Controllers\Api\Client\V1\ProfileController;
use App\Http\Controllers\Api\Client\V1\ScheduleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| FitStudioHQ Branded Client App API (v1)
|--------------------------------------------------------------------------
|
| Consumed by the white-label consumer apps (one branded build per studio).
| Registered in bootstrap/app.php with the `api` middleware group and the
| `api/client/v1` prefix. Every route requires the studio app token via the
| `X-Studio-App-Token` header (`client.app` middleware); authenticated
| routes additionally require a client Sanctum bearer token plus the
| `client.scope` tenancy guard.
|
*/

// Public-disk file passthrough. The files are already world-readable via
// /storage; this route only adds the CORS headers (api/*) that a static
// file server doesn't send, so the web build of the app can render images.
Route::get('/files/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $disk = Storage::disk(config('filesystems.uploads'));
    abort_unless($disk->exists($path), 404);

    return $disk->response($path);
})->where('path', '[\w\-\/\. ]+');

Route::middleware('client.app')->group(function () {
    // Branding/config — the app's first call on every cold start.
    Route::get('/app-config', [AppConfigController::class, 'show']);

    // Authentication: Client Token ID first, then OTP or password.
    Route::prefix('auth')->group(function () {
        Route::post('/lookup', [AuthController::class, 'lookup'])->middleware('throttle:20,1');
        Route::post('/otp/send', [AuthController::class, 'sendOtp'])->middleware('throttle:10,1');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:20,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

        Route::middleware(['auth:sanctum', 'client.scope'])->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Signed-in client features.
    Route::middleware(['auth:sanctum', 'client.scope'])->group(function () {
        Route::get('/me/qr', [CheckinController::class, 'myCode']);
        Route::get('/me/schedule', [ScheduleController::class, 'index']);
        Route::get('/me/payments', [PaymentController::class, 'index']);
        Route::get('/me/profile', [ProfileController::class, 'show']);
        Route::put('/me/profile', [ProfileController::class, 'update'])->middleware('throttle:20,1');
        Route::post('/me/profile/photo', [ProfileController::class, 'updatePhoto'])->middleware('throttle:20,1');
        Route::delete('/me/profile/photo', [ProfileController::class, 'deletePhoto'])->middleware('throttle:20,1');
        Route::get('/classes', [BookingController::class, 'classes']);
        Route::get('/classes/{sessionId}/booking-options', [BookingController::class, 'bookingOptions']);
        Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:30,1');
        Route::post('/memberships/purchase', [MembershipController::class, 'purchase'])->middleware('throttle:10,1');
        Route::get('/catalog', [CatalogController::class, 'index']);
        Route::post('/passes/purchase', [CatalogController::class, 'purchasePass'])->middleware('throttle:10,1');
        Route::get('/services/{planId}/slots', [CatalogController::class, 'serviceSlots']);
        Route::post('/services/book', [CatalogController::class, 'bookService'])->middleware('throttle:20,1');
        Route::post('/events/register', [CatalogController::class, 'registerEvent'])->middleware('throttle:20,1');
        Route::post('/rentals/spaces', [CatalogController::class, 'rentSpace'])->middleware('throttle:10,1');
        Route::post('/rentals/items', [CatalogController::class, 'rentItem'])->middleware('throttle:10,1');
        Route::post('/bookings/{bookingId}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:30,1');
        Route::post('/bookings/{bookingId}/check-in', [CheckinController::class, 'selfCheckIn'])->middleware('throttle:30,1');
    });
});
