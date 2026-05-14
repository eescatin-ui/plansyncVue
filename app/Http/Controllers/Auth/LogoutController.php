<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
public function logout(Request $request)
{
    // Only revoke token if using Sanctum API token auth (not session)
    if ($request->user() && $request->user()->currentAccessToken()) {
        $request->user()->currentAccessToken()->delete();
    }
    
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/login');
}
}