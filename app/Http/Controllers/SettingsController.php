<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        
        return view('settings.index', [
            'user' => $user,
            'preferences' => $preferences
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar_color' => 'nullable|string'
        ]);
        
        $user->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully!',
                'user' => $user
            ]);
        }
        
        return redirect()->route('settings.index')->with('success', 'Profile updated successfully!');
    }
    
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'theme' => 'in:light,dark',
            'default_view' => 'in:week,month',
            'email_notifications' => 'boolean',
            'task_reminders' => 'boolean',
            'class_notifications' => 'boolean'
        ]);
        
        $preferences = $user->preferences ?? [];
        $preferences = array_merge($preferences, $validated);
        
        $user->preferences = $preferences;
        $user->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Preferences saved successfully!',
                'preferences' => $preferences
            ]);
        }
        
        return redirect()->route('settings.index')->with('success', 'Preferences saved successfully!');
    }
    
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Current password is incorrect.');
                }
            }],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Password changed successfully!'
            ]);
        }
        
        return redirect()->route('settings.index')->with('success', 'Password changed successfully!');
    }
    
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        
        // You might want to add additional validation here
        $user->delete();
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account deleted successfully']);
        }
        
        Auth::logout();
        return redirect('/')->with('success', 'Account deleted successfully');
    }
}