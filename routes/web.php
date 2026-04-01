<?php

use App\Http\Controllers\Host\AuthController;
use App\Http\Controllers\Host\BookingController;
use App\Http\Controllers\Host\DashboardController;
use App\Http\Controllers\Host\EmailVerificationController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Host\EventController;
use App\Http\Controllers\Host\InstructorController;
use App\Http\Controllers\Host\InvitationController;
use App\Http\Controllers\Host\MarketplaceController;
use App\Http\Controllers\Host\OneOnOneSetupController;
use App\Http\Controllers\Host\OneOnOneBookingController;
use App\Http\Controllers\Host\LocationController;
use App\Http\Controllers\Host\RoomController;
use App\Http\Controllers\Host\BookingPageController;
use App\Http\Controllers\Host\PoliciesController;
use App\Http\Controllers\Host\OfferController;
use App\Http\Controllers\Host\SegmentController;
use App\Http\Controllers\Host\PaymentController;
use App\Http\Controllers\Host\ReportController;
use App\Http\Controllers\Host\ScheduleController;
use App\Http\Controllers\Host\SettingsController;
use App\Http\Controllers\Host\SignupController;
use App\Http\Controllers\Host\TeamController;
use App\Http\Controllers\Host\EmailTemplateController;
use App\Http\Controllers\Host\ExportController;
use App\Http\Controllers\Host\ClientController;
use App\Http\Controllers\Host\TagController;
use App\Http\Controllers\Host\CatalogController;
use App\Http\Controllers\Host\ClassPlanController;
use App\Http\Controllers\Host\ClassSessionController;
use App\Http\Controllers\Host\ClassRequestController;
use App\Http\Controllers\Host\WaitlistController;
use App\Http\Controllers\Host\ServicePlanController;
use App\Http\Controllers\Host\ServiceSlotController;
use App\Http\Controllers\Host\MembershipPlanController;
use App\Http\Controllers\Host\ClassPassController;
use App\Http\Controllers\Host\RentalItemController;
use App\Http\Controllers\Host\RentalFulfillmentController;
use App\Http\Controllers\Host\RentalInvoiceController;
use App\Http\Controllers\Host\SpaceRentalConfigController;
use App\Http\Controllers\Host\SpaceRentalController;
use App\Http\Controllers\Host\QuestionnaireController;
use App\Http\Controllers\Host\ProgressTemplateController;
use App\Http\Controllers\Host\ClassSessionProgressController;
use App\Http\Controllers\Host\WalkInController;
use App\Http\Controllers\Host\PriceOverrideController;
use App\Http\Controllers\Host\ScheduledMembershipController;
use App\Http\Controllers\Host\SupportRequestController;
use App\Http\Controllers\Api\QuestionnaireBuilderController;
use App\Http\Controllers\QuestionnaireResponseController;
use App\Http\Controllers\SecurityCodeController;
use App\Http\Controllers\Public\ProspectWaitlistController;
use App\Http\Controllers\Public\NewsletterSubscribeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Security Code Routes
Route::get('/security-code', [SecurityCodeController::class, 'show'])->name('security-code');
Route::post('/security-code', [SecurityCodeController::class, 'verify'])->name('security-code.verify');

// Debug route (no auth)
Route::get('/debug-test-route', function () {
    return "Debug route works! No auth required.";
});

// Public
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Public Prospect Waitlist Form
Route::prefix('join-waitlist')->name('public.waitlist.')->group(function () {
    Route::get('/', [ProspectWaitlistController::class, 'show'])->name('form');
    Route::post('/', [ProspectWaitlistController::class, 'store'])->name('store');
    Route::get('/success', [ProspectWaitlistController::class, 'success'])->name('success');
});

// Public Newsletter Subscription Form
Route::prefix('subscribe')->name('public.newsletter.')->group(function () {
    Route::get('/', [NewsletterSubscribeController::class, 'form'])->name('form');
    Route::post('/', [NewsletterSubscribeController::class, 'store'])->name('store');
    Route::get('/success', [NewsletterSubscribeController::class, 'success'])->name('success');
});

// Public Questionnaire Response Routes (no auth required)
Route::prefix('q')->name('questionnaire.')->group(function () {
    Route::get('/{token}', [QuestionnaireResponseController::class, 'show'])->name('show');
    Route::post('/{token}', [QuestionnaireResponseController::class, 'store'])->name('store');
    Route::post('/{token}/step', [QuestionnaireResponseController::class, 'saveStep'])->name('saveStep');
    Route::post('/{token}/complete', [QuestionnaireResponseController::class, 'completeWizard'])->name('complete');
});

// Guest-only (redirect to dashboard if already logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Signup - accessible by guests AND users who haven't completed onboarding
Route::get('/signup', [SignupController::class, 'index'])->name('signup');

