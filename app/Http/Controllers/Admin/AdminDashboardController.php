<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
       // Get stats (only counts, not full records)
    $stats = [
        'totalUsers' => User::count(),
        'totalClasses' => ClassSchedule::count(),
        'totalTasks' => Task::count(),
        'totalNotes' => Note::count(),
        'totalReminders' => Reminder::count(),
    ];
    
    // Calculate user growth
    $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
    $lastMonthUsers = User::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
    
    $userGrowth = $lastMonthUsers > 0 
        ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 2)
        : ($currentMonthUsers > 0 ? 100 : 0);
    
    // Get recent data (limited records)
    $recentUsers = User::latest()->take(5)->get();
    $recentTasks = Task::with('user')->latest()->take(5)->get();
    
    // Active sessions
    $activeSessions = $this->getActiveSessions();
    
    return view('admin.dashboard.index', [
        'stats' => $stats,
        'userGrowth' => $userGrowth,
        'recentUsers' => $recentUsers,
        'recentTasks' => $recentTasks,
        'activeSessions' => $activeSessions,
        // No need to pass $users or $tasks - they're loaded via AJAX
        ]);
    }
    
    /**
     * Get active sessions count
     * This is a placeholder - implement according to your session driver
     */
    private function getActiveSessions()
    {
        // For database sessions
        if (config('session.driver') === 'database') {
            return \DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(config('session.lifetime')))
                ->count();
        }
        
        // Default placeholder
        return 12;
    }
    
    /**
     * Refresh dashboard stats via AJAX
     */
    public function refreshStats(Request $request)
    {
        if ($request->ajax()) {
            $stats = [
                'totalUsers' => User::count(),
                'totalClasses' => ClassSchedule::count(),
                'totalTasks' => Task::count(),
                'totalNotes' => Note::count(),
                'totalReminders' => Reminder::count(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'activeSessions' => $this->getActiveSessions(),
            ]);
        }
        
        return redirect()->route('admin.dashboard');
    }
    
    /**
     * Get user growth data for charts
     */
    public function getUserGrowthData()
    {
        $months = [];
        $userCounts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthName = $month->format('M');
            
            $count = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $months[] = $monthName;
            $userCounts[] = $count;
        }
        
        return response()->json([
            'months' => $months,
            'userCounts' => $userCounts,
        ]);
    }
}