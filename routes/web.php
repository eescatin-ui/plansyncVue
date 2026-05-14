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
// SPA CATCH-ALL ROUTE (MUST BE LAST)
// Serves Vue app for all non-API, non-admin routes
// =============================================
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!admin).*$');