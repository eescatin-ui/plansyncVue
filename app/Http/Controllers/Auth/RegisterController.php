<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'id' => Str::uuid()->toString(),  // ← Add this line
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar_color' => '#4361ee'
        ]);
        
        Auth::login($user);
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => $user->avatar_color
            ]
        ], 201);
    }
}