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
        // For SPA, we return JSON data
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->getDashboardData());
        }
        
        // Fallback for direct access (should not happen in SPA)
        return view('app');
    }
    
    public function stats(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayName = $today->format('l');
        
        return response()->json([
            'classes_count' => ClassSchedule::where('user_id', $user->id)
                ->where('day', $todayName)->count(),
            'tasks_count' => Task::where('user_id', $user->id)
                ->where('status', '!=', 'done')->count(),
            'notes_count' => Note::where('user_id', $user->id)->count(),
            'reminders_count' => Reminder::where('user_id', $user->id)
                ->where('reminder_time', '>', $today)
                ->where('type', 'user')->count(),
        ]);
    }
    
    public function getDashboardData()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayName = $today->format('l');
        
        return [
            'stats' => [
                'classes_count' => ClassSchedule::where('user_id', $user->id)
                    ->where('day', $todayName)->count(),
                'tasks_count' => Task::where('user_id', $user->id)
                    ->where('status', '!=', 'done')->count(),
                'notes_count' => Note::where('user_id', $user->id)->count(),
                'reminders_count' => Reminder::where('user_id', $user->id)
                    ->where('reminder_time', '>', $today)
                    ->where('type', 'user')->count(),
            ],
            'classesToday' => ClassSchedule::where('user_id', $user->id)
                ->where('day', $todayName)
                ->orderBy('time')
                ->get(),
            'tasksUpcoming' => Task::where('user_id', $user->id)
                ->where('status', '!=', 'done')
                ->whereDate('due_date', '>=', $today)
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
            'notesRecent' => Note::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recentReminders' => Reminder::where('user_id', $user->id)
                ->where('type', 'user')
                ->where('reminder_time', '>', $today)
                ->orderBy('reminder_time')
                ->limit(5)
                ->get(),
            'userName' => $user->name
        ];
    }
}