<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get the authenticated user
     */
    public function getUser(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar_color' => 'sometimes|string|max:7'
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => $user->avatar_color
            ]
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'total_classes' => $user->classSchedules()->count(),
                    'pending_tasks' => $user->tasks()->where('status', '!=', 'done')->count(),
                    'total_notes' => $user->notes()->count(),
                    'active_reminders' => $user->reminders()->count(),
                ]
            ]
        ]);
    }
    
    // ========== ADD THESE MISSING METHODS ==========
    
    /**
     * Get user's profile image
     */
    public function getProfileImage(Request $request)
    {
        $user = $request->user();
        
        // Return stored image URL or null
        if ($user->profile_image) {
            return response()->json([
                'success' => true,
                'url' => Storage::url($user->profile_image)
            ]);
        }
        
        return response()->json([
            'success' => true,
            'url' => null
        ]);
    }
    
    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);
        
        $user = $request->user();
        
        // Delete old image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profile_images', $filename, 'public');
            
            $user->profile_image = $path;
            $user->save();
            
            return response()->json([
                'success' => true,
                'url' => Storage::url($path),
                'message' => 'Profile image uploaded successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No image file provided'
        ], 400);
    }
    
    /**
     * Delete profile image
     */
    public function deleteProfileImage(Request $request)
    {
        $user = $request->user();
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->profile_image = null;
            $user->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Profile image deleted successfully'
        ]);
    }
    
    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Current password is incorrect.');
                }
            }],
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
    
    /**
     * Delete user account
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Delete profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}