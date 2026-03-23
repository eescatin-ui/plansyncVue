<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Check if user is trying to access admin login
                if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                    // Allow access to admin login even if logged in as regular user
                    return $next($request);
                }
                
                // Check if user is trying to access regular login
                if ($request->is('login') || $request->routeIs('login')) {
                    // If admin is logged in and trying to access regular login
                    if (Auth::user()->is_admin) {
                        return redirect()->route('admin.dashboard');
                    }
                    // If regular user is logged in
                    return redirect()->route('dashboard');
                }
                
                // Default redirect based on user type
                if (Auth::user()->is_admin) {
                    return redirect()->route('admin.dashboard');
                }
                
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}