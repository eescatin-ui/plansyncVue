<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // If user is admin AND trying to access user routes
        if (auth()->check() && auth()->user()->is_admin) {
            // BUT only redirect if NOT explicitly trying to switch
            if (!$request->is('switch-to-user*')) {
                // Option 1: Redirect to admin dashboard
                return redirect()->route('admin.dashboard');
                
                // Option 2: Allow access but show warning
                // return $next($request);
            }
        }
        
        return $next($request);
    }
}