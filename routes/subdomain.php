<?php

use App\Http\Controllers\SubdomainSetupController;
use App\Http\Controllers\Subdomain\BookingController;
use App\Http\Controllers\Subdomain\BookingFlowController;
use App\Http\Controllers\Subdomain\ClassRequestController;
use App\Http\Controllers\Subdomain\MemberAuthController;
use App\Http\Controllers\Subdomain\MemberPortalController;
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
        Route::get('/service-request', [ServiceRequestController::class, 'create'])->name('subdomain.service-request');
        Route::get('/service-request/success', [ServiceRequestController::class, 'success'])->name('subdomain.service-request.success');
        Route::get('/service-request/{servicePlanId}', [ServiceRequestController::class, 'create'])->name('subdomain.service-request.plan')->where('servicePlanId', '[0-9]+');
        Route::post('/service-request', [ServiceRequestController::class, 'store'])->name('subdomain.service-request.store');
        // Legacy route for backwards compatibility
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

        // ─────────────────────────────────────────────────────────────
        // Public Booking Flow (Guest Checkout)
        // ─────────────────────────────────────────────────────────────

        // Step 1: Select what to book
        Route::get('/book', [BookingFlowController::class, 'selectType'])->name('booking.select-type');
        Route::get('/book/class', [BookingFlowController::class, 'selectClass'])->name('booking.select-class');
        Route::get('/book/class/{classPlanId}', [BookingFlowController::class, 'selectClass'])->name('booking.select-class.filter')->where('classPlanId', '[0-9]+');
        Route::post('/book/class/{session}', [BookingFlowController::class, 'selectClassSession'])->name('booking.select-class-session');
        Route::get('/book/service', [BookingFlowController::class, 'selectService'])->name('booking.select-service');
        Route::get('/book/service/{servicePlanId}', [BookingFlowController::class, 'selectService'])->name('booking.select-service.filter')->where('servicePlanId', '[0-9]+');
        Route::post('/book/service/{slot}', [BookingFlowController::class, 'selectServiceSlot'])->name('booking.select-service-slot');
        Route::get('/book/membership', [BookingFlowController::class, 'selectMembership'])->name('booking.select-membership');
        Route::post('/book/membership/{plan}', [BookingFlowController::class, 'selectMembershipPlan'])->name('booking.select-membership-plan');
        Route::post('/book/class-pack/{pack}', [BookingFlowController::class, 'selectClassPack'])->name('booking.select-class-pack');

        // Step 2: Contact Information
        Route::get('/book/contact', [BookingFlowController::class, 'contactInfo'])->name('booking.contact');
        Route::post('/book/contact', [BookingFlowController::class, 'saveContactInfo'])->name('booking.contact.save');

        // Step 3: Payment
        Route::get('/book/payment', [BookingFlowController::class, 'payment'])->name('booking.payment');
        Route::post('/book/payment', [BookingFlowController::class, 'processPayment'])->name('booking.payment.process');

        // Stripe callbacks
        Route::get('/book/stripe/success/{transaction}', [BookingFlowController::class, 'stripeSuccess'])->name('booking.stripe-success');
        Route::get('/book/stripe/cancel/{transaction}', [BookingFlowController::class, 'stripeCancel'])->name('booking.stripe-cancel');

        // Step 4: Confirmation
        Route::get('/book/confirmation/{transaction}', [BookingFlowController::class, 'confirmation'])->name('booking.confirmation');

        // Clear booking and start over
        Route::get('/book/clear', [BookingFlowController::class, 'clear'])->name('booking.clear');

        // Stripe Webhook (no CSRF, outside of web middleware is ideal but kept here for subdomain context)
        Route::post('/book/stripe/webhook', [BookingFlowController::class, 'stripeWebhook'])
            ->name('booking.stripe-webhook')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        // ─────────────────────────────────────────────────────────────
        // Member Portal Authentication
        // ─────────────────────────────────────────────────────────────
        Route::get('/login', [MemberAuthController::class, 'showLogin'])->name('member.login');
        Route::post('/login', [MemberAuthController::class, 'login'])->name('member.login.post');
        Route::post('/logout', [MemberAuthController::class, 'logout'])->name('member.logout');

        // Member Sign Up
        Route::get('/signup', [MemberAuthController::class, 'showSignup'])->name('member.signup');
        Route::post('/signup', [MemberAuthController::class, 'signup'])->name('member.signup.post');

        // OTP Authentication
        Route::post('/login/send-otp', [MemberAuthController::class, 'sendOtp'])->name('member.send-otp');
        Route::get('/login/verify', [MemberAuthController::class, 'showVerifyOtp'])->name('member.verify-otp');
        Route::post('/login/verify', [MemberAuthController::class, 'verifyOtp'])->name('member.verify-otp.post');

        // Password Reset
        Route::get('/forgot-password', [MemberAuthController::class, 'showForgotPassword'])->name('member.forgot-password');
        Route::post('/forgot-password', [MemberAuthController::class, 'sendResetLink'])->name('member.forgot-password.post');
        Route::get('/reset-password/{token}', [MemberAuthController::class, 'showResetPassword'])->name('member.reset-password');
        Route::post('/reset-password', [MemberAuthController::class, 'resetPassword'])->name('member.reset-password.post');

        // ─────────────────────────────────────────────────────────────
        // Member Portal (Protected)
        // ─────────────────────────────────────────────────────────────
        Route::middleware('auth.member')->prefix('portal')->group(function () {
            // Dashboard / Home
            Route::get('/', [MemberPortalController::class, 'dashboard'])->name('member.portal');
            Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('member.portal.dashboard');

            // My Schedule (member's booked classes)
            Route::get('/my-schedule', [MemberPortalController::class, 'bookings'])->name('member.portal.bookings');

            // Booking (browse classes, services, memberships)
            Route::get('/booking', [MemberPortalController::class, 'booking'])->name('member.portal.booking');
            Route::get('/booking/classes', [MemberPortalController::class, 'schedule'])->name('member.portal.schedule');
            Route::get('/booking/services', [MemberPortalController::class, 'services'])->name('member.portal.services');
            Route::get('/booking/memberships', [MemberPortalController::class, 'memberships'])->name('member.portal.memberships');

            // Payments & Invoices
            Route::get('/payments', [MemberPortalController::class, 'payments'])->name('member.portal.payments');
            Route::get('/invoices/{invoice}/download', [MemberPortalController::class, 'downloadInvoice'])->name('member.portal.invoice.download');

            // Profile
            Route::get('/profile', [MemberPortalController::class, 'profile'])->name('member.portal.profile');
            Route::put('/profile', [MemberPortalController::class, 'updateProfile'])->name('member.portal.profile.update');
            Route::put('/profile/password', [MemberPortalController::class, 'changePassword'])->name('member.portal.profile.password');
        });
    });
