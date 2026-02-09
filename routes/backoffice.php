<?php

use App\Http\Controllers\Backoffice\AdminMemberController;
use App\Http\Controllers\Backoffice\Auth\LoginController;
use App\Http\Controllers\Backoffice\Auth\PasswordController;
use App\Http\Controllers\Backoffice\Auth\SecurityController;
use App\Http\Controllers\Backoffice\ClientController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\EmailLogController;
use App\Http\Controllers\Backoffice\EmailTemplateController;
use App\Http\Controllers\Backoffice\PlanController;
use App\Http\Controllers\Backoffice\PlaceholderController;
use App\Http\Controllers\Backoffice\SettingsController;
use App\Models\Host;
use Illuminate\Support\Facades\Route;

// Route model binding for client parameter
Route::model('client', Host::class);

Route::prefix('backoffice')->name('backoffice.')->group(function () {

    // Public: Security (OTP)
    Route::get('/security', [SecurityController::class, 'show'])->name('security');
    Route::post('/security', [SecurityController::class, 'sendOtp'])->name('security.send');
    Route::get('/security/verify', [SecurityController::class, 'showVerify'])->name('security.verify');
    Route::post('/security/verify', [SecurityController::class, 'verifyOtp'])->name('security.verify.submit');

    // Requires OTP verified
    Route::middleware('admin.otp.verified')->group(function () {

        // Guest only (login, forgot password)
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
            Route::post('/login', [LoginController::class, 'login']);
            Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->name('password.forgot');
        });

        // Authenticated admin
        Route::middleware('auth.admin')->group(function () {
            Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

            // Password change (bypasses must_change_password check)
            Route::get('/password/change', [PasswordController::class, 'showChangePassword'])->name('password.change');
            Route::post('/password/change', [PasswordController::class, 'changePassword'])->name('password.update');

            // All other routes require password to be changed
            Route::middleware('admin.must.change.password')->group(function () {

                // Dashboard
                Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

                // Clients
                Route::prefix('clients')->name('clients.')->middleware('admin.permission:clients')->group(function () {
                    Route::get('/', [ClientController::class, 'index'])->name('index');
                    Route::get('/{client}', [ClientController::class, 'show'])->name('show');
                    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
                    Route::put('/{client}', [ClientController::class, 'update'])->name('update');
                    Route::post('/{client}/resend-verification', [ClientController::class, 'resendVerification'])->name('resend-verification');
                    Route::post('/{client}/status', [ClientController::class, 'changeStatus'])->name('status');
                });

                // Plans
                Route::middleware('admin.permission:plans')->group(function () {
                    Route::resource('plans', PlanController::class);
                    Route::patch('/plans/{plan}/toggle', [PlanController::class, 'toggleActive'])->name('plans.toggle-active');
                });

                // Email Templates
                Route::middleware('admin.permission:email_templates')->group(function () {
                    Route::resource('email-templates', EmailTemplateController::class);
                    Route::post('/email-templates/{email_template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('email-templates.duplicate');
                    Route::get('/email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
                });

                // Email Logs
                Route::prefix('email-logs')->name('email-logs.')->middleware('admin.permission:email_logs')->group(function () {
                    Route::get('/', [EmailLogController::class, 'index'])->name('index');
                    Route::get('/export', [EmailLogController::class, 'export'])->name('export');
                    Route::get('/{emailLog}', [EmailLogController::class, 'show'])->name('show');
                    Route::delete('/{emailLog}', [EmailLogController::class, 'destroy'])->name('destroy');
                    Route::delete('/bulk', [EmailLogController::class, 'bulkDestroy'])->name('bulk-destroy');
                });

                // Admin Members
                Route::middleware('admin.permission:admin_members')->group(function () {
                    Route::resource('admin-members', AdminMemberController::class);
                    Route::patch('/admin-members/{adminMember}/toggle-status', [AdminMemberController::class, 'toggleStatus'])->name('admin-members.toggle-status');
                    Route::post('/admin-members/{adminMember}/reset-password', [AdminMemberController::class, 'resetPassword'])->name('admin-members.reset-password');
                });

                // Settings
                Route::middleware('admin.permission:settings')->group(function () {
                    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
                    Route::post('/settings/paddle', [SettingsController::class, 'updatePaddle'])->name('settings.update.paddle');
                    Route::post('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.update.mail');
                    Route::post('/settings/test-mail', [SettingsController::class, 'testMail'])->name('settings.test.mail');
                    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
                });

                // Coming Soon Placeholders
                Route::get('/class', [PlaceholderController::class, 'class'])->name('class.index');
                Route::get('/bookings', [PlaceholderController::class, 'bookings'])->name('bookings.index');
                Route::get('/schedule', [PlaceholderController::class, 'schedule'])->name('schedule.index');
                Route::get('/members', [PlaceholderController::class, 'members'])->name('members.index');
                Route::get('/invoice', [PlaceholderController::class, 'invoice'])->name('invoice.index');
            });
        });
    });
});
