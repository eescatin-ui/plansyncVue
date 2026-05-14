<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $query = ClassSchedule::where('user_id', $user->id);
        
        // Filter by day if provided
        if ($request->has('day')) {
            $query->where('day', $request->day);
        }
        
        // Filter for today's classes
        if ($request->has('today')) {
            $today = Carbon::today()->format('l');
            $query->where('day', $today);
        }
        
        $classes = $query->orderBy('time')->get();
        
        // Check if the request expects a specific format
        // For React Native (returns array directly - Vue.js expects this too)
        return response()->json($classes);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'color' => 'required|string'
        ]);
        
        $class = ClassSchedule::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'time' => $validated['time'],
            'location' => $validated['location'],
            'day' => $validated['day'],
            'color' => $validated['color']
        ]);
        
        return response()->json($class, 201);
    }
    
    public function edit(Request $request, ClassSchedule $schedule)
    {
        $user = $request->user();
        
        if (!$user || $schedule->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($schedule);
    }
    
    public function update(Request $request, ClassSchedule $schedule)
    {
        $user = $request->user();
        
        if (!$user || $schedule->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'color' => 'required|string'
        ]);
        
        $schedule->update($validated);
        
        return response()->json($schedule);
    }
    
    public function destroy(Request $request, ClassSchedule $schedule)
    {
        $user = $request->user();
        
        if (!$user || $schedule->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $schedule->delete();
        
        return response()->json(['message' => 'Class deleted successfully']);
    }
    
    public function show(Request $request, ClassSchedule $schedule)
    {
        $user = $request->user();
        
        if (!$user || $schedule->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($schedule);
    }
}