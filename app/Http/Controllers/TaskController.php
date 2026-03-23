<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        $query = Task::where('user_id', Auth::id());
        
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
        
        $tasks = $query->orderBy('due_date')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($tasks);
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'filter' => $filter
        ]);
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

        if ($request->wantsJson()) {
            return response()->json($task, 201);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function edit(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($task);
        }
        
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'required|in:low,medium,high'
        ]);

        $task->update($validated);
        
        $task->refresh();

        if ($request->wantsJson()) {
            return response()->json($task);
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        
        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }
}