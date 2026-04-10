<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Note::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Always return JSON for SPA
        return response()->json($notes);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|string'
        ]);
        
        // Convert tags string to array
        $tags = $validated['tags'] 
            ? array_map('trim', explode(',', $validated['tags']))
            : [];
        
        $note = Note::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $tags
        ]);
        
        return response()->json($note, 201);
    }
    
    public function edit(Request $request, Note $note)
    {
        // Check authorization
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($note);
    }
    
    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|string'
        ]);
        
        $tags = $validated['tags'] 
            ? array_map('trim', explode(',', $validated['tags']))
            : [];
        
        $note->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $tags
        ]);
        
        return response()->json($note);
    }
    
    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $note->delete();
        
        return response()->json(['message' => 'Note deleted successfully']);
    }
}