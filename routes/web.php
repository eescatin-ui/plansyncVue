<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| User Web Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC USER ROUTES
// =============================================

// User Auth API
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

// =============================================
// USER SPA ENTRY POINTS
// These routes return the Vue SPA layout
// =============================================

// Login/Register pages
Route::get('/login', function () {
    return view('app');
})->name('login');

Route::get('/register', function () {
    return view('app');
})->name('register');

// Main app routes (all user pages are handled by Vue Router)
Route::get('/dashboard', function () {
    return view('app');
})->name('dashboard');

Route::get('/notes', function () {
    return view('app');
})->name('notes');

Route::get('/notes/{id}', function () {
    return view('app');
})->name('notes.show');

Route::get('/schedule', function () {
    return view('app');
})->name('schedule');

Route::get('/tasks', function () {
    return view('app');
})->name('tasks');

Route::get('/tasks/{id}', function () {
    return view('app');
})->name('tasks.show');

Route::get('/reminders', function () {
    return view('app');
})->name('reminders');

Route::get('/settings', function () {
    return view('app');
})->name('settings');

// =============================================
// USER API ROUTES (JSON responses)
// =============================================

Route::middleware('auth')->group(function () {
    
    // User Profile API
    Route::get('/api/user', function () {
        return response()->json(auth()->user());
    });
    
    // Dashboard Stats API
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Notes API
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
    
    // Tasks API
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    
    // Schedule API
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::get('/schedule/{schedule}/edit', [ScheduleController::class, 'edit']);
    Route::put('/schedule/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy']);
    
    // Reminders API
    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::get('/reminders/{reminder}/edit', [ReminderController::class, 'edit']);
    Route::put('/reminders/{reminder}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy']);
    
    // Settings API
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences']);
    Route::put('/settings/password', [SettingsController::class, 'changePassword']);
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount']);
});

// =============================================
// CATCH-ALL ROUTE (MUST BE LAST)
// Redirects any other route to Vue SPA
// =============================================
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!admin).*$');  // Exclude admin routes