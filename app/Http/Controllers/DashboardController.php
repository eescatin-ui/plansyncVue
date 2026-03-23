<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayName = $today->format('l'); // Monday, Tuesday, etc.
        
        // Get stats
        $stats = [
            'classes_count' => ClassSchedule::where('user_id', $user->id)
                ->where('day', $todayName)
                ->count(),
            'tasks_count' => Task::where('user_id', $user->id)
                ->where('status', '!=', 'done')
                ->count(),
            'notes_count' => Note::where('user_id', $user->id)->count(),
            'reminders_count' => Reminder::where('user_id', $user->id)
                ->where('reminder_time', '>', $today)
                ->count(),
        ];
        
        // Get today's classes
        $classesToday = ClassSchedule::where('user_id', $user->id)
            ->where('day', $todayName)
            ->orderBy('time')
            ->get();
        
        // Get upcoming tasks (next 7 days, not done)
        $tasksUpcoming = Task::where('user_id', $user->id)
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        
        // Get recent notes
        $notesRecent = Note::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // For API requests (refresh)
        if ($request->wantsJson() || $request->ajax()) {
            if ($request->has('today')) {
                return response()->json($classesToday);
            }
            if ($request->has('upcoming')) {
                return response()->json($tasksUpcoming);
            }
            if ($request->has('recent')) {
                return response()->json($notesRecent);
            }
            if ($request->is('dashboard/stats')) {
                return response()->json($stats);
            }
            return response()->json([
                'stats' => $stats,
                'classes' => $classesToday,
                'tasks' => $tasksUpcoming,
                'notes' => $notesRecent
            ]);
        }
        
        // Web request - return view
        return view('dashboard.index', [
            'stats' => $stats,
            'classesToday' => $classesToday,
            'tasksUpcoming' => $tasksUpcoming,
            'notesRecent' => $notesRecent
        ]);
    }
    
    /**
     * API endpoint to refresh stats
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayName = $today->format('l');
        
        return response()->json([
            'classes_count' => ClassSchedule::where('user_id', $user->id)
                ->where('day', $todayName)
                ->count(),
            'tasks_count' => Task::where('user_id', $user->id)
                ->where('status', '!=', 'done')
                ->count(),
            'notes_count' => Note::where('user_id', $user->id)->count(),
            'reminders_count' => Reminder::where('user_id', $user->id)
                ->where('reminder_time', '>', $today)
                ->count(),
        ]);
    }
}