// Team Invitation - accessible by guests
Route::get('/invite/accept/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invite/accept/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
// Alias for subdomain-style URLs (used in email links)
Route::get('/setup/invite/{token}', [InvitationController::class, 'show'])->name('invitation.setup.show');
Route::post('/setup/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.setup.accept');

// Auth-required routes
Route::middleware('auth')->group(function () {
    // Debug route (with auth)
    Route::get('/debug-auth-test', function () {
        return "Auth debug route works! User: " . auth()->user()->name;
    });

    // Debug multi-segment route
    Route::get('/debug/{a}/test/{b}', function ($a, $b) {
        return "Multi-segment works! a=$a, b=$b";
    })->where(['a' => '[0-9]+', 'b' => '[0-9]+']);

    // Debug exact pattern - test without "progress"
    Route::get('/clients/{client}/reports/{report}', function ($client, $report) {
        return "Clients REPORTS pattern works! client=$client, report=$report";
    })->name('debug.clients.reports')->where(['client' => '[0-9]+', 'report' => '[0-9]+']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Set studio language preference
    Route::post('/set-language', function (Request $request) {
        $validated = $request->validate([
            'language' => 'required|string|size:2',
        ]);

        $user = Auth::user();
        $host = $user->currentHost() ?? $user->host;

        if (!$host) {
            return response()->json(['success' => false, 'message' => 'No host found'], 400);
        }

        $language = strtolower($validated['language']);
        $hostLanguages = $host->studio_languages ?? ['en'];

        if (!is_array($hostLanguages) || empty($hostLanguages)) {
            $hostLanguages = ['en'];
        }

        if (!in_array($language, $hostLanguages)) {
            return response()->json(['success' => false, 'message' => 'Language not available'], 400);
        }

        $request->session()->put("studio_language_{$host->id}", $language);

        return response()->json([
            'success' => true,
            'language' => $language,
        ]);
    })->name('set-language');

    // Email Verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = \App\Models\User::findOrFail($id);

        // Check if the hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Mark as verified if not already
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Log the user in
        \Illuminate\Support\Facades\Auth::login($user);

        // Check if user is in onboarding (host exists but onboarding not complete)
        $host = $user->host;

        // Mark the host as verified/active if owner verifies email
        if ($host && !$host->isVerified() && $user->role === 'owner') {
            $host->markVerified();
        }

        if ($host && !$host->onboarding_completed_at) {
            return redirect('/signup?verified=1');
        }

        return redirect('/dashboard')->with('verified', true);
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');

    // Phone Verification
    Route::post('/phone/verification-send', [PhoneVerificationController::class, 'send'])->name('verification.phone.send');
    Route::post('/phone/verification-verify', [PhoneVerificationController::class, 'verify'])->name('verification.phone.verify');

    // Studio Selection (for multi-studio users)
    Route::get('/select-studio', [AuthController::class, 'selectStudio'])->name('select-studio');
    Route::post('/switch-studio', [AuthController::class, 'switchStudio'])->name('switch-studio');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/todays-classes', [DashboardController::class, 'todaysClasses'])->name('dashboard.todays-classes');
    Route::get('/dashboard/upcoming-bookings', [DashboardController::class, 'upcomingBookings'])->name('dashboard.upcoming-bookings');
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts'])->name('dashboard.alerts');
    Route::post('/dashboard/skip-setup', [DashboardController::class, 'skipSetup'])->name('dashboard.skip-setup');
    Route::post('/dashboard/save-studio-info', [DashboardController::class, 'saveStudioInfo'])->name('dashboard.save-studio-info');
    Route::post('/dashboard/quick-invite-member', [DashboardController::class, 'quickInviteMember'])->name('dashboard.quick-invite-member');
    Route::post('/dashboard/save-booking-page', [DashboardController::class, 'saveBookingPage'])->name('dashboard.save-booking-page');

    // Support Requests
    Route::get('/support/requests', [SupportRequestController::class, 'index'])->name('support.requests.index');
    Route::post('/support/requests', [SupportRequestController::class, 'store'])->name('support.requests.store');
    Route::get('/support/requests/{supportRequest}', [SupportRequestController::class, 'show'])->name('support.requests.show');

    // Dashboard API (for charts)
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'apiDashboard'])->name('api.dashboard');
        Route::get('/revenue-chart', [DashboardController::class, 'apiRevenueChart'])->name('api.dashboard.revenue-chart');
    });

    // Schedule
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/calendar', [ScheduleController::class, 'calendar'])->name('schedule.calendar');
    Route::get('/schedule/list', [ScheduleController::class, 'list'])->name('schedule.list');
    Route::get('/schedule/requests', [ScheduleController::class, 'requests'])->name('schedule.requests');
    Route::get('/schedule/waitlist', [ScheduleController::class, 'waitlist'])->name('schedule.waitlist');
    Route::get('/schedule/events', [ScheduleController::class, 'events'])->name('schedule.events');
    Route::post('/schedule/check-in/{booking}', [ScheduleController::class, 'checkIn'])->name('schedule.check-in');
    Route::post('/schedule/mark-complete/{classSession}', [ScheduleController::class, 'markComplete'])->name('schedule.mark-complete');

    // Clients (renamed from Students)
    // Static routes must come before wildcard routes
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/leads', [ClientController::class, 'leads'])->name('clients.leads');
    Route::get('/clients/members', [ClientController::class, 'members'])->name('clients.members');
    Route::get('/clients/at-risk', [ClientController::class, 'atRisk'])->name('clients.at-risk');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    // Client Tags (before wildcard)
    Route::get('/clients/tags', [TagController::class, 'index'])->name('clients.tags');
    Route::post('/clients/tags', [TagController::class, 'store'])->name('clients.tags.store');
    Route::put('/clients/tags/{tag}', [TagController::class, 'update'])->name('clients.tags.update-tag');
    Route::delete('/clients/tags/{tag}', [TagController::class, 'destroy'])->name('clients.tags.destroy');

    // Lead Magnet (Coming Soon - before wildcard)
    Route::get('/clients/lead-magnet', function () {
        return view('host.clients.lead-magnet');
    })->name('clients.lead-magnet');

    // Client detail routes (wildcard - must come after static routes)
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show')->where('id', '[0-9]+');
    Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit')->where('id', '[0-9]+');
    Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update')->where('id', '[0-9]+');
    Route::post('/clients/{id}/archive', [ClientController::class, 'archive'])->name('clients.archive')->where('id', '[0-9]+');
    Route::post('/clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore')->where('id', '[0-9]+');
    Route::post('/clients/{id}/note', [ClientController::class, 'addNote'])->name('clients.note')->where('id', '[0-9]+');
    Route::post('/clients/{id}/convert-to-client', [ClientController::class, 'convertToClient'])->name('clients.convert-to-client')->where('id', '[0-9]+');
    Route::post('/clients/{id}/convert-to-member', [ClientController::class, 'convertToMember'])->name('clients.convert-to-member')->where('id', '[0-9]+');
    Route::post('/clients/{id}/clear-at-risk', [ClientController::class, 'clearAtRisk'])->name('clients.clear-at-risk')->where('id', '[0-9]+');
    Route::put('/clients/{id}/tags', [ClientController::class, 'updateTags'])->name('clients.tags.update')->where('id', '[0-9]+');

    // Client Progress
    Route::get('/clients/{client}/progress', [\App\Http\Controllers\Host\ClientProgressController::class, 'index'])->name('clients.progress.index')->where('client', '[0-9]+');
    Route::get('/clients/{client}/record-progress', [\App\Http\Controllers\Host\ClientProgressController::class, 'selectClass'])->name('clients.progress.select-class')->where('client', '[0-9]+');

    // Debug test route
    Route::get('/test-progress/{client}/{report}', function($client, $report) {
        return "Test route works! Client: $client, Report: $report";
    })->where(['client' => '[0-9]+', 'report' => '[0-9]+']);

    Route::get('/clients/{client}/progress/{clientProgressReport}', [\App\Http\Controllers\Host\ClientProgressController::class, 'show'])->name('clients.progress.show')->where(['client' => '[0-9]+', 'clientProgressReport' => '[0-9]+']);
    Route::get('/clients/{client}/progress/{clientProgressReport}/json', [\App\Http\Controllers\Host\ClientProgressController::class, 'getReportJson'])->name('clients.progress.json')->where(['client' => '[0-9]+', 'clientProgressReport' => '[0-9]+']);

    // Client Measurements
    Route::post('/clients/{client}/measurements', [\App\Http\Controllers\Host\ClientMeasurementController::class, 'store'])->name('clients.measurements.store')->where('client', '[0-9]+');
    Route::get('/clients/{client}/measurements/{measurement}', [\App\Http\Controllers\Host\ClientMeasurementController::class, 'show'])->name('clients.measurements.show')->where(['client' => '[0-9]+', 'measurement' => '[0-9]+']);
    Route::put('/clients/{client}/measurements/{measurement}', [\App\Http\Controllers\Host\ClientMeasurementController::class, 'update'])->name('clients.measurements.update')->where(['client' => '[0-9]+', 'measurement' => '[0-9]+']);
    Route::delete('/clients/{client}/measurements/{measurement}', [\App\Http\Controllers\Host\ClientMeasurementController::class, 'destroy'])->name('clients.measurements.destroy')->where(['client' => '[0-9]+', 'measurement' => '[0-9]+']);
    Route::get('/clients/{client}/measurements-chart', [\App\Http\Controllers\Host\ClientMeasurementController::class, 'chartData'])->name('clients.measurements.chart')->where('client', '[0-9]+');

    // Help Desk
    Route::get('/helpdesk', [\App\Http\Controllers\Host\HelpdeskController::class, 'index'])->name('helpdesk.index');
    Route::get('/helpdesk/create', [\App\Http\Controllers\Host\HelpdeskController::class, 'create'])->name('helpdesk.create');
    Route::post('/helpdesk', [\App\Http\Controllers\Host\HelpdeskController::class, 'store'])->name('helpdesk.store');
    Route::get('/helpdesk/tags', [\App\Http\Controllers\Host\HelpdeskController::class, 'tags'])->name('helpdesk.tags');
    Route::post('/helpdesk/tags', [\App\Http\Controllers\Host\HelpdeskController::class, 'storeTag'])->name('helpdesk.tags.store');
    Route::delete('/helpdesk/tags/{tag}', [\App\Http\Controllers\Host\HelpdeskController::class, 'destroyTag'])->name('helpdesk.tags.destroy');
    Route::get('/helpdesk/{ticket}', [\App\Http\Controllers\Host\HelpdeskController::class, 'show'])->name('helpdesk.show');
    Route::put('/helpdesk/{ticket}', [\App\Http\Controllers\Host\HelpdeskController::class, 'update'])->name('helpdesk.update');
    Route::post('/helpdesk/{ticket}/reply', [\App\Http\Controllers\Host\HelpdeskController::class, 'reply'])->name('helpdesk.reply');
    Route::post('/helpdesk/{ticket}/convert', [\App\Http\Controllers\Host\HelpdeskController::class, 'convertToClient'])->name('helpdesk.convert');
    Route::delete('/helpdesk/{ticket}', [\App\Http\Controllers\Host\HelpdeskController::class, 'destroy'])->name('helpdesk.destroy');

    // Events
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}', [EventController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
        Route::post('/{event}/publish', [EventController::class, 'publish'])->name('publish');
        Route::post('/{event}/cancel', [EventController::class, 'cancel'])->name('cancel');
        Route::post('/{event}/clients', [EventController::class, 'addClients'])->name('addClients');
        Route::delete('/{event}/clients/{client}', [EventController::class, 'removeClient'])->name('removeClient');
        Route::post('/{event}/attendees/{attendee}/check-in', [EventController::class, 'checkIn'])->name('checkIn');
        Route::post('/{event}/attendees/{attendee}/no-show', [EventController::class, 'markNoShow'])->name('markNoShow');
        Route::post('/{event}/clients/{client}/check-in', [EventController::class, 'checkInClient'])->name('checkInClient');
    });

    // Instructors (main module - all CRUD operations here)
    Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
    Route::get('/instructors/create', [InstructorController::class, 'create'])->name('instructors.create');
    Route::post('/instructors', [InstructorController::class, 'store'])->name('instructors.store');
    Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->name('instructors.show');
    Route::get('/instructors/{instructor}/edit', [InstructorController::class, 'edit'])->name('instructors.edit');
    Route::put('/instructors/{instructor}', [InstructorController::class, 'update'])->name('instructors.update');
    Route::delete('/instructors/{instructor}', [InstructorController::class, 'destroy'])->name('instructors.destroy');
    Route::post('/instructors/{instructor}/toggle-status', [InstructorController::class, 'toggleStatus'])->name('instructors.toggle-status');
    Route::post('/instructors/{instructor}/reset-password', [InstructorController::class, 'resetPassword'])->name('instructors.reset-password');
    Route::post('/instructors/{instructor}/photo', [InstructorController::class, 'uploadPhoto'])->name('instructors.photo');
    Route::delete('/instructors/{instructor}/photo', [InstructorController::class, 'removePhoto'])->name('instructors.photo.remove');
    Route::post('/instructors/{instructor}/invite', [InstructorController::class, 'sendInvite'])->name('instructors.invite');
    Route::post('/instructors/{instructor}/notes', [InstructorController::class, 'storeNote'])->name('instructors.notes.store');
    Route::put('/instructor-notes/{note}', [InstructorController::class, 'updateNote'])->name('instructors.notes.update');
    Route::delete('/instructor-notes/{note}', [InstructorController::class, 'deleteNote'])->name('instructors.notes.delete');

    // Instructor Certifications
    Route::post('/instructors/{instructor}/certifications', [InstructorController::class, 'storeCertification'])->name('instructors.certifications.store');
    Route::get('/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'getCertification'])->name('instructors.certifications.show');
    Route::post('/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'updateCertification'])->name('instructors.certifications.update');
    Route::delete('/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'deleteCertification'])->name('instructors.certifications.delete');

    // Settings team instructors certification routes (alias for drawer AJAX)
    Route::post('/settings/team/instructors/{instructor}/certifications', [InstructorController::class, 'storeCertification'])->name('settings.team.instructors.certifications.store');
    Route::get('/settings/team/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'getCertification'])->name('settings.team.instructors.certifications.show');
    Route::post('/settings/team/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'updateCertification'])->name('settings.team.instructors.certifications.update');
    Route::delete('/settings/team/instructors/{instructor}/certifications/{certification}', [InstructorController::class, 'deleteCertification'])->name('settings.team.instructors.certifications.delete');
    Route::patch('/settings/team/instructors/{instructor}/toggle-social-visibility', [InstructorController::class, 'toggleSocialVisibility'])->name('settings.team.instructors.toggle-social-visibility');

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

    // Membership Plans
    Route::resource('membership-plans', MembershipPlanController::class)->names('membership-plans');
    Route::patch('/membership-plans/{membershipPlan}/toggle-status', [MembershipPlanController::class, 'toggleStatus'])->name('membership-plans.toggle-status');
    Route::patch('/membership-plans/{membershipPlan}/archive', [MembershipPlanController::class, 'archive'])->name('membership-plans.archive');

    // Class Passes
    Route::resource('class-passes', ClassPassController::class)->names('class-passes');
    Route::patch('/class-passes/{classPass}/toggle-status', [ClassPassController::class, 'toggleStatus'])->name('class-passes.toggle-status');
    Route::patch('/class-passes/{classPass}/archive', [ClassPassController::class, 'archive'])->name('class-passes.archive');
    Route::post('/class-passes/reorder', [ClassPassController::class, 'reorder'])->name('class-passes.reorder');
    Route::post('/class-passes/{classPass}/duplicate', [ClassPassController::class, 'duplicate'])->name('class-passes.duplicate');
    Route::get('/class-passes/{classPass}/purchases', [ClassPassController::class, 'purchases'])->name('class-passes.purchases');
    Route::get('/class-passes/{classPass}/sell', [ClassPassController::class, 'sellForm'])->name('class-passes.sell-form');
    Route::post('/class-passes/{classPass}/sell', [ClassPassController::class, 'sell'])->name('class-passes.sell');
    Route::post('/class-pass-purchases/{purchase}/activate', [ClassPassController::class, 'activatePurchase'])->name('class-pass-purchases.activate');

    // Rental Fulfillment (must be before resource route)
    Route::prefix('rentals/fulfillment')->name('rentals.fulfillment.')->group(function () {
        Route::get('/', [RentalFulfillmentController::class, 'index'])->name('index');
        Route::get('/{booking}', [RentalFulfillmentController::class, 'show'])->name('show');
        Route::post('/{booking}/update-status', [RentalFulfillmentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{booking}/prepare', [RentalFulfillmentController::class, 'prepare'])->name('prepare');
        Route::post('/{booking}/hand-out', [RentalFulfillmentController::class, 'handOut'])->name('hand-out');
        Route::post('/{booking}/return', [RentalFulfillmentController::class, 'return'])->name('return');
        Route::post('/{booking}/lost', [RentalFulfillmentController::class, 'lost'])->name('lost');
    });

    // Rental Invoice (must be before resource route)
    Route::prefix('rentals/invoice')->name('rentals.invoice.')->group(function () {
        Route::get('/create', [RentalInvoiceController::class, 'create'])->name('create');
        Route::post('/', [RentalInvoiceController::class, 'store'])->name('store');
        Route::get('/item-price/{rentalItem}', [RentalInvoiceController::class, 'getItemPrice'])->name('item-price');
    });

    // Rental Items
    Route::resource('rentals', RentalItemController::class);
    Route::patch('/rentals/{rental}/toggle-status', [RentalItemController::class, 'toggleStatus'])->name('rentals.toggle-status');
    Route::post('/rentals/{rental}/adjust-inventory', [RentalItemController::class, 'adjustInventory'])->name('rentals.adjust-inventory');

    // Space Rental Configuration
    Route::prefix('space-rentals/config')->name('space-rentals.config.')->group(function () {
        Route::get('/', [SpaceRentalConfigController::class, 'index'])->name('index');
        Route::get('/create', [SpaceRentalConfigController::class, 'create'])->name('create');
        Route::post('/', [SpaceRentalConfigController::class, 'store'])->name('store');
        Route::get('/{config}', [SpaceRentalConfigController::class, 'show'])->name('show');
        Route::get('/{config}/edit', [SpaceRentalConfigController::class, 'edit'])->name('edit');
        Route::put('/{config}', [SpaceRentalConfigController::class, 'update'])->name('update');
        Route::delete('/{config}', [SpaceRentalConfigController::class, 'destroy'])->name('destroy');
        Route::patch('/{config}/toggle-status', [SpaceRentalConfigController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Space Rentals (Bookings)
    Route::prefix('space-rentals')->name('space-rentals.')->group(function () {
        Route::get('/', [SpaceRentalController::class, 'index'])->name('index');
        Route::get('/create', [SpaceRentalController::class, 'create'])->name('create');
        Route::post('/', [SpaceRentalController::class, 'store'])->name('store');
        Route::get('/{rental}', [SpaceRentalController::class, 'show'])->name('show');
        Route::get('/{rental}/edit', [SpaceRentalController::class, 'edit'])->name('edit');
        Route::put('/{rental}', [SpaceRentalController::class, 'update'])->name('update');
        Route::post('/{rental}/confirm', [SpaceRentalController::class, 'confirm'])->name('confirm');
        Route::post('/{rental}/start', [SpaceRentalController::class, 'start'])->name('start');
        Route::post('/{rental}/complete', [SpaceRentalController::class, 'complete'])->name('complete');
        Route::post('/{rental}/cancel', [SpaceRentalController::class, 'cancel'])->name('cancel');
        Route::post('/{rental}/sign-waiver', [SpaceRentalController::class, 'signWaiver'])->name('sign-waiver');
        Route::post('/{rental}/record-deposit', [SpaceRentalController::class, 'recordDeposit'])->name('record-deposit');
        Route::post('/{rental}/refund-deposit', [SpaceRentalController::class, 'refundDeposit'])->name('refund-deposit');
        // AJAX endpoints
        Route::get('/config/{config}/available-times', [SpaceRentalController::class, 'getAvailableTimes'])->name('available-times');
        Route::post('/calculate-price', [SpaceRentalController::class, 'calculatePrice'])->name('calculate-price');
        Route::post('/check-conflicts', [SpaceRentalController::class, 'checkConflicts'])->name('check-conflicts');
    });

    // Questionnaires
    Route::resource('questionnaires', QuestionnaireController::class)->names('questionnaires');
    Route::get('/questionnaires/{questionnaire}/builder', [QuestionnaireController::class, 'builder'])->name('questionnaires.builder');
    Route::get('/questionnaires/{questionnaire}/preview', [QuestionnaireController::class, 'preview'])->name('questionnaires.preview');
    Route::post('/questionnaires/{questionnaire}/publish', [QuestionnaireController::class, 'publish'])->name('questionnaires.publish');
    Route::post('/questionnaires/{questionnaire}/unpublish', [QuestionnaireController::class, 'unpublish'])->name('questionnaires.unpublish');
    Route::post('/questionnaires/{questionnaire}/duplicate', [QuestionnaireController::class, 'duplicate'])->name('questionnaires.duplicate');

    // Questionnaire Responses
    Route::get('/questionnaires/{questionnaire}/responses', [QuestionnaireController::class, 'responses'])->name('questionnaires.responses');
    Route::get('/questionnaires/{questionnaire}/responses/{response}', [QuestionnaireController::class, 'showResponse'])->name('questionnaires.responses.show');
    Route::post('/questionnaires/{questionnaire}/responses/create', [QuestionnaireController::class, 'createResponse'])->name('questionnaires.responses.create');
    Route::post('/questionnaires/{questionnaire}/responses/{response}/resend', [QuestionnaireController::class, 'resendResponse'])->name('questionnaires.responses.resend');

    // Questionnaire Builder API (JSON endpoints for AJAX calls)
    Route::prefix('api/v1/questionnaires/{questionnaire}')->group(function () {
        Route::post('/steps', [QuestionnaireBuilderController::class, 'storeStep'])->name('api.questionnaires.steps.store');
        Route::put('/steps/{step}', [QuestionnaireBuilderController::class, 'updateStep'])->name('api.questionnaires.steps.update');
        Route::delete('/steps/{step}', [QuestionnaireBuilderController::class, 'destroyStep'])->name('api.questionnaires.steps.destroy');
        Route::post('/blocks', [QuestionnaireBuilderController::class, 'storeBlock'])->name('api.questionnaires.blocks.store');
        Route::put('/blocks/{block}', [QuestionnaireBuilderController::class, 'updateBlock'])->name('api.questionnaires.blocks.update');
        Route::delete('/blocks/{block}', [QuestionnaireBuilderController::class, 'destroyBlock'])->name('api.questionnaires.blocks.destroy');
        Route::post('/questions', [QuestionnaireBuilderController::class, 'storeQuestion'])->name('api.questionnaires.questions.store');
        Route::put('/questions/{question}', [QuestionnaireBuilderController::class, 'updateQuestion'])->name('api.questionnaires.questions.update');
        Route::delete('/questions/{question}', [QuestionnaireBuilderController::class, 'destroyQuestion'])->name('api.questionnaires.questions.destroy');
        Route::put('/reorder', [QuestionnaireBuilderController::class, 'reorder'])->name('api.questionnaires.reorder');
    });

    // Progress Templates
    Route::prefix('progress-templates')->name('progress-templates.')->group(function () {
        Route::get('/', [ProgressTemplateController::class, 'index'])->name('index');
        Route::get('/{progressTemplate}', [ProgressTemplateController::class, 'show'])->name('show');
        Route::post('/{progressTemplate}/toggle', [ProgressTemplateController::class, 'toggle'])->name('toggle');
    });

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
    Route::patch('/class-sessions/{class_session}/resolve-conflict', [ClassSessionController::class, 'resolveConflict'])->name('class-sessions.resolve-conflict');

    // Class Session Progress Recording
    Route::get('/class-sessions/{classSession}/record-progress/{progressTemplate}', [ClassSessionProgressController::class, 'create'])->name('class-sessions.record-progress');
    Route::post('/class-sessions/{classSession}/record-progress/{progressTemplate}', [ClassSessionProgressController::class, 'store'])->name('class-sessions.store-batch-progress');
    Route::post('/class-sessions/{classSession}/booking/{booking}/progress/{progressTemplate}', [ClassSessionProgressController::class, 'storeSingle'])->name('class-sessions.store-single-progress');

    // Scheduled Membership Classes
    Route::get('/membership-schedules', [ScheduledMembershipController::class, 'index'])->name('membership-schedules.index');
    Route::get('/scheduled-membership/create', [ScheduledMembershipController::class, 'create'])->name('scheduled-membership.create');
    Route::post('/scheduled-membership', [ScheduledMembershipController::class, 'store'])->name('scheduled-membership.store');

    // Walk-In Booking
    Route::get('/walk-in', [WalkInController::class, 'selectSession'])->name('walk-in.select');
    Route::get('/walk-in/sessions', [WalkInController::class, 'getSessionsByDate'])->name('walk-in.sessions');
    Route::get('/walk-in/sessions-range', [WalkInController::class, 'getSessionsByDateRange'])->name('walk-in.sessions-range');
    Route::get('/walk-in/class/{class_session}', [WalkInController::class, 'classSession'])->name('walk-in.class');
    Route::post('/walk-in/class/{class_session}', [WalkInController::class, 'bookClass'])->name('walk-in.class.book');
    Route::get('/walk-in/service/{service_slot}', [WalkInController::class, 'serviceSlot'])->name('walk-in.service');
    Route::post('/walk-in/service/{service_slot}', [WalkInController::class, 'bookService'])->name('walk-in.service.book');
    Route::get('/walk-in/payment-methods/{client_id}', [WalkInController::class, 'getPaymentMethods'])->name('walk-in.payment-methods');
    Route::get('/billing-credits/{billingCredit}/cancel-preview', [\App\Http\Controllers\Host\BillingCreditController::class, 'cancelPreview'])->name('billing-credits.cancel-preview');
    Route::post('/billing-credits/{billingCredit}/cancel', [\App\Http\Controllers\Host\BillingCreditController::class, 'cancel'])->name('billing-credits.cancel');
    Route::post('/walk-in/clients/quick-add', [WalkInController::class, 'quickAddClient'])->name('walk-in.clients.quick-add');
    Route::get('/walk-in/clients/search', [WalkInController::class, 'searchClients'])->name('walk-in.clients.search');
    Route::post('/walk-in/sessions/quick-create', [WalkInController::class, 'quickCreateSession'])->name('walk-in.sessions.quick-create');
    Route::get('/walk-in/class-plan-defaults', [WalkInController::class, 'getClassPlanDefaults'])->name('walk-in.class-plan-defaults');
    Route::get('/walk-in/instructor-availability', [WalkInController::class, 'getInstructorAvailability'])->name('walk-in.instructor-availability');
    Route::get('/walk-in/available-slots', [WalkInController::class, 'getAvailableSlots'])->name('walk-in.available-slots');
    Route::get('/walk-in/class-plan-questionnaires', [WalkInController::class, 'getClassPlanQuestionnaires'])->name('walk-in.class-plan-questionnaires');
    Route::post('/walk-in/validate-promo', [WalkInController::class, 'validatePromoCode'])->name('walk-in.validate-promo');
    Route::get('/walk-in/applicable-offers', [WalkInController::class, 'getApplicableOffers'])->name('walk-in.applicable-offers');

    // Walk-In Service Slot Booking
    Route::get('/walk-in/services', [WalkInController::class, 'selectServiceSlot'])->name('walk-in.select-service');
    Route::get('/walk-in/service-slots', [WalkInController::class, 'getServiceSlotsByDate'])->name('walk-in.service-slots');
    Route::get('/walk-in/service-plan-defaults', [WalkInController::class, 'getServicePlanDefaults'])->name('walk-in.service-plan-defaults');
    Route::post('/walk-in/service-slots/quick-create', [WalkInController::class, 'quickCreateServiceSlot'])->name('walk-in.service-slots.quick-create');

    // Walk-In Membership Booking
    Route::get('/walk-in/memberships', [WalkInController::class, 'selectMembership'])->name('walk-in.select-membership');
    Route::get('/walk-in/membership-plans', [WalkInController::class, 'getMembershipPlans'])->name('walk-in.membership-plans');
    Route::post('/walk-in/membership/book', [WalkInController::class, 'bookMembership'])->name('walk-in.membership.book');

    // Walk-In Event Registration
    Route::get('/walk-in/event/{event}', [WalkInController::class, 'event'])->name('walk-in.event');
    Route::post('/walk-in/event/{event}/register', [WalkInController::class, 'registerEvent'])->name('walk-in.event.register');

    // Price Override
    Route::prefix('price-override')->name('price-override.')->group(function () {
        Route::get('/', [PriceOverrideController::class, 'index'])->name('index');
        Route::post('/request', [PriceOverrideController::class, 'store'])->name('store');
        Route::post('/verify', [PriceOverrideController::class, 'verify'])->name('verify');
        Route::get('/can-request', [PriceOverrideController::class, 'canRequest'])->name('can-request');
        Route::post('/fetch-approved', [PriceOverrideController::class, 'fetchApproved'])->name('fetch-approved');
        Route::get('/personal-code', [PriceOverrideController::class, 'getPersonalCode'])->name('personal-code');
        Route::get('/pending', [PriceOverrideController::class, 'pending'])->name('pending');
        Route::get('/history', [PriceOverrideController::class, 'history'])->name('history');
        Route::get('/stats', [PriceOverrideController::class, 'stats'])->name('stats');
        Route::get('/review/{code}', [PriceOverrideController::class, 'review'])->name('review');
        Route::get('/{priceOverrideRequest}/status', [PriceOverrideController::class, 'status'])->name('status');
        Route::post('/{priceOverrideRequest}/approve', [PriceOverrideController::class, 'approve'])->name('approve');
        Route::post('/{priceOverrideRequest}/reject', [PriceOverrideController::class, 'reject'])->name('reject');
        Route::post('/{priceOverrideRequest}/cancel', [PriceOverrideController::class, 'cancel'])->name('cancel');
    });

    // Class Requests
    Route::get('/class-requests', [ClassRequestController::class, 'index'])->name('class-requests.index');
    Route::get('/class-requests/{class_request}', [ClassRequestController::class, 'show'])->name('class-requests.show');
    Route::patch('/class-requests/{class_request}/status', [ClassRequestController::class, 'updateStatus'])->name('class-requests.update-status');
    Route::patch('/class-requests/{class_request}/booked', [ClassRequestController::class, 'markAsBooked'])->name('class-requests.mark-booked');
    Route::post('/class-requests/{class_request}/schedule', [ClassRequestController::class, 'scheduleFromRequest'])->name('class-requests.schedule');
    Route::post('/class-requests/{class_request}/add-to-waitlist', [ClassRequestController::class, 'addToWaitlist'])->name('class-requests.add-to-waitlist');
    Route::delete('/class-requests/{class_request}', [ClassRequestController::class, 'destroy'])->name('class-requests.destroy');

    // Waitlist
    Route::get('/waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::get('/waitlist/{waitlist_entry}', [WaitlistController::class, 'show'])->name('waitlist.show');
    Route::patch('/waitlist/{waitlist_entry}/status', [WaitlistController::class, 'updateStatus'])->name('waitlist.update-status');
    Route::patch('/waitlist/{waitlist_entry}/offer', [WaitlistController::class, 'offer'])->name('waitlist.offer');
    Route::patch('/waitlist/{waitlist_entry}/cancel', [WaitlistController::class, 'cancel'])->name('waitlist.cancel');
    Route::delete('/waitlist/{waitlist_entry}', [WaitlistController::class, 'destroy'])->name('waitlist.destroy');

    // Payments
    Route::get('/payments/transactions', [PaymentController::class, 'transactions'])->name('payments.transactions');
    Route::get('/payments/transactions/{transaction}', [PaymentController::class, 'showTransaction'])->name('payments.transactions.show');
    Route::post('/payments/transactions/{transaction}/confirm', [PaymentController::class, 'confirmPayment'])->name('payments.transactions.confirm');
    Route::post('/payments/transactions/{transaction}/cancel', [PaymentController::class, 'cancelTransaction'])->name('payments.transactions.cancel');
    Route::post('/payments/transactions/{transaction}/toggle-hide', [PaymentController::class, 'toggleHideFromBooks'])->name('payments.transactions.toggle-hide');
    Route::get('/payments/memberships', [PaymentController::class, 'memberships'])->name('payments.memberships');
    Route::get('/payments/class-packs', [PaymentController::class, 'classPacks'])->name('payments.class-packs');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/upcoming', [BookingController::class, 'upcoming'])->name('bookings.upcoming');
    Route::get('/bookings/cancellations', [BookingController::class, 'cancelled'])->name('bookings.cancelled');
    Route::get('/bookings/no-shows', [BookingController::class, 'noShows'])->name('bookings.no-shows');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/resend-intake', [BookingController::class, 'resendIntake'])->name('bookings.resend-intake');

    // Marketing - Segments
    Route::resource('segments', SegmentController::class);
    Route::post('/segments/{segment}/add-client', [SegmentController::class, 'addClient'])->name('segments.add-client');
    Route::post('/segments/{segment}/remove-client', [SegmentController::class, 'removeClient'])->name('segments.remove-client');
    Route::post('/segments/{segment}/refresh', [SegmentController::class, 'refresh'])->name('segments.refresh');
    Route::post('/segments/preview', [SegmentController::class, 'preview'])->name('segments.preview');

    // Marketing - Offers
    Route::resource('offers', OfferController::class);
    Route::post('/offers/{offer}/duplicate', [OfferController::class, 'duplicate'])->name('offers.duplicate');
    Route::post('/offers/{offer}/toggle-status', [OfferController::class, 'toggleStatus'])->name('offers.toggle-status');
    Route::post('/offers/validate-code', [OfferController::class, 'validateCode'])->name('offers.validate-code');

    // Feature Marketplace
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [MarketplaceController::class, 'index'])->name('index');
        Route::get('/{feature:slug}', [MarketplaceController::class, 'show'])->name('show');
        Route::post('/{feature}/toggle', [MarketplaceController::class, 'toggle'])->name('toggle');
        Route::post('/{feature}/config', [MarketplaceController::class, 'updateConfig'])->name('config');

        // 1:1 Meeting specific routes
        Route::post('/online-1on1-meeting/grant-access', [MarketplaceController::class, 'grantOneOnOneAccess'])->name('one-on-one.grant-access');
        Route::post('/online-1on1-meeting/revoke-access/{bookingProfile}', [MarketplaceController::class, 'revokeOneOnOneAccess'])->name('one-on-one.revoke-access');
        Route::post('/online-1on1-meeting/resend-invitation/{bookingProfile}', [MarketplaceController::class, 'resendOneOnOneInvitation'])->name('one-on-one.resend-invitation');

        // FitNearYou sync routes
        Route::post('/fitnearyou-sync/generate-credentials', [MarketplaceController::class, 'generateFitNearYouCredentials'])->name('fitnearyou.generate-credentials');
        Route::post('/fitnearyou-sync/regenerate-credentials', [MarketplaceController::class, 'regenerateFitNearYouCredentials'])->name('fitnearyou.regenerate-credentials');
        Route::post('/fitnearyou-sync/send-secret-code', [MarketplaceController::class, 'sendFitNearYouSecretCode'])->name('fitnearyou.send-secret-code');
        Route::post('/fitnearyou-sync/verify-secret-code', [MarketplaceController::class, 'verifyFitNearYouSecretCode'])->name('fitnearyou.verify-secret-code');
    });

    // 1:1 Booking Setup (Team Member)
    Route::prefix('one-on-one-setup')->name('one-on-one-setup.')->group(function () {
        Route::get('/', [OneOnOneSetupController::class, 'index'])->name('index');
        Route::post('/', [OneOnOneSetupController::class, 'store'])->name('store');
        Route::put('/', [OneOnOneSetupController::class, 'update'])->name('update');
    });

    // 1:1 Bookings Management (Team Member)
    Route::prefix('one-on-one')->name('one-on-one.')->group(function () {
        Route::get('/', [OneOnOneBookingController::class, 'index'])->name('index');
        Route::get('/invite', [OneOnOneBookingController::class, 'createInvite'])->name('invite.create');
        Route::post('/invite', [OneOnOneBookingController::class, 'sendInvite'])->name('send-invite');
        Route::post('/resend-invite/{invite}', [OneOnOneBookingController::class, 'resendInvite'])->name('resend-invite');
        Route::get('/availability/{instructor}', [OneOnOneBookingController::class, 'getAvailability'])->name('availability');
        Route::get('/{booking}', [OneOnOneBookingController::class, 'show'])->name('show');
        Route::post('/{booking}/accept', [OneOnOneBookingController::class, 'accept'])->name('accept');
        Route::post('/{booking}/decline', [OneOnOneBookingController::class, 'decline'])->name('decline');
        Route::post('/{booking}/cancel', [OneOnOneBookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/complete', [OneOnOneBookingController::class, 'complete'])->name('complete');
        Route::post('/{booking}/no-show', [OneOnOneBookingController::class, 'noShow'])->name('no-show');
    });

    // Reports / Insights
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/class-performance', [ReportController::class, 'classPerformance'])->name('class-performance');
        Route::get('/retention', [ReportController::class, 'retention'])->name('retention');
    });

    // Reports API
    Route::prefix('api/reports')->group(function () {
        Route::get('/attendance/chart', [ReportController::class, 'apiAttendanceChart'])->name('api.reports.attendance-chart');
        Route::get('/membership/chart', [ReportController::class, 'apiMembershipChart'])->name('api.reports.membership-chart');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Settings - My Profile (accessible to all authenticated users)
    Route::get('/settings/profile', [SettingsController::class, 'myProfile'])->name('settings.profile');
    Route::put('/settings/profile', [SettingsController::class, 'updateMyProfile'])->name('settings.profile.update');
    Route::put('/settings/profile/password', [SettingsController::class, 'updateMyPassword'])->name('settings.profile.password');
    Route::post('/settings/profile/photo', [SettingsController::class, 'uploadMyPhoto'])->name('settings.profile.photo');
    Route::delete('/settings/profile/photo', [SettingsController::class, 'removeMyPhoto'])->name('settings.profile.photo.remove');

    // Settings - Studio
    Route::get('/settings/studio/profile', [SettingsController::class, 'studioProfile'])->name('settings.studio.profile');
    Route::put('/settings/studio/profile', [SettingsController::class, 'updateStudioProfile'])->name('settings.studio.profile.update');
    Route::put('/settings/studio/about', [SettingsController::class, 'updateStudioAbout'])->name('settings.studio.about.update');
    Route::put('/settings/studio/contact', [SettingsController::class, 'updateStudioContact'])->name('settings.studio.contact.update');
    Route::put('/settings/studio/social', [SettingsController::class, 'updateStudioSocial'])->name('settings.studio.social.update');
    Route::put('/settings/studio/amenities', [SettingsController::class, 'updateStudioAmenities'])->name('settings.studio.amenities.update');
    Route::put('/settings/studio/currency', [SettingsController::class, 'updateStudioCurrency'])->name('settings.studio.currency.update');
    Route::put('/settings/studio/countries', [SettingsController::class, 'updateStudioCountries'])->name('settings.studio.countries.update');
    Route::put('/settings/studio/language', [SettingsController::class, 'updateStudioLanguage'])->name('settings.studio.language.update');
    Route::put('/settings/studio/categories', [SettingsController::class, 'updateStudioCategories'])->name('settings.studio.categories.update');
    Route::put('/settings/studio/cancellation', [SettingsController::class, 'updateStudioCancellation'])->name('settings.studio.cancellation.update');

    Route::post('/settings/studio/logo', [SettingsController::class, 'uploadStudioLogo'])->name('settings.studio.logo.upload');
    Route::post('/settings/studio/cover', [SettingsController::class, 'uploadStudioCover'])->name('settings.studio.cover.upload');

    // Gallery
    Route::post('/settings/studio/gallery', [SettingsController::class, 'uploadGalleryImage'])->name('settings.studio.gallery.upload');
    Route::put('/settings/studio/gallery/{id}', [SettingsController::class, 'updateGalleryImage'])->name('settings.studio.gallery.update');
    Route::delete('/settings/studio/gallery/{id}', [SettingsController::class, 'deleteGalleryImage'])->name('settings.studio.gallery.delete');
    Route::post('/settings/studio/gallery/reorder', [SettingsController::class, 'reorderGalleryImages'])->name('settings.studio.gallery.reorder');

    // Certifications
    Route::post('/settings/studio/certifications', [SettingsController::class, 'storeCertification'])->name('settings.studio.certifications.store');
    Route::get('/settings/studio/certifications/{id}', [SettingsController::class, 'getCertification'])->name('settings.studio.certifications.show');
    Route::post('/settings/studio/certifications/{id}', [SettingsController::class, 'updateCertification'])->name('settings.studio.certifications.update');
    Route::delete('/settings/studio/certifications/{id}', [SettingsController::class, 'deleteCertification'])->name('settings.studio.certifications.delete');

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
    Route::get('/settings/team/users/invite', [TeamController::class, 'showInvite'])->name('settings.team.users.invite');
    Route::post('/settings/team/invite', [TeamController::class, 'invite'])->name('settings.team.invite');
    Route::post('/settings/team/invitations/{invitation}/resend', [TeamController::class, 'resendInvite'])->name('settings.team.invite.resend');
    Route::delete('/settings/team/invitations/{invitation}', [TeamController::class, 'revokeInvite'])->name('settings.team.invite.revoke');
    Route::get('/settings/team/users/{user}', [TeamController::class, 'showUser'])->name('settings.team.users.show')->where('user', '[0-9]+');
    Route::get('/settings/team/users/{user}/edit', [TeamController::class, 'editUser'])->name('settings.team.users.edit');
    Route::put('/settings/team/users/{user}', [TeamController::class, 'updateUser'])->name('settings.team.users.update');
    Route::put('/settings/team/users/{user}/role', [TeamController::class, 'updateRole'])->name('settings.team.users.role');
    Route::post('/settings/team/users/{user}/deactivate', [TeamController::class, 'deactivate'])->name('settings.team.users.deactivate');
    Route::post('/settings/team/users/{user}/reactivate', [TeamController::class, 'reactivate'])->name('settings.team.users.reactivate');
    Route::post('/settings/team/users/{user}/suspend', [TeamController::class, 'suspend'])->name('settings.team.users.suspend');
    Route::post('/settings/team/users/{user}/reset-password', [TeamController::class, 'resetUserPassword'])->name('settings.team.users.reset-password');
    Route::post('/settings/team/users/{user}/send-invite', [TeamController::class, 'sendUserInvite'])->name('settings.team.users.send-invite');
    Route::post('/settings/team/users/{user}/notes', [TeamController::class, 'storeUserNote'])->name('settings.team.users.notes.store');
    Route::put('/settings/team/users/{user}/profile', [TeamController::class, 'updateUserProfile'])->name('settings.team.users.profile.update');
    Route::delete('/settings/team/user-notes/{note}', [TeamController::class, 'deleteUserNote'])->name('settings.team.user-notes.delete');
    Route::delete('/settings/team/users/{user}', [TeamController::class, 'remove'])->name('settings.team.users.remove');
    Route::post('/settings/team/users/{user}/add-as-instructor', [TeamController::class, 'addAsInstructor'])->name('settings.team.users.add-as-instructor');

    // User Certifications
    Route::post('/settings/team/users/{user}/certifications', [TeamController::class, 'storeUserCertification'])->name('settings.team.users.certifications.store');
    Route::get('/settings/team/users/{user}/certifications/{certification}', [TeamController::class, 'getUserCertification'])->name('settings.team.users.certifications.show');
    Route::delete('/settings/team/users/{user}/certifications/{certification}', [TeamController::class, 'deleteUserCertification'])->name('settings.team.users.certifications.delete');

    // Redirect old settings instructor routes to main instructors module
    Route::get('/settings/team/instructors', fn() => redirect()->route('instructors.index'))->name('settings.team.instructors');
    Route::get('/settings/team/instructors/create', fn() => redirect()->route('instructors.create'))->name('settings.team.instructors.create');
    Route::get('/settings/team/instructors/{instructor}/edit', fn($instructor) => redirect()->route('instructors.edit', $instructor))->name('settings.team.instructors.edit');

    Route::get('/settings/team/permissions', [TeamController::class, 'permissions'])->name('settings.team.permissions');
    Route::get('/settings/team/permissions/{user}/edit', [TeamController::class, 'editPermissions'])->name('settings.team.permissions.edit');
    Route::put('/settings/team/permissions/{user}', [TeamController::class, 'updatePermissions'])->name('settings.team.permissions.update');

    // Settings - Clients
    Route::get('/settings/clients', [SettingsController::class, 'clientSettings'])->name('settings.clients');
    Route::put('/settings/clients', [SettingsController::class, 'updateClientSettings'])->name('settings.clients.update');

    // Settings - Member Portal
    Route::get('/settings/member-portal', [SettingsController::class, 'memberPortal'])->name('settings.member-portal');
    Route::put('/settings/member-portal', [SettingsController::class, 'updateMemberPortal'])->name('settings.member-portal.update');

    // Settings - Payments
    Route::get('/settings/payments/settings', [SettingsController::class, 'paymentSettings'])->name('settings.payments.settings');
    Route::put('/settings/payments/settings', [SettingsController::class, 'updatePaymentSettings'])->name('settings.payments.settings.update');
    Route::get('/settings/payments/tax', [SettingsController::class, 'taxSettings'])->name('settings.payments.tax');
    Route::put('/settings/payments/tax', [SettingsController::class, 'updateTaxSettings'])->name('settings.payments.tax.update');
    Route::post('/settings/payments/tax/rates', [SettingsController::class, 'storeTaxRate'])->name('settings.payments.tax.rates.store');
    Route::put('/settings/payments/tax/rates/{id}', [SettingsController::class, 'updateTaxRate'])->name('settings.payments.tax.rates.update');
    Route::delete('/settings/payments/tax/rates/{id}', [SettingsController::class, 'deleteTaxRate'])->name('settings.payments.tax.rates.delete');
    Route::patch('/settings/payments/tax/rates/{id}/toggle', [SettingsController::class, 'toggleTaxRate'])->name('settings.payments.tax.rates.toggle');
    Route::post('/settings/payments/tax/rates/{id}/override', [SettingsController::class, 'overrideTaxRate'])->name('settings.payments.tax.rates.override');
    Route::get('/settings/payments/payouts', [SettingsController::class, 'payoutPreferences'])->name('settings.payments.payouts');

    // Settings - Notifications
    Route::get('/settings/notifications/email', [SettingsController::class, 'emailNotifications'])->name('settings.notifications.email');
    Route::get('/settings/notifications/sms', [SettingsController::class, 'smsNotifications'])->name('settings.notifications.sms');
    Route::get('/settings/notifications/automation', [SettingsController::class, 'automationRules'])->name('settings.notifications.automation');
    Route::post('/settings/notifications/automation/{key}', [SettingsController::class, 'updateAutomationSetting'])->name('settings.notifications.automation.update');

    // Settings - Communication / Email Templates
    Route::get('/settings/communication/email-templates', [EmailTemplateController::class, 'index'])->name('settings.communication.email-templates');
    Route::get('/settings/communication/email-templates/{key}/edit', [EmailTemplateController::class, 'edit'])->name('settings.communication.email-templates.edit');
    Route::put('/settings/communication/email-templates/{key}', [EmailTemplateController::class, 'update'])->name('settings.communication.email-templates.update');
    Route::post('/settings/communication/email-templates/{key}/preview', [EmailTemplateController::class, 'preview'])->name('settings.communication.email-templates.preview');
    Route::post('/settings/communication/email-templates/{key}/test', [EmailTemplateController::class, 'sendTest'])->name('settings.communication.email-templates.test');
    Route::post('/settings/communication/email-templates/{key}/reset', [EmailTemplateController::class, 'reset'])->name('settings.communication.email-templates.reset');

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
    Route::get('/settings/advanced/audit', [SettingsController::class, 'auditLogs'])->name('settings.audit');
    Route::get('/settings/advanced/danger', [SettingsController::class, 'dangerZone'])->name('settings.advanced.danger');

    // Export Routes
    Route::get('/export/clients', [ExportController::class, 'clients'])->name('export.clients');
    Route::get('/export/transactions', [ExportController::class, 'transactions'])->name('export.transactions');
    Route::get('/export/bookings', [ExportController::class, 'bookings'])->name('export.bookings');
    Route::get('/export/classes', [ExportController::class, 'classes'])->name('export.classes');
    Route::get('/export/memberships', [ExportController::class, 'memberships'])->name('export.memberships');
    Route::get('/export/instructors', [ExportController::class, 'instructors'])->name('export.instructors');
    Route::get('/export/audit-logs', [ExportController::class, 'auditLogs'])->name('export.audit-logs');
    Route::get('/export/user-sessions', [ExportController::class, 'userSessions'])->name('export.user-sessions');

    // Settings - Developer Tools (local only)
    Route::get('/settings/dev/email-logs', [SettingsController::class, 'emailLogs'])->name('settings.dev.email-logs');
    Route::post('/settings/dev/email-logs/clear', [SettingsController::class, 'clearEmailLogs'])->name('settings.dev.email-logs.clear');
});
