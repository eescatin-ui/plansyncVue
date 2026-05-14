<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $reminders = Reminder::where('user_id', $user->id)
            ->orderBy('reminder_time', 'asc')
            ->get();
        
        return response()->json($reminders);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reminder_time' => 'required|date',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'reminder_time' => $validated['reminder_time'],
        ]);
        
        return response()->json($reminder, 201);
    }
    
    public function edit(Request $request, Reminder $reminder)
    {
        $user = $request->user();
        
        if (!$user || $reminder->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($reminder);
    }
    
    public function update(Request $request, Reminder $reminder)
    {
        $user = $request->user();
        
        if (!$user || $reminder->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reminder_time' => 'required|date',
        ]);

        $reminder->update($validated);
        return response()->json($reminder);
    }
    
    public function destroy(Request $request, Reminder $reminder)
    {
        $user = $request->user();
        
        if (!$user || $reminder->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $reminder->delete();
        return response()->json(['message' => 'Reminder deleted successfully']);
    }
    
    public function show(Request $request, Reminder $reminder)
    {
        $user = $request->user();
        
        if (!$user || $reminder->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($reminder);
    }
}