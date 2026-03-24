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
    $user = Auth::id();
    $today = Carbon::today();
    $todayName = $today->format('l');
    
    // Get stats
    $stats = [
        'classes_count' => ClassSchedule::where('user_id', $user)
            ->where('day', $todayName)->count(),
        'tasks_count' => Task::where('user_id', $user)
            ->where('status', '!=', 'done')->count(),
        'notes_count' => Note::where('user_id', $user)->count(),
        'reminders_count' => Reminder::where('user_id', $user)
            ->where('reminder_time', '>', $today)
            ->where('type', 'user')
            ->count(),
    ];
    
    // Get today's classes
    $classesToday = ClassSchedule::where('user_id', $user)
        ->where('day', $todayName)
        ->orderBy('time')
        ->get();
    
    // Get upcoming tasks
    $tasksUpcoming = Task::where('user_id', $user)
        ->where('status', '!=', 'done')
        ->whereDate('due_date', '>=', $today)
        ->orderBy('due_date')
        ->limit(5)
        ->get();
    
    // Get recent notes
    $notesRecent = Note::where('user_id', $user)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    // Get recent personal reminders
    $recentReminders = Reminder::where('user_id', $user)
        ->where('type', 'user')
        ->where('reminder_time', '>', $today)
        ->orderBy('reminder_time')
        ->limit(5)
        ->get();
    
    return view('dashboard.index', [
        'stats' => $stats,
        'classesToday' => $classesToday,
        'tasksUpcoming' => $tasksUpcoming,
        'notesRecent' => $notesRecent,
        'recentReminders' => $recentReminders,
        'userName' => Auth::user()->name
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