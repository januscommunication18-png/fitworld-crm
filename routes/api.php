<?php

use App\Http\Controllers\Api\SignupController;
use App\Http\Controllers\Api\QuestionnaireBuilderController;
use App\Http\Controllers\Api\WalkInBookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Signup endpoints (public)
    Route::post('/signup/register', [SignupController::class, 'register']);
    Route::get('/signup/subdomain-check', [SignupController::class, 'checkSubdomain']);

    // Signup endpoints (authenticated — user registers at step 2, then continues)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/signup/progress', [SignupController::class, 'progress']);
        Route::post('/signup/verify-email', [SignupController::class, 'verifyEmail']);
        Route::post('/signup/studio', [SignupController::class, 'saveStudio']);
        Route::post('/signup/location', [SignupController::class, 'saveLocation']);
        Route::post('/signup/instructors', [SignupController::class, 'saveInstructors']);
        Route::post('/signup/classes', [SignupController::class, 'saveClass']);
        Route::post('/signup/payments', [SignupController::class, 'savePayments']);
        Route::post('/signup/complete', [SignupController::class, 'complete']);
    });

    // Questionnaire Builder API (authenticated)
    Route::middleware('auth:sanctum')->prefix('questionnaires/{questionnaire}')->group(function () {
        // Steps (wizard mode)
        Route::post('/steps', [QuestionnaireBuilderController::class, 'storeStep']);
        Route::put('/steps/{step}', [QuestionnaireBuilderController::class, 'updateStep']);
        Route::delete('/steps/{step}', [QuestionnaireBuilderController::class, 'destroyStep']);

        // Blocks
        Route::post('/blocks', [QuestionnaireBuilderController::class, 'storeBlock']);
        Route::put('/blocks/{block}', [QuestionnaireBuilderController::class, 'updateBlock']);
        Route::delete('/blocks/{block}', [QuestionnaireBuilderController::class, 'destroyBlock']);

        // Questions
        Route::post('/questions', [QuestionnaireBuilderController::class, 'storeQuestion']);
        Route::put('/questions/{question}', [QuestionnaireBuilderController::class, 'updateQuestion']);
        Route::delete('/questions/{question}', [QuestionnaireBuilderController::class, 'destroyQuestion']);

        // Reorder
        Route::put('/reorder', [QuestionnaireBuilderController::class, 'reorder']);
    });

    // Walk-In Booking API (authenticated, staff+)
    Route::middleware('auth:sanctum')->prefix('walk-in')->group(function () {
        // Book a class session
        Route::post('/class/{session}', [WalkInBookingController::class, 'bookClass']);

        // Book a service slot
        Route::post('/service/{slot}', [WalkInBookingController::class, 'bookService']);

        // Check class availability
        Route::get('/class/{session}/availability', [WalkInBookingController::class, 'checkClassAvailability']);

        // Get payment methods for a client
        Route::get('/payment-methods/{client}', [WalkInBookingController::class, 'getPaymentMethods']);
    });

    // Client API for walk-in (authenticated, staff+)
    Route::middleware('auth:sanctum')->prefix('clients')->group(function () {
        // Quick add client
        Route::post('/quick-add', [WalkInBookingController::class, 'quickAddClient']);

        // Search clients
        Route::get('/search', [WalkInBookingController::class, 'searchClients']);

        // Recent clients
        Route::get('/recent', [WalkInBookingController::class, 'recentClients']);
    });
});
