<?php

use App\Http\Controllers\Api\SignupController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Signup endpoints (public)
    Route::post('/signup/register', [SignupController::class, 'register']);
    Route::get('/signup/subdomain-check', [SignupController::class, 'checkSubdomain']);

    // Signup endpoints (authenticated — user registers at step 2, then continues)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/signup/verify-email', [SignupController::class, 'verifyEmail']);
        Route::post('/signup/studio', [SignupController::class, 'saveStudio']);
        Route::post('/signup/location', [SignupController::class, 'saveLocation']);
        Route::post('/signup/instructors', [SignupController::class, 'saveInstructors']);
        Route::post('/signup/classes', [SignupController::class, 'saveClass']);
        Route::post('/signup/payments', [SignupController::class, 'savePayments']);
        Route::post('/signup/complete', [SignupController::class, 'complete']);
    });
});
