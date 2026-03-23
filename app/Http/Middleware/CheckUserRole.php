<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    public function handle(Request $request, Closure $next)
    {
        // If user is admin AND not viewing as admin
        if (auth()->user()->is_admin && !session('viewing_as_admin', false)) {
            // Redirect to switch page or admin dashboard
            return redirect()->route('switch-to-admin')
                ->with('info', 'You are an admin. Switch to admin mode?');
        }
        
        return $next($request);
    }
}