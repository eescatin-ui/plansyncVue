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
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $period = $request->get('period', 'week'); // day, week, month, year
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set date range based on period
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        // Get analytics data
        $analytics = [
            'overview' => $this->getOverviewStats($dateRange),
            'userGrowth' => $this->getUserGrowthData($dateRange),
            'activityTrends' => $this->getActivityTrends($dateRange),
            'contentStats' => $this->getContentStatistics($dateRange),
            'topActiveUsers' => $this->getTopActiveUsers($dateRange),
            'systemUsage' => $this->getSystemUsageStats($dateRange),
            'recentActivity' => $this->getRecentActivity(),
        ];
        
        return view('admin.analytics.index', compact('analytics', 'period', 'dateRange'));
    }

    /**
     * Get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate),
                'end' => Carbon::parse($endDate),
            ];
        }
        
        switch ($period) {
            case 'day':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->subWeek()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->subMonth()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->subYear()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            default:
                return [
                    'start' => $now->copy()->subWeek()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
        }
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        return [
            'totalUsers' => User::count(),
            'activeUsers' => $hasLastLoginColumn 
                ? User::where('last_login_at', '>=', $dateRange['start'])->count()
                : User::where('updated_at', '>=', $dateRange['start'])->count(), // Fallback to updated_at
            'newUsers' => User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'totalClasses' => ClassSchedule::count(),
            'totalTasks' => Task::count(),
            'totalNotes' => Note::count(),
            'totalReminders' => Reminder::count(),
            'avgTasksPerUser' => $this->getAverageTasksPerUser(),
            'avgClassesPerUser' => $this->getAverageClassesPerUser(),
        ];
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData($dateRange)
    {
        $growthData = [];
        $currentDate = $dateRange['start']->copy();
        
        // Limit to 30 days max to prevent too many data points
        $maxDays = min($dateRange['start']->diffInDays($dateRange['end']), 30);
        
        // Use appropriate interval based on period length
        $interval = $maxDays > 7 ? floor($maxDays / 7) : 1;
        
        while ($currentDate <= $dateRange['end']) {
            $periodEnd = $currentDate->copy()->addDays($interval);
            
            $newUsers = User::whereBetween('created_at', [$currentDate, $periodEnd])->count();
            $totalUsers = User::where('created_at', '<=', $periodEnd)->count();
            
            $growthData[] = [
                'date' => $currentDate->format('M d'),
                'new_users' => $newUsers,
                'total_users' => $totalUsers,
                'growth_rate' => $this->calculateGrowthRate($currentDate, $periodEnd),
            ];
            
            $currentDate->addDays($interval);
        }
        
        return $growthData;
    }

    /**
     * Get activity trends
     */
    private function getActivityTrends($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if ($hasLastLoginColumn) {
            $dailyLogins = User::whereBetween('last_login_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy(DB::raw('DATE(last_login_at)'))
                ->select(DB::raw('DATE(last_login_at) as date'), DB::raw('COUNT(*) as count'))
                ->orderBy('date')
                ->get();
        } else {
            // Use created_at as fallback for user activity
            $dailyLogins = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->orderBy('date')
                ->get();
        }
        
        return [
            'dailyLogins' => $dailyLogins,
            
            'taskCompletion' => Task::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get(),
            
            'classCreation' => ClassSchedule::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->orderBy('date')
                ->get(),
        ];
    }

    /**
     * Get content statistics
     */
    private function getContentStatistics($dateRange)
    {
        // Check if category column exists in notes table
        $hasCategoryColumn = Schema::hasColumn('notes', 'category');
        
        // Check if type column exists in reminders table
        $hasTypeColumn = Schema::hasColumn('reminders', 'type');
        
        $notesByCategory = [];
        if ($hasCategoryColumn) {
            $notesByCategory = Note::groupBy('category')
                ->select('category', DB::raw('COUNT(*) as count'))
                ->get()
                ->pluck('count', 'category')
                ->toArray();
        }
        
        $remindersByType = [];
        if ($hasTypeColumn) {
            $remindersByType = Reminder::groupBy('type')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->get()
                ->pluck('count', 'type')
                ->toArray();
        }
        
        return [
            'tasksByStatus' => [
                'pending' => Task::where('status', 'pending')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'completed' => Task::where('status', 'completed')->count(),
            ],
            'tasksByPriority' => [
                'low' => Task::where('priority', 'low')->count(),
                'medium' => Task::where('priority', 'medium')->count(),
                'high' => Task::where('priority', 'high')->count(),
            ],
            'notesByCategory' => $notesByCategory,
            'remindersByType' => $remindersByType,
            'totalNotes' => Note::count(),
            'totalReminders' => Reminder::count(),
            'recentNotes' => Note::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'recentReminders' => Reminder::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
        ];
    }

    /**
     * Get top active users
     */
    private function getTopActiveUsers($dateRange, $limit = 10)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        $query = User::withCount(['tasks', 'classSchedules', 'notes', 'reminders'])
            ->with(['tasks' => function($query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }]);
        
        // Order by last_login_at if available, otherwise by updated_at
        if ($hasLastLoginColumn) {
            $query->orderBy('last_login_at', 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }
        
        return $query->limit($limit)
            ->get()
            ->map(function($user) use ($hasLastLoginColumn) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'last_login' => $hasLastLoginColumn && $user->last_login_at 
                        ? $user->last_login_at->diffForHumans() 
                        : ($user->updated_at ? $user->updated_at->diffForHumans() : 'Never'),
                    'total_tasks' => $user->tasks_count,
                    'total_classes' => $user->class_schedules_count,
                    'total_notes' => $user->notes_count,
                    'total_reminders' => $user->reminders_count,
                    'activity_score' => $this->calculateActivityScore($user),
                ];
            });
    }

    /**
     * Get system usage statistics
     */
    private function getSystemUsageStats($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        return [
            'peakHours' => $this->getPeakUsageHours($dateRange),
            'popularDays' => $this->getPopularDays($dateRange),
            'deviceUsage' => $this->getDeviceUsage($dateRange),
            'retentionRate' => $hasLastLoginColumn ? $this->calculateRetentionRate($dateRange) : 0,
            'churnRate' => $hasLastLoginColumn ? $this->calculateChurnRate($dateRange) : 0,
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($limit = 20)
    {
        $activities = collect();
        
        // Get recent user registrations
        $users = User::latest()->limit($limit)->get();
        $users->each(function($user) use ($activities) {
            $activities->push([
                'type' => 'user_registered',
                'title' => 'New User Registration',
                'description' => $user->name . ' joined PlanSync',
                'icon' => 'user-plus',
                'color' => 'success',
                'time' => $user->created_at,
                'user' => $user,
            ]);
        });
        
        // Get recent task completions
        $tasks = Task::where('status', 'completed')->latest()->limit($limit)->get();
        $tasks->each(function($task) use ($activities) {
            $activities->push([
                'type' => 'task_completed',
                'title' => 'Task Completed',
                'description' => $task->title . ' was completed',
                'icon' => 'check-circle',
                'color' => 'primary',
                'time' => $task->updated_at,
                'user' => $task->user,
            ]);
        });
        
        // Get recent class creations
        $classes = ClassSchedule::latest()->limit($limit)->get();
        $classes->each(function($class) use ($activities) {
            $activities->push([
                'type' => 'class_created',
                'title' => 'New Class Created',
                'description' => $class->course_name . ' was added',
                'icon' => 'calendar',
                'color' => 'info',
                'time' => $class->created_at,
                'user' => $class->user,
            ]);
        });
        
        return $activities->sortByDesc('time')->take($limit);
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate($startDate, $endDate)
    {
        $previousPeriodStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousPeriodEnd = $startDate;
        
        $previousUsers = User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
        $currentUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        
        if ($previousUsers == 0) {
            return $currentUsers > 0 ? 100 : 0;
        }
        
        return (($currentUsers - $previousUsers) / $previousUsers) * 100;
    }

    /**
     * Calculate average tasks per user
     */
    private function getAverageTasksPerUser()
    {
        $totalTasks = Task::count();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? round($totalTasks / $totalUsers, 2) : 0;
    }

    /**
     * Calculate average classes per user
     */
    private function getAverageClassesPerUser()
    {
        $totalClasses = ClassSchedule::count();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? round($totalClasses / $totalUsers, 2) : 0;
    }

    /**
     * Calculate activity score for a user
     */
    private function calculateActivityScore($user)
    {
        $score = 0;
        $score += $user->tasks_count * 2;
        $score += $user->class_schedules_count * 3;
        $score += $user->notes_count * 1;
        $score += $user->reminders_count * 1;
        
        // Check if last_login_at exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if ($hasLastLoginColumn && $user->last_login_at && $user->last_login_at->diffInDays(now()) <= 7) {
            $score += 10;
        } elseif (!$hasLastLoginColumn && $user->updated_at && $user->updated_at->diffInDays(now()) <= 7) {
            $score += 10; // Fallback to updated_at
        }
        
        return min($score, 100);
    }

    /**
     * Get peak usage hours
     */
    private function getPeakUsageHours($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if ($hasLastLoginColumn) {
            return User::whereBetween('last_login_at', [$dateRange['start'], $dateRange['end']])
                ->select(DB::raw('HOUR(last_login_at) as hour'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('HOUR(last_login_at)'))
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
        } else {
            // Use task creation as fallback
            return Task::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
        }
    }

    /**
     * Get popular days
     */
    private function getPopularDays($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if ($hasLastLoginColumn) {
            return User::whereBetween('last_login_at', [$dateRange['start'], $dateRange['end']])
                ->select(DB::raw('DAYNAME(last_login_at) as day'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DAYNAME(last_login_at)'))
                ->orderBy('count', 'desc')
                ->get();
        } else {
            // Use task creation as fallback
            return Task::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->select(DB::raw('DAYNAME(created_at) as day'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DAYNAME(created_at)'))
                ->orderBy('count', 'desc')
                ->get();
        }
    }

    /**
     * Get device usage (simplified - would need additional tracking)
     */
    private function getDeviceUsage($dateRange)
    {
        // This would require additional user_agent tracking in the users table
        return [
            'desktop' => rand(60, 75),
            'mobile' => rand(20, 35),
            'tablet' => rand(5, 10),
        ];
    }

    /**
     * Calculate retention rate
     */
    private function calculateRetentionRate($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if (!$hasLastLoginColumn) {
            return 0; // Can't calculate without last_login_at
        }
        
        $monthAgo = now()->subMonth();
        $twoMonthsAgo = now()->subMonths(2);
        
        $previousMonthUsers = User::whereBetween('created_at', [$twoMonthsAgo, $monthAgo])->count();
        $retainedUsers = User::whereBetween('created_at', [$twoMonthsAgo, $monthAgo])
            ->where('last_login_at', '>=', $monthAgo)
            ->count();
        
        return $previousMonthUsers > 0 ? round(($retainedUsers / $previousMonthUsers) * 100, 2) : 0;
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate($dateRange)
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');
        
        if (!$hasLastLoginColumn) {
            return 0; // Can't calculate without last_login_at
        }
        
        $monthAgo = now()->subMonth();
        $twoMonthsAgo = now()->subMonths(2);
        
        $previousMonthUsers = User::whereBetween('created_at', [$twoMonthsAgo, $monthAgo])->count();
        $churnedUsers = User::whereBetween('created_at', [$twoMonthsAgo, $monthAgo])
            ->where('last_login_at', '<', $monthAgo)
            ->count();
        
        return $previousMonthUsers > 0 ? round(($churnedUsers / $previousMonthUsers) * 100, 2) : 0;
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $period = $request->get('period', 'week');
        $format = $request->get('format', 'csv');
        $dateRange = $this->getDateRange($period);
        
        $data = [
            'overview' => $this->getOverviewStats($dateRange),
            'user_growth' => $this->getUserGrowthData($dateRange),
            'activity_trends' => $this->getActivityTrends($dateRange),
        ];
        
        if ($format === 'json') {
            return response()->json($data);
        }
        
        // For CSV export
        $filename = 'analytics_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write overview
            fputcsv($file, ['Overview Statistics']);
            foreach ($data['overview'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            
            fputcsv($file, []); // Empty row
            
            // Write user growth
            fputcsv($file, ['User Growth Data']);
            fputcsv($file, ['Date', 'New Users', 'Total Users', 'Growth Rate (%)']);
            foreach ($data['user_growth'] as $row) {
                fputcsv($file, [$row['date'], $row['new_users'], $row['total_users'], $row['growth_rate']]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}