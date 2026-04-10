<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Task;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = Carbon::today();
        $todayName = $today->format('l');
        
        // Get user-created reminders
        $userReminders = Reminder::where('user_id', $user->id)
            ->where('type', 'user')
            ->where('reminder_time', '>', $now)
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
        
        // Get task reminders
        $taskReminders = $this->generateTaskReminders($user->id);
        
        // Get class reminders
        $classReminders = $this->generateClassReminders($user->id);
        
        // Combine all reminders
        $allReminders = collect()
            ->merge($userReminders)
            ->merge($taskReminders)
            ->merge($classReminders)
            ->sortBy(function($item) {
                return $item->reminder_time ? strtotime($item->reminder_time) : PHP_INT_MAX;
            })
            ->values();
        
        return response()->json($allReminders);
    }
    
    private function generateClassReminders($userId)
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $reminders = collect();
        
        $classes = ClassSchedule::where('user_id', $userId)->get();
        
        foreach ($classes as $class) {
            try {
                $timeParts = explode(' - ', $class->time);
                $startTimeStr = trim($timeParts[0]);
                $startTime = Carbon::parse($startTimeStr);
                $classDay = $class->day;
                
                $classDateTime = $this->getNextDayOfWeek($classDay, $startTime->hour, $startTime->minute);
                $minutesUntil = $now->diffInMinutes($classDateTime, false);
                $classDuration = 90;
                
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
                
                if ($minutesUntil <= 0 && $minutesUntil > -$classDuration) {
                    $reminderData->type = 'class-now';
                    $reminderData->minutes_remaining = abs($minutesUntil);
                } elseif ($minutesUntil > 0 && $minutesUntil <= 60) {
                    $reminderData->type = 'class-upcoming';
                } elseif ($minutesUntil > 60) {
                    $reminderData->type = 'class-later';
                }
                
                $reminders->push($reminderData);
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $reminders;
    }
    
    private function generateTaskReminders($userId)
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $reminders = collect();
        
        $tasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->get();
        
        foreach ($tasks as $task) {
            try {
                $dueDateTime = Carbon::parse($task->due_date);
                $minutesUntil = $now->diffInMinutes($dueDateTime, false);
                
                $reminders->push((object) [
                    'id' => 'task-' . $task->id,
                    'title' => $task->title,
                    'subtitle' => $task->description ? substr($task->description, 0, 50) : null,
                    'reminder_time' => $dueDateTime,
                    'type' => 'task',
                    'task_id' => $task->id,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'minutes_until' => $minutesUntil
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $reminders;
    }
    
    private function getNextDayOfWeek($dayName, $hour = 0, $minute = 0)
    {
        $days = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2,
            'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6
        ];
        
        $now = Carbon::now();
        $currentDayOfWeek = $now->dayOfWeek;
        $targetDayOfWeek = $days[$dayName] ?? 1;
        
        $daysToAdd = ($targetDayOfWeek - $currentDayOfWeek + 7) % 7;
        
        if ($daysToAdd === 0) {
            $candidate = Carbon::today()->setTime($hour, $minute);
            if ($candidate > $now) {
                return $candidate;
            }
            $daysToAdd = 7;
        }
        
        return Carbon::today()->addDays($daysToAdd)->setTime($hour, $minute);
    }
    
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
        
        return response()->json($reminder, 201);
    }
    
    public function edit(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($reminder);
    }
    
    public function update(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reminder_time' => 'required|date',
        ]);
        
        $reminder->update($validated);
        
        return response()->json($reminder);
    }
    
    public function destroy(Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $reminder->delete();
        
        return response()->json(['message' => 'Reminder deleted successfully']);
    }
}