<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard (returns Vue SPA)
     */
    public function index()
    {
        return view('app');
    }

    /**
     * API: Get dashboard statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'totalUsers' => User::count(),
                'totalClasses' => ClassSchedule::count(),
                'totalTasks' => Task::count(),
                'totalNotes' => Note::count(),
                'totalReminders' => Reminder::count(),
                'newUsersThisMonth' => User::whereMonth('created_at', now()->month)->count(),
                'activeUsers' => User::whereHas('tasks', function($q) {
                    $q->where('updated_at', '>=', now()->subDays(30));
                })->count(),
                'pendingTasks' => Task::where('status', 'todo')->count(),
                'inProgressTasks' => Task::where('status', 'inprogress')->count(),
                'completedTasks' => Task::where('status', 'done')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get recent users
     */
    public function recentUsers()
    {
        try {
            $recentUsers = User::latest()->take(5)->get();
            return response()->json([
                'success' => true,
                'data' => $recentUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get recent tasks
     */
    public function recentTasks()
    {
        try {
            $recentTasks = Task::with('user')->latest()->take(5)->get();
            return response()->json([
                'success' => true,
                'data' => $recentTasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get recent activity
     */
    public function recentActivity(Request $request)
    {
        try {
            $period = $request->get('period', 'week');
            
            $data = [];
            
            if ($period === 'week') {
                $data['weeklyUsers'] = $this->getWeeklyData(User::class);
                $data['weeklyTasks'] = $this->getWeeklyData(Task::class);
                $data['weeklyNotes'] = $this->getWeeklyData(Note::class);
            } elseif ($period === 'month') {
                $data['monthlyUsers'] = $this->getMonthlyData(User::class);
                $data['monthlyTasks'] = $this->getMonthlyData(Task::class);
                $data['monthlyNotes'] = $this->getMonthlyData(Note::class);
            } else {
                $data['yearlyUsers'] = $this->getYearlyData(User::class);
                $data['yearlyTasks'] = $this->getYearlyData(Task::class);
                $data['yearlyNotes'] = $this->getYearlyData(Note::class);
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get system status
     */
    public function systemStatus()
    {
        try {
            $status = [
                'serverLoad' => $this->getServerLoad(),
                'dbUsage' => 28,
                'dbUsed' => '2.8GB',
                'dbTotal' => '10GB',
                'activeSessions' => rand(10, 50),
                'uptime' => 99.8,
                'phpVersion' => PHP_VERSION,
                'laravelVersion' => app()->version()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getWeeklyData($model)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = $model::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getMonthlyData($model)
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = $model::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getYearlyData($model)
    {
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $count = $model::whereMonth('created_at', $i)
                ->whereYear('created_at', now()->year)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getServerLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 10);
        }
        return 45;
    }
}