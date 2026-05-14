<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Get current user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Update profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
            'avatar_color' => 'nullable|string',
        ]);

        $request->user()->update($request->only(['name', 'email', 'avatar_color']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'user' => $request->user()->fresh(),
        ]);
    }

    // Upload profile image
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->user()->profile_image) {
            Storage::disk('public')->delete($request->user()->profile_image);
        }

        $path = $request->file('image')->store('profile-images', 'public');
        $request->user()->update(['profile_image' => $path]);

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
        ]);
    }

    // Get profile image
    public function getProfileImage(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'url' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
        ]);
    }

    // Remove profile image
    public function removeProfileImage(Request $request)
    {
        if ($request->user()->profile_image) {
            Storage::disk('public')->delete($request->user()->profile_image);
            $request->user()->update(['profile_image' => null]);
        }
        return response()->json(['success' => true]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $request->new_password]);

        return response()->json(['success' => true, 'message' => 'Password changed']);
    }

    // Delete account
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->courses()->delete();
        $user->tasks()->delete();
        $user->notes()->delete();
        $user->reminders()->delete();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['success' => true, 'message' => 'Account deleted']);
    }
}