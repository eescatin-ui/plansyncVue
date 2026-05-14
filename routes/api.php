<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;

Route::get('/test-middleware', function() {
    return response()->json(['working' => true]);
})->middleware('auth:sanctum');

// Public routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::get('/schedule/{schedule}', [ScheduleController::class, 'show']);
    Route::put('/schedule/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy']);
    Route::get('/schedule/{schedule}/edit', [ScheduleController::class, 'edit']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit']);

    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}', [NoteController::class, 'show']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit']);

    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::get('/reminders/{reminder}', [ReminderController::class, 'show']);
    Route::put('/reminders/{reminder}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy']);
    Route::get('/reminders/{reminder}/edit', [ReminderController::class, 'edit']);

    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences']);
    Route::put('/settings/password', [SettingsController::class, 'changePassword']);
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount']);
});