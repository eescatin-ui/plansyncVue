<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes
Route::middleware('auth.token')->group(function () {
    // User routes
    Route::get('/user', [UserController::class, 'getUser']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::get('/profile/image', [UserController::class, 'getProfileImage']);
    Route::post('/profile/image', [UserController::class, 'uploadProfileImage']);
    Route::delete('/profile/image', [UserController::class, 'deleteProfileImage']);
    
    // Classes/Schedule routes
    Route::get('/classes', [ScheduleController::class, 'index']);
    Route::post('/classes', [ScheduleController::class, 'store']);
    Route::get('/classes/{classSchedule}', [ScheduleController::class, 'show']);
    Route::put('/classes/{classSchedule}', [ScheduleController::class, 'update']);
    Route::delete('/classes/{classSchedule}', [ScheduleController::class, 'destroy']);
    
    Route::get('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::get('/schedule/{schedule}', [ScheduleController::class, 'show']);
    Route::put('/schedule/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy']);
    
    // Courses alias
    Route::get('/courses', [ScheduleController::class, 'index']);
    
    // Tasks routes - Use {task} for Task model binding
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    
    // Notes routes - Use {note} for Note model binding
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}', [NoteController::class, 'show']);
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
    
    // Reminders routes - Use {reminder} for Reminder model binding
    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::get('/reminders/{reminder}', [ReminderController::class, 'show']);
    Route::get('/reminders/{reminder}/edit', [ReminderController::class, 'edit']);
    Route::put('/reminders/{reminder}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy']);

    // Settings routes (for Vue SPA)
    Route::put('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile']);
    Route::put('/settings/preferences', [\App\Http\Controllers\SettingsController::class, 'updatePreferences']);
    Route::put('/settings/password', [\App\Http\Controllers\SettingsController::class, 'changePassword']);
    Route::delete('/settings/account', [\App\Http\Controllers\SettingsController::class, 'deleteAccount']);

    // Dashboard stats
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);
});
