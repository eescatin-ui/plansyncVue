<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('user_id', Auth::id());
        
        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('upcoming')) {
            $query->whereDate('due_date', '>=', Carbon::today())
                  ->whereDate('due_date', '<=', Carbon::today()->addDays(7));
        }
        
        $tasks = $query->orderBy('due_date', 'asc')->get();
        
        return response()->json($tasks);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'required|in:low,medium,high'
        ]);
        
        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'priority' => $validated['priority']
        ]);
        
        return response()->json($task, 201);
    }
    
    public function edit(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($task);
    }
    
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'required|in:low,medium,high'
        ]);
        
        $task->update($validated);
        
        return response()->json($task);
    }
    
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
}