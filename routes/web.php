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

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

// =============================================
// USER API ROUTES (JSON responses)
// =============================================

Route::middleware('auth')->group(function () {

    // User Profile API
    Route::get('/api/user', function () {
        return response()->json(auth()->user());
    });

    // Dashboard Stats API
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats']);

    // Notes API
    Route::get('/api/notes', [NoteController::class, 'index']);
    Route::post('/api/notes', [NoteController::class, 'store']);
    Route::get('/api/notes/{note}/edit', [NoteController::class, 'edit']);
    Route::put('/api/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/api/notes/{note}', [NoteController::class, 'destroy']);

    // Tasks API
    Route::get('/api/tasks', [TaskController::class, 'index']);
    Route::post('/api/tasks', [TaskController::class, 'store']);
    Route::get('/api/tasks/{task}/edit', [TaskController::class, 'edit']);
    Route::put('/api/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/api/tasks/{task}', [TaskController::class, 'destroy']);

    // Schedule API
    Route::get('/api/schedule', [ScheduleController::class, 'index']);
    Route::post('/api/schedule', [ScheduleController::class, 'store']);
    Route::get('/api/schedule/{schedule}/edit', [ScheduleController::class, 'edit']);
    Route::put('/api/schedule/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('/api/schedule/{schedule}', [ScheduleController::class, 'destroy']);

    // Reminders API
    Route::get('/api/reminders', [ReminderController::class, 'index']);
    Route::post('/api/reminders', [ReminderController::class, 'store']);
    Route::get('/api/reminders/{reminder}/edit', [ReminderController::class, 'edit']);
    Route::put('/api/reminders/{reminder}', [ReminderController::class, 'update']);
    Route::delete('/api/reminders/{reminder}', [ReminderController::class, 'destroy']);

    // Settings API
    Route::put('/api/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/api/settings/preferences', [SettingsController::class, 'updatePreferences']);
    Route::put('/api/settings/password', [SettingsController::class, 'changePassword']);
    Route::delete('/api/settings/account', [SettingsController::class, 'deleteAccount']);
});

// =============================================
// SPA CATCH-ALL ROUTE (MUST BE LAST)
// Serves Vue app for all non-API, non-admin routes
// =============================================
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!admin).*$');