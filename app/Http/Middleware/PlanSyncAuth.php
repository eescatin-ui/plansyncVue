<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PlanSyncAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('plansync_user_id')) {
            return redirect()->route('plansync.login');
        }

        return $next($request);
    }
}