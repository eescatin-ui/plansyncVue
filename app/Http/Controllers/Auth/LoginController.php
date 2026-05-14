<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'csrf_token' => csrf_token(), // for Vue SPA
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => $user->avatar_color ?? '#4361ee'
            ]
        ]);
    }

    // Failed login — was wrongly returning success before
    return response()->json([
        'success' => false,
        'message' => 'Invalid email or password.',
    ], 401);
}
    
public function logout(Request $request)
{
    // Only delete token if it exists (API auth), skip for session auth
    if ($request->user() && $request->user()->currentAccessToken()) {
        $request->user()->currentAccessToken()->delete();
    }
    
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/login');
}
}