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
use App\Http\Controllers\Host\ClientController;
use App\Http\Controllers\Host\TagController;
use App\Http\Controllers\Host\CatalogController;
use App\Http\Controllers\Host\ClassPlanController;
use App\Http\Controllers\Host\ClassSessionController;
use App\Http\Controllers\Host\ClassRequestController;
use App\Http\Controllers\Host\ServicePlanController;
use App\Http\Controllers\Host\ServiceSlotController;
use App\Http\Controllers\Host\MembershipPlanController;
use App\Http\Controllers\Host\QuestionnaireController;
use App\Http\Controllers\Host\WalkInController;
use App\Http\Controllers\Api\QuestionnaireBuilderController;
use App\Http\Controllers\QuestionnaireResponseController;
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
})->name('welcome');

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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

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
        if ($host && !$host->onboarding_completed_at) {
            return redirect('/signup?verified=1');
        }

        return redirect('/dashboard')->with('verified', true);
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

    // Studio Selection (for multi-studio users)
    Route::get('/select-studio', [AuthController::class, 'selectStudio'])->name('select-studio');
    Route::post('/switch-studio', [AuthController::class, 'switchStudio'])->name('switch-studio');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Schedule
    Route::get('/schedule/classes', [ScheduleController::class, 'classes'])->name('schedule.classes');
    Route::get('/schedule/appointments', [ScheduleController::class, 'appointments'])->name('schedule.appointments');
    Route::get('/schedule/calendar', [ScheduleController::class, 'calendar'])->name('schedule.calendar');

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

    // Walk-In Booking
    Route::get('/walk-in', [WalkInController::class, 'selectSession'])->name('walk-in.select');
    Route::get('/walk-in/sessions', [WalkInController::class, 'getSessionsByDate'])->name('walk-in.sessions');
    Route::get('/walk-in/class/{class_session}', [WalkInController::class, 'classSession'])->name('walk-in.class');
    Route::post('/walk-in/class/{class_session}', [WalkInController::class, 'bookClass'])->name('walk-in.class.book');
    Route::get('/walk-in/service/{service_slot}', [WalkInController::class, 'serviceSlot'])->name('walk-in.service');
    Route::post('/walk-in/service/{service_slot}', [WalkInController::class, 'bookService'])->name('walk-in.service.book');
    Route::get('/walk-in/payment-methods/{client}', [WalkInController::class, 'getPaymentMethods'])->name('walk-in.payment-methods');
    Route::post('/walk-in/clients/quick-add', [WalkInController::class, 'quickAddClient'])->name('walk-in.clients.quick-add');
    Route::get('/walk-in/clients/search', [WalkInController::class, 'searchClients'])->name('walk-in.clients.search');
    Route::post('/walk-in/sessions/quick-create', [WalkInController::class, 'quickCreateSession'])->name('walk-in.sessions.quick-create');
    Route::get('/walk-in/class-plan-defaults', [WalkInController::class, 'getClassPlanDefaults'])->name('walk-in.class-plan-defaults');
    Route::get('/walk-in/instructor-availability', [WalkInController::class, 'getInstructorAvailability'])->name('walk-in.instructor-availability');
    Route::get('/walk-in/available-slots', [WalkInController::class, 'getAvailableSlots'])->name('walk-in.available-slots');
    Route::get('/walk-in/class-plan-questionnaires', [WalkInController::class, 'getClassPlanQuestionnaires'])->name('walk-in.class-plan-questionnaires');

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
    Route::get('/bookings/upcoming', [BookingController::class, 'upcoming'])->name('bookings.upcoming');
    Route::get('/bookings/cancellations', [BookingController::class, 'cancelled'])->name('bookings.cancelled');
    Route::get('/bookings/no-shows', [BookingController::class, 'noShows'])->name('bookings.no-shows');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/resend-intake', [BookingController::class, 'resendIntake'])->name('bookings.resend-intake');

    // Offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

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
    Route::delete('/settings/team/user-notes/{note}', [TeamController::class, 'deleteUserNote'])->name('settings.team.user-notes.delete');
    Route::delete('/settings/team/users/{user}', [TeamController::class, 'remove'])->name('settings.team.users.remove');

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

    // Settings - Payments
    Route::get('/settings/payments/settings', [SettingsController::class, 'paymentSettings'])->name('settings.payments.settings');
    Route::put('/settings/payments/settings', [SettingsController::class, 'updatePaymentSettings'])->name('settings.payments.settings.update');
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
