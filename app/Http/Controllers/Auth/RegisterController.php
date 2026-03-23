<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar_color' => $this->generateAvatarColor(),
            'preferences' => ['theme' => 'light', 'default_view' => 'week'],
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }

    private function generateAvatarColor()
    {
        $colors = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0'];
        return $colors[array_rand($colors)];
    }
}