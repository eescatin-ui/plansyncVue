<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminNoteController extends Controller
{
    /**
     * Display the admin notes view (returns Vue SPA)
     */
    public function index()
    {
        return view('app');
    }
    
    /**
     * API endpoint for fetching notes data (includes stats like AdminClasses)
     */
    public function api(Request $request)
    {
        $query = Note::with('user');
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->get('per_page', 10);
        $notes = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Ensure tags are decoded properly
        foreach ($notes as $note) {
            if (is_string($note->tags)) {
                $note->tags = json_decode($note->tags, true) ?: [];
            }
            // Also ensure tags is always an array
            if (!is_array($note->tags)) {
                $note->tags = [];
            }
        }
        
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        
        // Calculate stats - same pattern as AdminClasses
        $totalNotes = Note::count();
        $recentNotes = Note::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $notesWithTags = Note::whereNotNull('tags')->where('tags', '!=', '[]')->where('tags', '!=', 'null')->count();
        $uniqueUsers = Note::distinct('user_id')->count('user_id');
        
        $stats = [
            'totalNotes' => $totalNotes,
            'recentNotes' => $recentNotes,
            'notesWithTags' => $notesWithTags,
            'avgPerUser' => $uniqueUsers > 0 ? round($totalNotes / $uniqueUsers, 1) : 0
        ];
        
        // Return in same format as AdminClasses
        return response()->json([
            'notes' => $notes,
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get statistics for dashboard cards (standalone endpoint - kept for backward compatibility)
     */
    public function stats()
    {
        $totalNotes = Note::count();
        $recentNotes = Note::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $notesWithTags = Note::whereNotNull('tags')->where('tags', '!=', '[]')->where('tags', '!=', 'null')->count();
        $uniqueUsers = Note::distinct('user_id')->count('user_id');
        
        return response()->json([
            'totalNotes' => $totalNotes,
            'recentNotes' => $recentNotes,
            'notesWithTags' => $notesWithTags,
            'avgPerUser' => $uniqueUsers > 0 ? round($totalNotes / $uniqueUsers, 1) : 0
        ]);
    }
    
    /**
     * Get top tags for the tags cloud
     */
    public function tags()
    {
        $notes = Note::whereNotNull('tags')->where('tags', '!=', '[]')->where('tags', '!=', 'null')->get();
        $tagCounts = [];
        
        foreach ($notes as $note) {
            $tags = is_array($note->tags) ? $note->tags : (json_decode($note->tags, true) ?: []);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag) {
                    $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                }
            }
        }
        
        arsort($tagCounts);
        $topTags = array_slice($tagCounts, 0, 20);
        
        return response()->json($topTags);
    }
    
    /**
     * Get list of users for filters
     */
    public function list()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * Get all users for dropdown (alias for list)
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }
    
    /**
     * Store a new note
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'tags' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            // Process tags
            $tags = [];
            if (!empty($request->tags)) {
                $tags = array_map('trim', explode(',', $request->tags));
                $tags = array_filter($tags);
            }
            
            $note = Note::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'content' => $request->content,
                'tags' => $tags
            ]);
            
            $note->load('user');
            
            return response()->json([
                'success' => true,
                'message' => 'Note created successfully',
                'note' => $note
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating note: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update a note
     */
    public function update(Request $request, $id)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'tags' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            // Process tags
            $tags = [];
            if (!empty($request->tags)) {
                $tags = array_map('trim', explode(',', $request->tags));
                $tags = array_filter($tags);
            }
            
            $note->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'content' => $request->content,
                'tags' => $tags
            ]);
            
            $note->load('user');
            
            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'note' => $note
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating note: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a note
     */
    public function destroy($id)
    {
        try {
            $note = Note::findOrFail($id);
            $noteTitle = $note->title;
            $note->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Note '{$noteTitle}' deleted successfully"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note: ' . $e->getMessage()
            ], 500);
        }
    }
}