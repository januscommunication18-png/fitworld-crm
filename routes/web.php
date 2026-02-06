<?php

use App\Http\Controllers\Host\AuthController;
use App\Http\Controllers\Host\BookingController;
use App\Http\Controllers\Host\DashboardController;
use App\Http\Controllers\Host\InstructorController;
use App\Http\Controllers\Host\OfferController;
use App\Http\Controllers\Host\PaymentController;
use App\Http\Controllers\Host\ReportController;
use App\Http\Controllers\Host\ScheduleController;
use App\Http\Controllers\Host\SettingsController;
use App\Http\Controllers\Host\SignupController;
use App\Http\Controllers\Host\StudentController;
use App\Http\Controllers\SecurityCodeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Security Code Routes
Route::get('/security-code', [SecurityCodeController::class, 'show'])->name('security-code');
Route::post('/security-code', [SecurityCodeController::class, 'verify'])->name('security-code.verify');

// Public
Route::get('/', function () {
    return view('welcome');
});

// Guest-only (redirect to dashboard if already logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Signup - accessible by guests AND users who haven't completed onboarding
Route::get('/signup', [SignupController::class, 'index'])->name('signup');

// Auth-required routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard')->with('verified', true);
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Schedule
    Route::get('/schedule/classes', [ScheduleController::class, 'classes'])->name('schedule.classes');
    Route::get('/schedule/appointments', [ScheduleController::class, 'appointments'])->name('schedule.appointments');
    Route::get('/schedule/calendar', [ScheduleController::class, 'calendar'])->name('schedule.calendar');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');

    // Instructors
    Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');

    // Payments
    Route::get('/payments/transactions', [PaymentController::class, 'transactions'])->name('payments.transactions');
    Route::get('/payments/memberships', [PaymentController::class, 'memberships'])->name('payments.memberships');
    Route::get('/payments/class-packs', [PaymentController::class, 'classPacks'])->name('payments.class-packs');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

    // Offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Settings - Studio
    Route::get('/settings/studio/profile', [SettingsController::class, 'studioProfile'])->name('settings.studio.profile');
    Route::put('/settings/studio/profile', [SettingsController::class, 'updateStudioProfile'])->name('settings.studio.profile.update');
    Route::put('/settings/studio/about', [SettingsController::class, 'updateStudioAbout'])->name('settings.studio.about.update');
    Route::put('/settings/studio/contact', [SettingsController::class, 'updateStudioContact'])->name('settings.studio.contact.update');
    Route::put('/settings/studio/social', [SettingsController::class, 'updateStudioSocial'])->name('settings.studio.social.update');
    Route::put('/settings/studio/amenities', [SettingsController::class, 'updateStudioAmenities'])->name('settings.studio.amenities.update');
    Route::put('/settings/studio/currency', [SettingsController::class, 'updateStudioCurrency'])->name('settings.studio.currency.update');
    Route::post('/settings/studio/logo', [SettingsController::class, 'uploadStudioLogo'])->name('settings.studio.logo.upload');
    Route::post('/settings/studio/cover', [SettingsController::class, 'uploadStudioCover'])->name('settings.studio.cover.upload');

    // Settings - Locations
    Route::get('/settings/locations/rooms', [SettingsController::class, 'rooms'])->name('settings.locations.rooms');
    Route::get('/settings/locations/booking-page', [SettingsController::class, 'bookingPage'])->name('settings.locations.booking-page');
    Route::get('/settings/locations/policies', [SettingsController::class, 'policies'])->name('settings.locations.policies');

    // Settings - Team
    Route::get('/settings/team/users', [SettingsController::class, 'users'])->name('settings.team.users');
    Route::get('/settings/team/instructors', [SettingsController::class, 'instructors'])->name('settings.team.instructors');
    Route::get('/settings/team/permissions', [SettingsController::class, 'permissions'])->name('settings.team.permissions');

    // Settings - Payments
    Route::get('/settings/payments/settings', [SettingsController::class, 'paymentSettings'])->name('settings.payments.settings');
    Route::get('/settings/payments/tax', [SettingsController::class, 'taxSettings'])->name('settings.payments.tax');
    Route::get('/settings/payments/payouts', [SettingsController::class, 'payoutPreferences'])->name('settings.payments.payouts');

    // Settings - Notifications
    Route::get('/settings/notifications/email', [SettingsController::class, 'emailNotifications'])->name('settings.notifications.email');
    Route::get('/settings/notifications/sms', [SettingsController::class, 'smsNotifications'])->name('settings.notifications.sms');
    Route::get('/settings/notifications/automation', [SettingsController::class, 'automationRules'])->name('settings.notifications.automation');

    // Settings - Integrations
    Route::get('/settings/integrations/stripe', [SettingsController::class, 'stripeIntegration'])->name('settings.integrations.stripe');
    Route::get('/settings/integrations/fitnearyou', [SettingsController::class, 'fitNearYouIntegration'])->name('settings.integrations.fitnearyou');
    Route::get('/settings/integrations/calendar', [SettingsController::class, 'calendarSync'])->name('settings.integrations.calendar');
    Route::get('/settings/integrations/paypal', [SettingsController::class, 'paypalIntegration'])->name('settings.integrations.paypal');
    Route::get('/settings/integrations/cashapp', [SettingsController::class, 'cashAppIntegration'])->name('settings.integrations.cashapp');
    Route::get('/settings/integrations/venmo', [SettingsController::class, 'venmoIntegration'])->name('settings.integrations.venmo');

    // Settings - Plans & Billing
    Route::get('/settings/billing/plan', [SettingsController::class, 'currentPlan'])->name('settings.billing.plan');
    Route::get('/settings/billing/usage', [SettingsController::class, 'usage'])->name('settings.billing.usage');
    Route::get('/settings/billing/invoices', [SettingsController::class, 'invoices'])->name('settings.billing.invoices');

    // Settings - Advanced
    Route::get('/settings/advanced/export', [SettingsController::class, 'dataExport'])->name('settings.advanced.export');
    Route::get('/settings/advanced/audit', [SettingsController::class, 'auditLogs'])->name('settings.advanced.audit');
    Route::get('/settings/advanced/danger', [SettingsController::class, 'dangerZone'])->name('settings.advanced.danger');
});
