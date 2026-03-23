<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingsController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated Routes (for all authenticated users)
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    
    // Dashboard - accessible to both users and admins
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Switch between admin/user
    Route::get('/switch-to-admin', function () {
        session(['viewing_as_admin' => true]);
        return redirect()->route('admin.dashboard');
    })->name('switch-to-admin');
    
    Route::get('/switch-to-user', function () {
        session(['viewing_as_admin' => false]);
        return redirect()->route('dashboard');
    })->name('switch-to-user');
    
    // Schedule routes
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/color-suggestions', [ScheduleController::class, 'getColorSuggestions'])->name('color-suggestions');
        Route::put('/{id}/color', [ScheduleController::class, 'updateColor'])->name('update-color');
    });
    
    // User-only routes (non-admins)
    Route::middleware(['auth'])->group(function () {
        Route::resource('schedule', ScheduleController::class)->except(['index']);
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::resource('tasks', TaskController::class);
        Route::resource('notes', NoteController::class);
        Route::resource('reminders', ReminderController::class);
        
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('updateProfile');
            Route::post('/preferences', [SettingsController::class, 'updatePreferences'])->name('updatePreferences');
            Route::post('/change-password', [SettingsController::class, 'changePassword'])->name('changePassword');
        });
    });
});

// Include admin routes
require __DIR__.'/admin.php';

// Redirect root
Route::redirect('/', '/dashboard');