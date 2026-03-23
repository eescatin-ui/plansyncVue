<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Task;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReminderController extends Controller
{
    /**
     * Display a listing of the reminders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q');
        $now = Carbon::now();
        $today = Carbon::today();
        
        // 1. Get user-created reminders (personal)
        $userReminders = Reminder::where('user_id', $user->id)
            ->where('reminder_time', '>', $now)
            ->when($query, function ($q) use ($query) {
                return $q->where('title', 'like', "%{$query}%");
            })
            ->orderBy('reminder_time')
            ->get()
            ->map(function($reminder) {
                return (object) [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'reminder_time' => $reminder->reminder_time,
                    'type' => 'user',
                    'subtitle' => null
                ];
            });
        
        // 2. Get task-based reminders (tasks due soon)
        $taskReminders = $this->generateTaskReminders($user->id);
        
        // 3. Get ALL class reminders (every class from schedule)
        $classReminders = $this->generateClassReminders($user->id);
        
        // 4. Combine all reminders
        $allReminders = collect()
            ->merge($userReminders)
            ->merge($taskReminders)
            ->merge($classReminders)
            ->sortBy(function($item) {
                // Sort by reminder_time, with class-now having highest priority
                if (isset($item->type) && $item->type === 'class-now') return 0;
                return $item->reminder_time ? strtotime($item->reminder_time) : PHP_INT_MAX;
            })
            ->values();

        // Log for debugging
        \Log::info('Total reminders: ' . $allReminders->count());
        \Log::info('Class reminders: ' . $classReminders->count());
        \Log::info('Task reminders: ' . $taskReminders->count());
        \Log::info('User reminders: ' . $userReminders->count());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($allReminders);
        }

        return view('reminders.index', [
            'allReminders' => $allReminders,
            'query' => $query
        ]);
    }

    /**
     * Generate reminders for all classes in the schedule
     */
    private function generateClassReminders($userId)
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $reminders = collect();
        
        // Get ALL classes (not just today's)
        $classes = ClassSchedule::where('user_id', $userId)->get();
        
        \Log::info('Total classes found: ' . $classes->count());
        
        foreach ($classes as $class) {
            try {
                // Parse class time (format: "7:00 AM - 8:00 AM" or similar)
                $timeParts = explode(' - ', $class->time);
                $startTimeStr = trim($timeParts[0]);
                
                // Parse the start time
                $startTime = Carbon::parse($startTimeStr);
                
                // Get the day of the week for this class
                $classDay = $class->day; // "Monday", "Tuesday", etc.
                
                // Find the next occurrence of this day
                $classDateTime = $this->getNextDayOfWeek($classDay, $startTime->hour, $startTime->minute);
                
                // Calculate minutes until class
                $minutesUntil = $now->diffInMinutes($classDateTime, false);
                
                // Determine if class is happening now (within class duration)
                // Assume class duration is 60-120 minutes
                $classDuration = 90; // average class duration in minutes
                
                // Add class reminder with all necessary information
                $reminderData = (object) [
                    'id' => 'class-' . $class->id,
                    'title' => $class->name,
                    'subtitle' => $class->location,
                    'reminder_time' => $classDateTime,
                    'type' => 'class',
                    'class_id' => $class->id,
                    'day' => $class->day,
                    'time' => $class->time,
                    'location' => $class->location,
                    'minutes_until' => $minutesUntil
                ];
                
                // Add type modifier based on timing
                if ($minutesUntil <= 0 && $minutesUntil > -$classDuration) {
                    // Class is happening now
                    $reminderData->type = 'class-now';
                    $reminderData->minutes_remaining = abs($minutesUntil);
                } elseif ($minutesUntil > 0 && $minutesUntil <= 60) {
                    // Class starts within the next hour
                    $reminderData->type = 'class-upcoming';
                } elseif ($minutesUntil > 60) {
                    // Class starts later
                    $reminderData->type = 'class-later';
                }
                
                $reminders->push($reminderData);
                \Log::info('Added class reminder: ' . $class->name . ' on ' . $class->day . ' at ' . $class->time);
                
            } catch (\Exception $e) {
                \Log::error('Error parsing class: ' . $class->name . ' - ' . $e->getMessage());
                
                // Still add the class with basic info even if time parsing fails
                $reminders->push((object) [
                    'id' => 'class-' . $class->id,
                    'title' => $class->name,
                    'subtitle' => $class->location . ' - ' . $class->time,
                    'reminder_time' => Carbon::now(),
                    'type' => 'class',
                    'class_id' => $class->id,
                    'day' => $class->day,
                    'time' => $class->time,
                    'location' => $class->location
                ]);
            }
        }
        
        return $reminders;
    }

    /**
     * Generate reminders for tasks
     */
    private function generateTaskReminders($userId)
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $reminders = collect();
        
        // Get tasks that are due soon (next 7 days) and not done
        $tasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->get();
        
        foreach ($tasks as $task) {
            try {
                $dueDateTime = Carbon::parse($task->due_date);
                $minutesUntil = $now->diffInMinutes($dueDateTime, false);
                
                $reminders->push((object) [
                    'id' => 'task-' . $task->id,
                    'title' => $task->title,
                    'subtitle' => $task->description ? substr($task->description, 0, 50) . '...' : null,
                    'reminder_time' => $dueDateTime,
                    'type' => 'task',
                    'task_id' => $task->id,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'minutes_until' => $minutesUntil
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error parsing task: ' . $task->title . ' - ' . $e->getMessage());
            }
        }
        
        return $reminders;
    }

    /**
     * Get the next occurrence of a specific day of week
     */
    private function getNextDayOfWeek($dayName, $hour = 0, $minute = 0)
    {
        $days = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6
        ];
        
        $now = Carbon::now();
        $currentDayOfWeek = $now->dayOfWeek;
        $targetDayOfWeek = $days[$dayName] ?? 1; // Default to Monday if not found
        
        // Calculate days to add
        $daysToAdd = ($targetDayOfWeek - $currentDayOfWeek + 7) % 7;
        
        // If today is the target day and the time hasn't passed yet, use today
        if ($daysToAdd === 0) {
            $candidate = Carbon::today()->setTime($hour, $minute);
            if ($candidate > $now) {
                return $candidate;
            }
            // Otherwise, get next week
            $daysToAdd = 7;
        }
        
        // Return the next occurrence
        return Carbon::today()
            ->addDays($daysToAdd)
            ->setTime($hour, $minute);
    }

    /**
     * Store a newly created reminder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reminder_time' => 'required|date',
        ]);

        $reminder = Reminder::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'reminder_time' => $validated['reminder_time'],
            'type' => 'user'
        ]);

        if ($request->wantsJson()) {
            return response()->json($reminder, 201);
        }

        return redirect()->route('reminders.index')
            ->with('success', 'Reminder created successfully!');
    }

    /**
     * Show the form for editing the specified reminder.
     */
    public function edit(Reminder $reminder)
    {
        // Check authorization - make sure user owns the reminder
        if ($reminder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->wantsJson()) {
            return response()->json($reminder);
        }
        
        return view('reminders.edit', compact('reminder'));
    }

    /**
     * Update the specified reminder.
     */
    public function update(Request $request, Reminder $reminder)
    {
        // Check authorization
        if ($reminder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reminder_time' => 'required|date',
        ]);

        $reminder->update($validated);

        if ($request->wantsJson()) {
            return response()->json($reminder);
        }

        return redirect()->route('reminders.index')
            ->with('success', 'Reminder updated successfully!');
    }

    /**
     * Remove the specified reminder.
     */
    public function destroy(Reminder $reminder)
    {
        // Check authorization
        if ($reminder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $reminder->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Reminder deleted successfully']);
        }

        return redirect()->route('reminders.index')
            ->with('success', 'Reminder deleted successfully!');
    }
}