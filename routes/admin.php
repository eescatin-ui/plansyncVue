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
| Admin Routes - Separate Login System
|--------------------------------------------------------------------------
|
| These routes are for administrators only with separate login
| Accessible at /admin/*
|
*/

// Admin Auth Routes (Separate from user auth)
Route::middleware('guest:web')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    
    // Optional: Admin setup route (remove in production)
    Route::get('/admin/setup', [AdminAuthController::class, 'createAdminUser']);
});

// Protected Admin Routes (Requires admin authentication)
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // AJAX data loading routes for modals
    Route::get('/users/list', [AdminUserController::class, 'list'])->name('users.list');
    Route::get('/tasks/list', [AdminTaskController::class, 'list'])->name('tasks.list');
    Route::get('/classes/list', [AdminClassController::class, 'list'])->name('classes.list');
    Route::get('/notes/list', [AdminNoteController::class, 'list'])->name('notes.list');
    Route::get('/reminders/list', [AdminReminderController::class, 'list'])->name('reminders.list');
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
    
    // Classes Management
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminClassController::class, 'index'])->name('index');
        Route::post('/', [AdminClassController::class, 'store'])->name('store');
        Route::get('/{class}', [AdminClassController::class, 'show'])->name('show');
        Route::get('/{class}/edit', [AdminClassController::class, 'edit'])->name('edit');
        Route::put('/{class}', [AdminClassController::class, 'update'])->name('update');
        Route::delete('/{class}', [AdminClassController::class, 'destroy'])->name('destroy');
    });
    
    // Tasks Management
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [AdminTaskController::class, 'index'])->name('index');
        Route::post('/', [AdminTaskController::class, 'store'])->name('store');
        Route::get('/{task}', [AdminTaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [AdminTaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [AdminTaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [AdminTaskController::class, 'destroy'])->name('destroy');
    });
    
    // Notes Management
Route::prefix('notes')->name('notes.')->group(function () {
    Route::get('/', [AdminNoteController::class, 'index'])->name('index');
    Route::post('/', [AdminNoteController::class, 'store'])->name('store');
    Route::put('/{note}', [AdminNoteController::class, 'update'])->name('update');
    Route::delete('/{note}', [AdminNoteController::class, 'destroy'])->name('destroy');
});
    
    // Reminders Management
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [AdminReminderController::class, 'index'])->name('index');
        Route::get('/create', [AdminReminderController::class, 'create'])->name('create');
        Route::post('/', [AdminReminderController::class, 'store'])->name('store');
        Route::get('/{reminder}', [AdminReminderController::class, 'show'])->name('show');
        Route::get('/{reminder}/edit', [AdminReminderController::class, 'edit'])->name('edit');
        Route::put('/{reminder}', [AdminReminderController::class, 'update'])->name('update');
        Route::delete('/{reminder}', [AdminReminderController::class, 'destroy'])->name('destroy');
        Route::post('/{reminder}/toggle-status', [AdminReminderController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/upcoming', [AdminReminderController::class, 'upcoming'])->name('upcoming');
    });
    
    // Additional routes
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/export', [AdminAnalyticsController::class, 'export'])->name('analytics.export');
    
    Route::get('/search', [AdminSearchController::class, 'search'])->name('search');
    
    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // Switch to user panel
    Route::get('/switch-to-user', function () {
        session(['viewing_as_admin' => false]);
        return redirect()->route('dashboard');
    })->name('switch-to-user');
});