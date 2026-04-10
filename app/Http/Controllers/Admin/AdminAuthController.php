<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('app');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $admin = Auth::guard('admin')->user();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                    ],
                    'redirect' => '/admin/dashboard'
                ]);
            }
            
            // For non-JSON requests, redirect to dashboard
            return redirect()->to('/admin/dashboard');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => '/admin/login'
            ]);
        }
        
        return redirect()->to('/admin/login');
    }

    public function verify(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            return response()->json([
                'authenticated' => true,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ]
            ]);
        }
        
        return response()->json(['authenticated' => false], 401);
    }

    public function createAdminUser()
    {
        if (Admin::count() > 0) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Admin user already exists'], 400);
            }
            return redirect()->to('/admin/login')->with('error', 'Admin user already exists');
        }

        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@plansync.com',
            'password' => Hash::make('admin123'),
            'is_super_admin' => true
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Admin user created successfully',
                'credentials' => [
                    'email' => 'admin@plansync.com',
                    'password' => 'admin123'
                ]
            ]);
        }

        return redirect()->to('/admin/login')->with('success', 'Admin user created. Email: admin@plansync.com, Password: admin123');
    }
}