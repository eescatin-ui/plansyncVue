<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return view('admin.search.index', [
                'results' => [],
                'query' => '',
                'totalResults' => 0
            ]);
        }
        
        // Search users
        $users = User::regularUsers()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Search classes
        $classes = ClassSchedule::with('user')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%")
                  ->orWhere('day', 'like', "%{$query}%")
                  ->orWhere('instructor', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Search tasks
        $tasks = Task::with('user')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Search notes
        $notes = Note::with('user')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('tags', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Search reminders
        $reminders = Reminder::with(['user', 'task'])
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        $results = [
            'users' => $users,
            'classes' => $classes,
            'tasks' => $tasks,
            'notes' => $notes,
            'reminders' => $reminders,
        ];
        
        $totalResults = $users->count() + $classes->count() + $tasks->count() + 
                       $notes->count() + $reminders->count();
        
        return view('admin.search.index', compact('results', 'query', 'totalResults'));
    }
}