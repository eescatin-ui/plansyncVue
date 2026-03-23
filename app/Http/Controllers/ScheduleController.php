<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Add this

class ScheduleController extends Controller
{
    use AuthorizesRequests; // Add this trait

    public function index(Request $request)
    {
        $classes = ClassSchedule::where('user_id', Auth::id())
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
            ->orderBy('time')
            ->get();
            
        $uniqueClassNames = $classes->pluck('name')->unique()->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($classes);
        }

        return view('schedule.index', [
            'classes' => $classes,
            'uniqueClassNames' => $uniqueClassNames
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'color' => 'required|string'
        ]);

        $class = ClassSchedule::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'time' => $validated['time'],
            'location' => $validated['location'],
            'day' => $validated['day'],
            'color' => $validated['color']
        ]);

        if ($request->wantsJson()) {
            return response()->json($class, 201);
        }

        return redirect()->route('schedule.index')->with('success', 'Class added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ClassSchedule $schedule)
    {
        // Check if the user owns this class
        if ($schedule->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        
        return response()->json($schedule);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ClassSchedule $schedule)
    {
        // Check if the user owns this class
        if ($schedule->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        
        // For API requests, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($schedule);
        }
        
        // For web requests, return the edit view
        return view('schedule.edit', compact('schedule'));
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        // Check if the user owns this class
        if ($schedule->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'color' => 'required|string'
        ]);

        $schedule->update($validated);

        if ($request->wantsJson()) {
            return response()->json($schedule);
        }

        return redirect()->route('schedule.index')->with('success', 'Class updated successfully!');
    }

    public function destroy(Request $request, ClassSchedule $schedule)
    {
        // Check if the user owns this class
        if ($schedule->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        
        $schedule->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Class deleted successfully']);
        }

        return redirect()->route('schedule.index')->with('success', 'Class deleted successfully!');
    }
}