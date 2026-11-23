<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureDemoPasswordResetIsEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware(['guest', EnsureDemoPasswordResetIsEnabled::class])->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('password.send');

    Route::get('/verify-otp', [PasswordResetController::class, 'verifyForm'])->name('password.verify.form');
    Route::post('/verify-otp', [PasswordResetController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('password.verify');
    Route::post('/verify-otp/resend', [PasswordResetController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('password.resend');

    Route::get('/reset-password', [PasswordResetController::class, 'resetForm'])->name('password.reset.form');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.reset');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard (main page)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Task resource routes
    Route::resource('tasks', TaskController::class);

    // Toggle task completion status
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');

    // Account settings
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('settings.password');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])
        ->middleware('throttle:3,1')
        ->name('settings.destroy');
});
