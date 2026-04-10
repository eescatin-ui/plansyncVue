<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\AdminTaskController;
use App\Http\Controllers\Admin\AdminNoteController;
use App\Http\Controllers\Admin\AdminReminderController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminSearchController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ADMIN ROUTES
// =============================================
Route::get('/admin/login', function () {
    return view('app');
})->name('admin.login');

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::get('/admin/setup', [AdminAuthController::class, 'createAdminUser']);

// =============================================
// PROTECTED ADMIN ROUTES
// =============================================
Route::middleware(['auth:admin'])->group(function () {
    
    // PAGE ROUTES (Return Vue SPA) - ADD NAMES HERE
    Route::get('/admin/dashboard', function () { return view('app'); })->name('admin.dashboard');
    Route::get('/admin/classes', function () { return view('app'); })->name('admin.classes');
    Route::get('/admin/users', function () { return view('app'); })->name('admin.users');
    Route::get('/admin/tasks', function () { return view('app'); })->name('admin.tasks');
    Route::get('/admin/notes', function () { return view('app'); })->name('admin.notes');
    Route::get('/admin/reminders', function () { return view('app'); })->name('admin.reminders');
    Route::get('/admin/analytics', function () { return view('app'); })->name('admin.analytics');
    Route::get('/admin/search', function () { return view('app'); })->name('admin.search');
    
    // ========== CLASSES API ROUTES ==========
    Route::get('/admin/classes/api', [AdminClassController::class, 'api']);
    Route::get('/admin/classes/api/{id}', [AdminClassController::class, 'show']);
    Route::get('/admin/classes/list', [AdminClassController::class, 'list']);
    Route::post('/admin/classes', [AdminClassController::class, 'store']);
    Route::put('/admin/classes/{id}', [AdminClassController::class, 'update']);
    Route::delete('/admin/classes/{id}', [AdminClassController::class, 'destroy']);
    
    // ========== USERS API ROUTES ==========
    Route::get('/admin/users/api', [AdminUserController::class, 'api']);
    Route::get('/admin/users/api/{id}', [AdminUserController::class, 'show']);
    Route::get('/admin/users/list', [AdminUserController::class, 'list']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    
    // ========== DASHBOARD API ==========
    Route::get('/admin/api/dashboard/stats', [AdminDashboardController::class, 'stats']);
    Route::get('/admin/api/dashboard/recent-users', [AdminDashboardController::class, 'recentUsers']);
    Route::get('/admin/api/dashboard/recent-tasks', [AdminDashboardController::class, 'recentTasks']);
    
    // ========== AUTH API ==========
    Route::get('/admin/api/verify', [AdminAuthController::class, 'verify']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    
    // Tasks API
    Route::get('/admin/tasks/api', [AdminTaskController::class, 'api']);
    Route::get('/admin/tasks/api/{id}', [AdminTaskController::class, 'show']);
    Route::post('/admin/tasks', [AdminTaskController::class, 'store']);
    Route::put('/admin/tasks/{id}', [AdminTaskController::class, 'update']);
    Route::delete('/admin/tasks/{id}', [AdminTaskController::class, 'destroy']);
    
    // Notes API
    Route::get('/admin/notes/api', [AdminNoteController::class, 'api']);
    Route::get('/admin/notes/api/{id}', [AdminNoteController::class, 'show']);
    Route::post('/admin/notes', [AdminNoteController::class, 'store']);
    Route::put('/admin/notes/{id}', [AdminNoteController::class, 'update']);
    Route::delete('/admin/notes/{id}', [AdminNoteController::class, 'destroy']);
    
    // Reminders API
    Route::get('/admin/reminders/api', [AdminReminderController::class, 'api']);
    Route::get('/admin/reminders/api/{id}', [AdminReminderController::class, 'show']);
    Route::post('/admin/reminders', [AdminReminderController::class, 'store']);
    Route::put('/admin/reminders/{id}', [AdminReminderController::class, 'update']);
    Route::delete('/admin/reminders/{id}', [AdminReminderController::class, 'destroy']);
    
    // Analytics API
    Route::get('/admin/analytics/data', [AdminAnalyticsController::class, 'data']);
    
    // Search API
    Route::get('/admin/search/api', [AdminSearchController::class, 'api']);
});