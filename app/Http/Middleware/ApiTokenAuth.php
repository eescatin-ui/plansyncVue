<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Try Bearer token first (mobile app)
        $token = $request->bearerToken();
        
        if ($token) {
            $hashedToken = hash('sha256', $token);
            $user = User::where('api_token', $hashedToken)->first();
            
            if ($user) {
                auth()->guard('web')->login($user);
                // Explicitly set user resolver so $request->user() always works
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
                return $next($request);
            }
        }
        
        // 2. Fall back to session auth (Vue SPA)
        if (auth()->guard('web')->check()) {
            return $next($request);
        }
        
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }
}