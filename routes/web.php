<?php

use App\Http\Controllers\Host\DashboardController;
use App\Http\Controllers\Host\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/signup', [SignupController::class, 'index'])->name('signup');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
