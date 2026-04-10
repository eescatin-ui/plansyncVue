<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Check if it's an admin route
        if ($request->is('admin/*') || $request->is('admin')) {
            // For API requests, return null (will return 401)
            if ($request->expectsJson()) {
                return null;
            }
            // For web requests, redirect to the Vue SPA admin login page
            return '/admin/login';
        }
        
        // For user routes
        if (!$request->expectsJson()) {
            return '/login';
        }
        
        return null;
    }
}