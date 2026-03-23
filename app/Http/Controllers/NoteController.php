<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');
        
        $notes = Note::where('user_id', Auth::id())
            ->when($query, function ($q) use ($query) {
                return $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('title', 'like', "%{$query}%")
                             ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // If it's an AJAX request (from Vue), return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($notes);
        }

        // Regular browser request - return view
        return view('notes.index', [
            'notes' => $notes,
            'query' => $query
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|string'
        ]);

        $note = Note::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : []
        ]);

        if ($request->wantsJson()) {
            return response()->json($note, 201);
        }

        return redirect()->route('notes.index')->with('success', 'Note created successfully!');
    }

    public function update(Request $request, Note $note)
{
    // Check if user is authorized to update this note
    if ($note->user_id !== Auth::id()) {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        abort(403);
    }

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'tags' => 'nullable|string'
    ]);

    // Convert tags string to array for storage
    $tags = [];
    if (!empty($validated['tags'])) {
        // Split by comma and trim whitespace
        $tags = array_map('trim', explode(',', $validated['tags']));
        // Remove empty tags
        $tags = array_filter($tags);
        // Re-index array
        $tags = array_values($tags);
    }

    $note->update([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'tags' => $tags
    ]);

    // Refresh the note to get any updated attributes
    $note->refresh();

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json($note);
    }

    return redirect()->route('notes.index')->with('success', 'Note updated successfully!');
}

public function destroy(Request $request, Note $note)
{
    // Check if user is authorized to delete this note
    if ($note->user_id !== Auth::id()) {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        abort(403);
    }
    
    try {
        $note->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Note deleted successfully']);
        }

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully!');
    } catch (\Exception $e) {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Failed to delete note'], 500);
        }
        return redirect()->route('notes.index')->with('error', 'Failed to delete note!');
    }
}
}