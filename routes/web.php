<?php

use App\Http\Controllers\Host\AuthController;
use App\Http\Controllers\Host\BookingController;
use App\Http\Controllers\Host\DashboardController;
use App\Http\Controllers\Host\InstructorController;
use App\Http\Controllers\Host\InvitationController;
use App\Http\Controllers\Host\LocationController;
use App\Http\Controllers\Host\RoomController;
use App\Http\Controllers\Host\BookingPageController;
use App\Http\Controllers\Host\PoliciesController;
use App\Http\Controllers\Host\OfferController;
use App\Http\Controllers\Host\PaymentController;
use App\Http\Controllers\Host\ReportController;
use App\Http\Controllers\Host\ScheduleController;
use App\Http\Controllers\Host\SettingsController;
use App\Http\Controllers\Host\SignupController;
use App\Http\Controllers\Host\TeamController;
use App\Http\Controllers\Host\StudentController;
use App\Http\Controllers\Host\CatalogController;
use App\Http\Controllers\Host\ClassPlanController;
use App\Http\Controllers\Host\ClassSessionController;
use App\Http\Controllers\Host\ClassRequestController;
use App\Http\Controllers\Host\ServicePlanController;
use App\Http\Controllers\Host\ServiceSlotController;
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

// Team Invitation - accessible by guests
Route::get('/invite/accept/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invite/accept/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');

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

    // Catalog (Classes & Services)
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

    // Class Plans
    Route::resource('class-plans', ClassPlanController::class)->names('class-plans');
    Route::patch('/class-plans/{classPlan}/toggle-active', [ClassPlanController::class, 'toggleActive'])->name('class-plans.toggle-active');
    Route::post('/class-plans/reorder', [ClassPlanController::class, 'reorder'])->name('class-plans.reorder');

    // Service Plans
    Route::resource('service-plans', ServicePlanController::class)->names('service-plans');
    Route::patch('/service-plans/{servicePlan}/toggle-active', [ServicePlanController::class, 'toggleActive'])->name('service-plans.toggle-active');
    Route::get('/service-plans/{servicePlan}/instructors', [ServicePlanController::class, 'manageInstructors'])->name('service-plans.instructors');
    Route::post('/service-plans/{servicePlan}/instructors', [ServicePlanController::class, 'updateInstructors'])->name('service-plans.instructors.update');

    // Service Slots
    Route::resource('service-slots', ServiceSlotController::class)->names('service-slots');
    Route::post('/service-slots/bulk', [ServiceSlotController::class, 'bulkCreate'])->name('service-slots.bulk');

    // Class Sessions
    Route::resource('class-sessions', ClassSessionController::class)->names('class-sessions');
    Route::patch('/class-sessions/{class_session}/publish', [ClassSessionController::class, 'publish'])->name('class-sessions.publish');
    Route::patch('/class-sessions/{class_session}/unpublish', [ClassSessionController::class, 'unpublish'])->name('class-sessions.unpublish');
    Route::patch('/class-sessions/{class_session}/cancel', [ClassSessionController::class, 'cancel'])->name('class-sessions.cancel');
    Route::patch('/class-sessions/{class_session}/promote-backup', [ClassSessionController::class, 'promoteBackup'])->name('class-sessions.promote-backup');
    Route::post('/class-sessions/{class_session}/duplicate', [ClassSessionController::class, 'duplicate'])->name('class-sessions.duplicate');

    // Class Requests
    Route::get('/class-requests', [ClassRequestController::class, 'index'])->name('class-requests.index');
    Route::get('/class-requests/{class_request}', [ClassRequestController::class, 'show'])->name('class-requests.show');
    Route::post('/class-requests/{class_request}/schedule', [ClassRequestController::class, 'scheduleFromRequest'])->name('class-requests.schedule');
    Route::patch('/class-requests/{class_request}/ignore', [ClassRequestController::class, 'ignore'])->name('class-requests.ignore');
    Route::delete('/class-requests/{class_request}', [ClassRequestController::class, 'destroy'])->name('class-requests.destroy');

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

    // Settings - Locations (specific routes first, then parameterized routes)
    Route::get('/settings/locations', [LocationController::class, 'index'])->name('settings.locations.index');
    Route::get('/settings/locations/create', [LocationController::class, 'create'])->name('settings.locations.create');
    Route::post('/settings/locations', [LocationController::class, 'store'])->name('settings.locations.store');

    // Rooms
    Route::get('/settings/locations/rooms', [RoomController::class, 'index'])->name('settings.locations.rooms');
    Route::get('/settings/locations/rooms/create', [RoomController::class, 'create'])->name('settings.rooms.create');
    Route::post('/settings/locations/rooms', [RoomController::class, 'store'])->name('settings.rooms.store');
    Route::get('/settings/locations/rooms/{room}/edit', [RoomController::class, 'edit'])->name('settings.rooms.edit');
    Route::put('/settings/locations/rooms/{room}', [RoomController::class, 'update'])->name('settings.rooms.update');
    Route::delete('/settings/locations/rooms/{room}', [RoomController::class, 'destroy'])->name('settings.rooms.destroy');
    Route::post('/settings/locations/rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])->name('settings.rooms.toggle-status');

    // Booking Page
    Route::get('/settings/locations/booking-page', [BookingPageController::class, 'index'])->name('settings.locations.booking-page');
    Route::put('/settings/locations/booking-page', [BookingPageController::class, 'update'])->name('settings.booking-page.update');
    Route::post('/settings/locations/booking-page/logo', [BookingPageController::class, 'uploadLogo'])->name('settings.booking-page.upload-logo');
    Route::post('/settings/locations/booking-page/cover', [BookingPageController::class, 'uploadCover'])->name('settings.booking-page.upload-cover');
    Route::delete('/settings/locations/booking-page/logo', [BookingPageController::class, 'removeLogo'])->name('settings.booking-page.remove-logo');
    Route::delete('/settings/locations/booking-page/cover', [BookingPageController::class, 'removeCover'])->name('settings.booking-page.remove-cover');

    // Policies
    // Policies
    Route::get('/settings/locations/policies', [PoliciesController::class, 'index'])->name('settings.locations.policies');
    Route::put('/settings/locations/policies', [PoliciesController::class, 'update'])->name('settings.policies.update');

    // Location CRUD (parameterized routes must come AFTER specific routes)
    Route::get('/settings/locations/{location}/edit', [LocationController::class, 'edit'])->name('settings.locations.edit');
    Route::put('/settings/locations/{location}', [LocationController::class, 'update'])->name('settings.locations.update');
    Route::delete('/settings/locations/{location}', [LocationController::class, 'destroy'])->name('settings.locations.destroy');
    Route::post('/settings/locations/{location}/default', [LocationController::class, 'setDefault'])->name('settings.locations.set-default');

    // Settings - Team
    Route::get('/settings/team/users', [TeamController::class, 'users'])->name('settings.team.users');
    Route::post('/settings/team/invite', [TeamController::class, 'invite'])->name('settings.team.invite');
    Route::post('/settings/team/invitations/{invitation}/resend', [TeamController::class, 'resendInvite'])->name('settings.team.invite.resend');
    Route::delete('/settings/team/invitations/{invitation}', [TeamController::class, 'revokeInvite'])->name('settings.team.invite.revoke');
    Route::put('/settings/team/users/{user}/role', [TeamController::class, 'updateRole'])->name('settings.team.users.role');
    Route::post('/settings/team/users/{user}/deactivate', [TeamController::class, 'deactivate'])->name('settings.team.users.deactivate');
    Route::post('/settings/team/users/{user}/reactivate', [TeamController::class, 'reactivate'])->name('settings.team.users.reactivate');
    Route::post('/settings/team/users/{user}/suspend', [TeamController::class, 'suspend'])->name('settings.team.users.suspend');
    Route::delete('/settings/team/users/{user}', [TeamController::class, 'remove'])->name('settings.team.users.remove');

    Route::get('/settings/team/instructors', [TeamController::class, 'instructors'])->name('settings.team.instructors');
    Route::post('/settings/team/instructors', [TeamController::class, 'storeInstructor'])->name('settings.team.instructors.store');
    Route::put('/settings/team/instructors/{instructor}', [TeamController::class, 'updateInstructor'])->name('settings.team.instructors.update');
    Route::post('/settings/team/instructors/{instructor}/photo', [TeamController::class, 'uploadInstructorPhoto'])->name('settings.team.instructors.photo');
    Route::delete('/settings/team/instructors/{instructor}/photo', [TeamController::class, 'removeInstructorPhoto'])->name('settings.team.instructors.photo.remove');
    Route::post('/settings/team/instructors/{instructor}/invite', [TeamController::class, 'inviteInstructor'])->name('settings.team.instructors.invite');
    Route::delete('/settings/team/instructors/{instructor}', [TeamController::class, 'deleteInstructor'])->name('settings.team.instructors.delete');

    Route::get('/settings/team/permissions', [TeamController::class, 'permissions'])->name('settings.team.permissions');
    Route::put('/settings/team/permissions/{user}', [TeamController::class, 'updatePermissions'])->name('settings.team.permissions.update');

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

    // Settings - Developer Tools (local only)
    Route::get('/settings/dev/email-logs', [SettingsController::class, 'emailLogs'])->name('settings.dev.email-logs');
    Route::post('/settings/dev/email-logs/clear', [SettingsController::class, 'clearEmailLogs'])->name('settings.dev.email-logs.clear');
});
