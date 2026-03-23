<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        // If already logged in as admin, redirect to admin dashboard
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login request
     */
    public function login(Request $request)
    {
        // Validate credentials
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists and is admin
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email.',
            ])->withInput($request->only('email'));
        }

        // Check if user is an admin
        if (!$user->is_admin) {
            return back()->withErrors([
                'email' => 'This account does not have admin privileges.',
            ])->withInput($request->only('email'));
        }

        // Attempt to authenticate
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Redirect to intended URL or admin dashboard
            return redirect()->intended(route('admin.dashboard'));
        }

        // If authentication fails
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout admin user - FIXED VERSION
     */
    public function logout(Request $request)
    {
        // Log the user out
        Auth::logout();
        
        // Invalidate the session
        $request->session()->invalidate();
        
        // Regenerate the CSRF token
        $request->session()->regenerateToken();
        
        // Redirect to admin login page
        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Create initial admin user (for setup)
     */
    public function createAdminUser()
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@plansync.com')->exists();
        
        if (!$adminExists) {
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@plansync.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully!',
                'credentials' => [
                    'email' => 'admin@plansync.com',
                    'password' => 'admin123'
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Admin user already exists.'
        ]);
    }
}