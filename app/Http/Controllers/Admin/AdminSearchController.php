<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSearchController extends Controller
{
    /**
     * Display the search page (Blade view for SPA entry)
     */
    public function search(Request $request)
    {
        return view('admin.search');
    }

    /**
     * API: Perform global search across all modules
     */
    public function api(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'results' => [
                    'users' => [],
                    'classes' => [],
                    'tasks' => [],
                    'notes' => [],
                    'reminders' => []
                ],
                'total' => 0,
                'query' => $query
            ]);
        }
        
        $results = [
            'users' => $this->searchUsers($query),
            'classes' => $this->searchClasses($query),
            'tasks' => $this->searchTasks($query),
            'notes' => $this->searchNotes($query),
            'reminders' => $this->searchReminders($query)
        ];
        
        $total = array_sum(array_map('count', $results));
        
        return response()->json([
            'results' => $results,
            'total' => $total,
            'query' => $query
        ]);
    }

    /**
     * Search users
     */
    private function searchUsers($query)
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    /**
     * Search classes
     */
    private function searchClasses($query)
    {
        return ClassSchedule::with('user')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('location', 'like', "%{$query}%")
            ->orWhere('instructor', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    /**
     * Search tasks
     */
    private function searchTasks($query)
    {
        return Task::with('user')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderBy('due_date')
            ->take(10)
            ->get();
    }

    /**
     * Search notes
     */
    private function searchNotes($query)
    {
        return Note::with('user')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->orWhereJsonContains('tags', $query)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Search reminders
     */
    private function searchReminders($query)
    {
        return Reminder::with('user')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderBy('reminder_time')
            ->take(10)
            ->get();
    }

    /**
     * Advanced search with filters
     */
    public function advanced(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|min:2',
            'type' => 'nullable|in:users,classes,tasks,notes,reminders',
            'status' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);
        
        $query = $validated['q'] ?? '';
        $type = $validated['type'] ?? null;
        
        $results = [];
        
        if (!$type || $type === 'users') {
            $results['users'] = $this->advancedSearchUsers($validated);
        }
        
        if (!$type || $type === 'classes') {
            $results['classes'] = $this->advancedSearchClasses($validated);
        }
        
        if (!$type || $type === 'tasks') {
            $results['tasks'] = $this->advancedSearchTasks($validated);
        }
        
        if (!$type || $type === 'notes') {
            $results['notes'] = $this->advancedSearchNotes($validated);
        }
        
        if (!$type || $type === 'reminders') {
            $results['reminders'] = $this->advancedSearchReminders($validated);
        }
        
        return response()->json($results);
    }

    /**
     * Advanced search for users
     */
    private function advancedSearchUsers($filters)
    {
        $query = User::query();
        
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['q']}%")
                  ->orWhere('email', 'like', "%{$filters['q']}%");
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('created_at', 'desc')->take(20)->get();
    }

    /**
     * Advanced search for classes
     */
    private function advancedSearchClasses($filters)
    {
        $query = ClassSchedule::with('user');
        
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['q']}%")
                  ->orWhere('location', 'like', "%{$filters['q']}%")
                  ->orWhere('instructor', 'like', "%{$filters['q']}%");
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('name')->take(20)->get();
    }

    /**
     * Advanced search for tasks
     */
    private function advancedSearchTasks($filters)
    {
        $query = Task::with('user');
        
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['q']}%")
                  ->orWhere('description', 'like', "%{$filters['q']}%");
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('due_date')->take(20)->get();
    }

    /**
     * Advanced search for notes
     */
    private function advancedSearchNotes($filters)
    {
        $query = Note::with('user');
        
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['q']}%")
                  ->orWhere('content', 'like', "%{$filters['q']}%")
                  ->orWhereJsonContains('tags', $filters['q']);
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('updated_at', 'desc')->take(20)->get();
    }

    /**
     * Advanced search for reminders
     */
    private function advancedSearchReminders($filters)
    {
        $query = Reminder::with('user');
        
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['q']}%")
                  ->orWhere('description', 'like', "%{$filters['q']}%");
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('reminder_time', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('reminder_time', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('reminder_time')->take(20)->get();
    }
}