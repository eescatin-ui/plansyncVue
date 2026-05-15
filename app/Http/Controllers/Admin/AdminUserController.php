<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * API: Get all users (same pattern as AdminClassController)
     */
    public function api(Request $request)
    {
        try {
            $query = User::query();
            
            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            $users = $query->orderBy('created_at', 'desc')->get();
            
            // Add counts for each user (same as ClassSchedule with user relation)
            foreach ($users as $user) {
                $user->tasks_count = $user->tasks()->count();
                $user->notes_count = $user->notes()->count();
                $user->class_schedules_count = $user->classSchedules()->count();
                $user->reminders_count = $user->reminders()->count();
            }
            
            // Get stats (similar to classes stats)
            $stats = [
                'totalUsers' => User::count(),
                'newUsersThisMonth' => User::whereMonth('created_at', now()->month)->count(),
                'activeUsers' => User::whereHas('tasks')->count(),
            ];
            
            // Return same format as AdminClassController
            return response()->json([
                'users' => $users,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Get single user (same as show in AdminClassController)
     */
    public function show($id)
    {
        try {
            $user = User::with(['tasks', 'notes', 'classSchedules', 'reminders'])->findOrFail($id);
            
            // Add counts
            $user->tasks_count = $user->tasks->count();
            $user->notes_count = $user->notes->count();
            $user->class_schedules_count = $user->classSchedules->count();
            $user->reminders_count = $user->reminders->count();
            
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    
    /**
     * API: Get list of users for dropdown (same as list in AdminClassController)
     */
    public function list()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * API: Get user statistics (same as stats in AdminClassController)
     */
    public function stats()
    {
        $stats = [
            'totalUsers' => User::count(),
            'newUsersThisMonth' => User::whereMonth('created_at', now()->month)->count(),
            'activeUsers' => User::whereHas('tasks')->count(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * API: Store new user (same as store in AdminClassController)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update user (same as update in AdminClassController)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $user->name = $request->name;
            $user->email = $request->email;
            
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Delete user (same as destroy in AdminClassController)
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => "User '{$userName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }
}