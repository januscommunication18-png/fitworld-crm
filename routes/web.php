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
});
