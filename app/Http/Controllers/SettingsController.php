<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? ['theme' => 'light', 'default_view' => 'week'];
        
        return view('settings.index', compact('user', 'preferences'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'theme' => 'required|in:light,dark',
            'default_view' => 'required|in:week,month',
        ]);
        
        $user->update([
            'preferences' => $validated,
        ]);
        
        return back()->with('success', 'Preferences updated successfully!');
    }

    // Add this new method for changing password
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return back()->with('success', 'Password changed successfully!');
    }
}