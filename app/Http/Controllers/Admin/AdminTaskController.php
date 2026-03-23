<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTaskController extends Controller
{
    public function index(Request $request)
    {
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get();
        
        // Build tasks query
        $query = Task::with('user')->latest();
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $tasks = $query->paginate(10);
        $allUsers = User::orderBy('name')->get();
        
        // Calculate stats
        $stats = [
            'totalUsers' => User::count(),
            'totalClasses' => ClassSchedule::count(),
            'totalTasks' => Task::count(),
            'totalNotes' => Note::count(),
            'totalReminders' => Reminder::count(),
            'pendingTasks' => Task::where('status', 'todo')->count(),
            'inProgressTasks' => Task::where('status', 'inprogress')->count(),
            'completedTasks' => Task::where('status', 'done')->count(),
        ];
        
        return view('admin.tasks.index', [
            'tasks' => $tasks,
            'users' => $users,
            'allUsers' => $allUsers,
            'stats' => $stats,
            'pendingTasks' => $stats['pendingTasks'],
            'inProgressTasks' => $stats['inProgressTasks'],
            'completedTasks' => $stats['completedTasks'],
        ]);
    }

    public function create()
    {
        // This will be handled by the modal
        return redirect()->route('admin.tasks.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'nullable|in:low,medium,high',
        ]);
        
        try {
            $task = Task::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'priority' => $request->priority,
            ]);
            
            return redirect()->route('admin.tasks.index')
                ->with('success', 'Task created successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating task: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // This will be handled by the modal
        return redirect()->route('admin.tasks.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:todo,inprogress,done',
            'priority' => 'nullable|in:low,medium,high',
        ]);
        
        try {
            $task = Task::findOrFail($id);
            
            $task->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'priority' => $request->priority,
            ]);
            
            return redirect()->route('admin.tasks.index')
                ->with('success', 'Task updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating task: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $taskTitle = $task->title;
            $task->delete();
            
            return redirect()->route('admin.tasks.index')
                ->with('success', 'Task "' . $taskTitle . '" deleted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->route('admin.tasks.index')
                ->with('error', 'Error deleting task: ' . $e->getMessage());
        }
    }
}