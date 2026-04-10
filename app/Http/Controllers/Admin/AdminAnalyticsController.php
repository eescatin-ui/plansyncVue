<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Show analytics page (returns Vue SPA)
     */
    public function index()
    {
        return view('app');
    }

    /**
     * API: Get analytics data
     */
    public function data(Request $request)
    {
        $period = $request->get('period', 'week');
        
        $analytics = [
            'overview' => $this->getOverviewStats($period),
            'userGrowth' => $this->getUserGrowthData($period),
            'contentStats' => $this->getContentStats(),
            'systemUsage' => $this->getSystemUsage(),
            'topActiveUsers' => $this->getTopActiveUsers(),
            'recentActivity' => $this->getRecentActivity()
        ];
        
        return response()->json($analytics);
    }

    private function getOverviewStats($period)
    {
        return [
            'totalUsers' => User::count(),
            'newUsers' => $this->getNewUsersCount($period),
            'activeUsers' => $this->getActiveUsersCount($period),
            'totalTasks' => Task::count(),
            'totalNotes' => Note::count(),
            'totalClasses' => ClassSchedule::count(),
            'totalReminders' => Reminder::count(),
            'avgTasksPerUser' => User::count() > 0 ? round(Task::count() / User::count(), 1) : 0,
            'avgClassesPerUser' => User::count() > 0 ? round(ClassSchedule::count() / User::count(), 1) : 0,
        ];
    }

    private function getNewUsersCount($period)
    {
        $startDate = $this->getStartDate($period);
        return User::where('created_at', '>=', $startDate)->count();
    }

    private function getActiveUsersCount($period)
    {
        $startDate = $this->getStartDate($period);
        return User::whereHas('tasks', function($query) use ($startDate) {
            $query->where('updated_at', '>=', $startDate);
        })->count();
    }

    private function getUserGrowthData($period)
    {
        $data = [];
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();
        
        $currentDate = clone $startDate;
        $previousTotal = 0;
        
        while ($currentDate <= $endDate) {
            $nextDate = clone $currentDate;
            if ($period === 'day') {
                $nextDate->addHour();
            } elseif ($period === 'week' || $period === 'month') {
                $nextDate->addDay();
            } else {
                $nextDate->addMonth();
            }
            
            $totalUsers = User::where('created_at', '<', $nextDate)->count();
            $newUsers = User::whereBetween('created_at', [$currentDate, $nextDate])->count();
            $growthRate = $previousTotal > 0 ? round(($totalUsers - $previousTotal) / $previousTotal * 100, 1) : 0;
            
            $data[] = [
                'date' => $currentDate->format($this->getDateFormat($period)),
                'total_users' => $totalUsers,
                'new_users' => $newUsers,
                'growth_rate' => $growthRate
            ];
            
            $previousTotal = $totalUsers;
            $currentDate = $nextDate;
        }
        
        return $data;
    }

    private function getContentStats()
    {
        return [
            'tasksByStatus' => [
                'pending' => Task::where('status', 'todo')->count(),
                'in_progress' => Task::where('status', 'inprogress')->count(),
                'completed' => Task::where('status', 'done')->count()
            ],
            'tasksByPriority' => [
                'low' => Task::where('priority', 'low')->count(),
                'medium' => Task::where('priority', 'medium')->count(),
                'high' => Task::where('priority', 'high')->count()
            ]
        ];
    }

    private function getSystemUsage()
    {
        return [
            'retentionRate' => 78.5,
            'churnRate' => 12.3,
            'deviceUsage' => [
                'desktop' => 45,
                'mobile' => 42,
                'tablet' => 13
            ]
        ];
    }

    private function getTopActiveUsers()
    {
        $users = User::withCount(['tasks', 'notes', 'classSchedules'])
            ->get()
            ->map(function($user) {
                $activityScore = $user->tasks_count + $user->notes_count + ($user->class_schedules_count * 2);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_tasks' => $user->tasks_count,
                    'total_notes' => $user->notes_count,
                    'total_classes' => $user->class_schedules_count,
                    'activity_score' => $activityScore,
                    'last_login' => $user->updated_at->diffForHumans()
                ];
            })
            ->sortByDesc('activity_score')
            ->take(10)
            ->values();
        
        return $users;
    }

    private function getRecentActivity()
    {
        $activities = collect();
        
        Task::with('user')->latest()->take(5)->get()->each(function($task) use ($activities) {
            $activities->push([
                'id' => 'task_' . $task->id,
                'type' => 'task_created',
                'icon' => 'tasks',
                'color' => 'primary',
                'title' => 'New Task Created',
                'description' => $task->title,
                'user' => $task->user,
                'time' => $task->created_at
            ]);
        });
        
        return $activities->sortByDesc('time')->take(10)->values();
    }

    private function getStartDate($period)
    {
        switch($period) {
            case 'day': return Carbon::today();
            case 'week': return Carbon::now()->subWeek();
            case 'month': return Carbon::now()->subMonth();
            case 'year': return Carbon::now()->subYear();
            default: return Carbon::now()->subWeek();
        }
    }

    private function getDateFormat($period)
    {
        switch($period) {
            case 'day': return 'H:00';
            case 'week': return 'D';
            case 'month': return 'M d';
            case 'year': return 'M Y';
            default: return 'M d';
        }
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', 'week');
        
        $data = [
            'generated_at' => Carbon::now()->toDateTimeString(),
            'period' => $period,
            'overview' => $this->getOverviewStats($period),
            'userGrowth' => $this->getUserGrowthData($period),
            'contentStats' => $this->getContentStats(),
            'topActiveUsers' => $this->getTopActiveUsers()
        ];
        
        if ($format === 'csv') {
            return $this->exportToCsv($data);
        }
        
        return response()->json($data);
    }

    private function exportToCsv($data)
    {
        $filename = "analytics_export_" . date('Y-m-d_His') . ".csv";
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Analytics Export']);
            fputcsv($file, ['Generated At', $data['generated_at']]);
            fputcsv($file, ['Period', $data['period']]);
            fputcsv($file, []);
            
            fputcsv($file, ['OVERVIEW STATISTICS']);
            fputcsv($file, ['Metric', 'Value']);
            foreach ($data['overview'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}