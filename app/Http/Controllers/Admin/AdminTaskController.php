<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    /**
     * Show tasks management page (returns Vue SPA)
     */
    public function index()
    {
        return view('app');
    }

    /**
     * API: Get tasks with filters and pagination (includes stats like AdminClasses)
     */
    public function api(Request $request)
    {
        $query = Task::with('user');
        
        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        
        $perPage = $request->get('per_page', 10);
        $tasks = $query->latest()->paginate($perPage);
        
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        
        // Calculate stats - same pattern as AdminClasses
        $stats = [
            'totalTasks' => Task::count(),
            'pendingTasks' => Task::where('status', 'todo')->count(),
            'inProgressTasks' => Task::where('status', 'inprogress')->count(),
            'completedTasks' => Task::where('status', 'done')->count(),
        ];
        
        // Return in same format as AdminClasses
        return response()->json([
            'tasks' => $tasks,
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * API: Get single task
     */
    public function show($id)
    {
        try {
            $task = Task::with('user')->findOrFail($id);
            return response()->json($task);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Task not found'], 404);
        }
    }
    
    /**
     * API: Get task statistics (standalone endpoint - kept for backward compatibility)
     */
    public function stats()
    {
        $stats = [
            'totalTasks' => Task::count(),
            'pendingTasks' => Task::where('status', 'todo')->count(),
            'inProgressTasks' => Task::where('status', 'inprogress')->count(),
            'completedTasks' => Task::where('status', 'done')->count(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * API: Get overdue tasks
     */
    public function overdue()
    {
        $overdueTasks = Task::with('user')
            ->where('status', '!=', 'done')
            ->where('due_date', '<', now())
            ->get();
        
        return response()->json($overdueTasks);
    }
    
    /**
     * API: Get all users for dropdown
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * API: Create task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|in:work,personal,shopping,health,other',
        ]);
        
        try {
            $task = Task::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update task
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|in:work,personal,shopping,health,other',
        ]);
        
        try {
            $task->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating task: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Delete task
     */
    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $taskTitle = $task->title;
            $task->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Task '{$taskTitle}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }
}