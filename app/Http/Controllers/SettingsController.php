<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar_color' => 'nullable|string'
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => $user->avatar_color
            ]
        ]);
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
        
        return response()->json([
            'message' => 'Preferences saved successfully!',
            'preferences' => $preferences
        ]);
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
        
        return response()->json(['message' => 'Password changed successfully!']);
    }
    
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        $user->delete();
        
        return response()->json(['message' => 'Account deleted successfully']);
    }
}