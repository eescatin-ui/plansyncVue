<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;

class AdminReminderController extends Controller
{
    public function index(Request $request)
    {
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get();
        $tasks = Task::orderBy('title')->get();
        
        // Build reminders query
        $query = Reminder::with(['user', 'task'])->latest();
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('task_id') && $request->task_id) {
            $query->where('task_id', $request->task_id);
        }
        
        if ($request->has('status') && $request->status) {
            if ($request->status === 'upcoming') {
                $query->where('reminder_time', '>=', now());
            } elseif ($request->status === 'past') {
                $query->where('reminder_time', '<', now());
            }
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('task', function($taskQuery) use ($search) {
                      $taskQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }
        
        $reminders = $query->paginate(10);
        $allUsers = User::orderBy('name')->get();
        $allTasks = Task::orderBy('title')->get();
        
        // Calculate stats
        $now = now();
        $stats = [
            'totalReminders' => Reminder::count(),
            'upcomingReminders' => Reminder::where('reminder_time', '>=', $now)->count(),
            'pastReminders' => Reminder::where('reminder_time', '<', $now)->count(),
            'remindersWithTasks' => Reminder::whereNotNull('task_id')->count(),
            'todaysReminders' => Reminder::whereDate('reminder_time', $now->toDateString())->count(),
        ];
        
        // Get upcoming reminders for the next 24 hours
        $next24Hours = Reminder::with('user')
            ->whereBetween('reminder_time', [$now, $now->copy()->addHours(24)])
            ->orderBy('reminder_time')
            ->take(5)
            ->get();
        
        return view('admin.reminders.index', [
            'reminders' => $reminders,
            'users' => $users,
            'tasks' => $tasks,
            'allUsers' => $allUsers,
            'allTasks' => $allTasks,
            'stats' => $stats,
            'next24Hours' => $next24Hours,
        ]);
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'reminder_time' => 'required|date',
                'task_id' => 'nullable|exists:tasks,id',
            ]);
            
            // Validate reminder time is in the future
            if (strtotime($validated['reminder_time']) <= time()) {
                return back()->withErrors(['reminder_time' => 'Reminder time must be in the future.'])->withInput();
            }
            
            Reminder::create($validated);
            
            return redirect()->route('admin.reminders.index')
                ->with('success', 'Reminder created successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating reminder: ' . $e->getMessage())->withInput();
        }
    }
    
    public function edit($id)
    {
        // This is handled via modal in the view
    }
    
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'reminder_time' => 'required|date',
                'task_id' => 'nullable|exists:tasks,id',
            ]);
            
            $reminder = Reminder::findOrFail($id);
            
            // Only validate future time for upcoming reminders
            if ($reminder->reminder_time->isFuture() && strtotime($validated['reminder_time']) <= time()) {
                return back()->withErrors(['reminder_time' => 'Reminder time must be in the future.'])->withInput();
            }
            
            $reminder->update($validated);
            
            return redirect()->route('admin.reminders.index')
                ->with('success', 'Reminder updated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating reminder: ' . $e->getMessage())->withInput();
        }
    }
    
    public function destroy($id)
    {
        try {
            $reminder = Reminder::findOrFail($id);
            $reminderTitle = $reminder->title;
            $reminder->delete();
            
            return redirect()->route('admin.reminders.index')
                ->with('success', 'Reminder "' . $reminderTitle . '" deleted successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting reminder: ' . $e->getMessage());
        }
    }
}