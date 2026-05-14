<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $reminders = Reminder::where('user_id', Auth::id())
            ->where('type', 'user')
            ->orderBy('reminder_time', 'asc')
            ->get();
        
        return response()->json($reminders);
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
            'type' => 'user',
        ]);
        
        return response()->json($reminder, 201);
    }

    public function edit(Reminder $reminder)
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
        return response()->json(['message' => 'Deleted']);
    }
}