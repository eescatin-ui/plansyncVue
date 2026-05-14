<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            
            // Generate new token (plain text for client, hashed for database)
            $plainTextToken = Str::random(60);
            $hashedToken = hash('sha256', $plainTextToken);
            
            // Store hashed token in database
            $user->api_token = $hashedToken;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $plainTextToken,  // Send plain token to client
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_color' => $user->avatar_color ?? '#4361ee'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.',
        ], 401);
    }
    
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Delete the api_token
            $user->api_token = null;
            $user->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}