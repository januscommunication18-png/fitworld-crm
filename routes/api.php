<?php

use App\Http\Controllers\Api\SignupController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\QuestionnaireBuilderController;
use App\Http\Controllers\Api\WalkInBookingController;
use App\Http\Controllers\Api\FitNearYouApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Signup endpoints (public)
    Route::post('/signup/register', [SignupController::class, 'register']);
    Route::get('/signup/subdomain-check', [SignupController::class, 'checkSubdomain']);
    Route::get('/signup/legal-pages', [SignupController::class, 'getLegalPages']);

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

    // Post-signup onboarding endpoints (authenticated)
    Route::middleware('auth:sanctum')->prefix('onboarding')->group(function () {
        // Progress
        Route::get('/progress', [OnboardingController::class, 'progress']);

        // Step 1: Email & Phone Verification
        Route::post('/resend-email', [OnboardingController::class, 'resendEmailVerification']);
        Route::post('/send-phone-code', [OnboardingController::class, 'sendPhoneCode']);
        Route::post('/verify-phone-code', [OnboardingController::class, 'verifyPhoneCode']);

        // Step 2: Studio Information
        Route::post('/studio-info', [OnboardingController::class, 'saveStudioInfo']);

        // Step 3: Location
        Route::post('/location', [OnboardingController::class, 'saveLocation']);

        // Step 4: Staff Member
        Route::post('/staff-member', [OnboardingController::class, 'saveStaffMember']);

        // Step 5: Booking Page
        Route::post('/booking-page', [OnboardingController::class, 'saveBookingPage']);
        Route::post('/booking-page/logo', [OnboardingController::class, 'uploadLogo']);

        // Completion
        Route::post('/complete', [OnboardingController::class, 'completeOnboarding']);

        // Tech Support
        Route::post('/tech-support', [OnboardingController::class, 'requestTechSupport']);
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

    // FitNearYou Sync API (authenticated via API key/secret headers)
    Route::prefix('fitnearyou')->group(function () {
        // Verify credentials
        Route::post('/verify', [FitNearYouApiController::class, 'verifyCredentials']);

        // Get studio data for sync
        Route::get('/sync', [FitNearYouApiController::class, 'getStudioData']);
    });
});
