<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminReminderController extends Controller
{
    /**
     * Display the admin reminders view (returns Vue SPA)
     */
    public function index()
    {
        return view('app');
    }
    
    /**
     * API endpoint for fetching reminders data (includes stats like AdminClasses)
     */
    public function api(Request $request)
    {
        $query = Reminder::with(['user', 'task']);
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('task_id') && $request->task_id) {
            $query->where('task_id', $request->task_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->get('per_page', 10);
        $reminders = $query->orderBy('reminder_time', 'asc')->paginate($perPage);
        
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        
        // Get all tasks for filter dropdown
        $tasks = Task::select('id', 'title', 'user_id')->with('user')->orderBy('title')->get();
        
        // Calculate stats - same pattern as AdminClasses
        $now = Carbon::now();
        $totalReminders = Reminder::count();
        $upcomingReminders = Reminder::where('reminder_time', '>', $now)->count();
        $pastReminders = Reminder::where('reminder_time', '<', $now)->count();
        $remindersWithTasks = Reminder::whereNotNull('task_id')->count();
        $todaysReminders = Reminder::whereDate('reminder_time', $now->toDateString())->count();
        
        $stats = [
            'totalReminders' => $totalReminders,
            'upcomingReminders' => $upcomingReminders,
            'pastReminders' => $pastReminders,
            'remindersWithTasks' => $remindersWithTasks,
            'todaysReminders' => $todaysReminders
        ];
        
        // Get next 24 hours reminders
        $next24HoursReminders = Reminder::with(['user', 'task'])
            ->where('reminder_time', '>', $now)
            ->where('reminder_time', '<', $now->copy()->addHours(24))
            ->orderBy('reminder_time', 'asc')
            ->get();
        
        // Return in same format as AdminClasses
        return response()->json([
            'reminders' => $reminders,
            'users' => $users,
            'tasks' => $tasks,
            'stats' => $stats,
            'next24HoursReminders' => $next24HoursReminders
        ]);
    }
    
    /**
     * Get statistics for dashboard cards (standalone endpoint - kept for backward compatibility)
     */
    public function stats()
    {
        $now = Carbon::now();
        $totalReminders = Reminder::count();
        $upcomingReminders = Reminder::where('reminder_time', '>', $now)->count();
        $pastReminders = Reminder::where('reminder_time', '<', $now)->count();
        $remindersWithTasks = Reminder::whereNotNull('task_id')->count();
        $todaysReminders = Reminder::whereDate('reminder_time', $now->toDateString())->count();
        
        return response()->json([
            'totalReminders' => $totalReminders,
            'upcomingReminders' => $upcomingReminders,
            'pastReminders' => $pastReminders,
            'remindersWithTasks' => $remindersWithTasks,
            'todaysReminders' => $todaysReminders
        ]);
    }
    
    /**
     * Get upcoming reminders for next 24 hours
     */
    public function upcoming()
    {
        $now = Carbon::now();
        $upcomingReminders = Reminder::with(['user', 'task'])
            ->where('reminder_time', '>', $now)
            ->where('reminder_time', '<', $now->copy()->addHours(24))
            ->orderBy('reminder_time', 'asc')
            ->get();
        
        return response()->json($upcomingReminders);
    }
    
    /**
     * Get list of users for filters
     */
    public function list()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * Get all users for dropdown (alias for list)
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * Get all tasks for dropdown
     */
    public function tasks()
    {
        $tasks = Task::select('id', 'title', 'user_id')->with('user')->orderBy('title')->get();
        return response()->json($tasks);
    }
    
    /**
     * Store a new reminder
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_time' => 'required|date',
            'task_id' => 'nullable|exists:tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $reminder = Reminder::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'reminder_time' => $request->reminder_time,
                'task_id' => $request->task_id,
                'is_active' => true
            ]);
            
            $reminder->load(['user', 'task']);
            
            return response()->json([
                'success' => true,
                'message' => 'Reminder created successfully',
                'reminder' => $reminder
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating reminder: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update a reminder
     */
    public function update(Request $request, $id)
    {
        try {
            $reminder = Reminder::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_time' => 'required|date',
            'task_id' => 'nullable|exists:tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $reminder->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'reminder_time' => $request->reminder_time,
                'task_id' => $request->task_id
            ]);
            
            $reminder->load(['user', 'task']);
            
            return response()->json([
                'success' => true,
                'message' => 'Reminder updated successfully',
                'reminder' => $reminder
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reminder: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a reminder
     */
    public function destroy($id)
    {
        try {
            $reminder = Reminder::findOrFail($id);
            $reminderTitle = $reminder->title;
            $reminder->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Reminder '{$reminderTitle}' deleted successfully"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting reminder: ' . $e->getMessage()
            ], 500);
        }
    }
}