<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNoteController extends Controller
{
    public function index(Request $request)
    {
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get();
        
        // Build notes query
        $query = Note::with('user')->latest();
        
        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by tag
        if ($request->has('tag') && $request->tag) {
            $tag = $request->tag;
            $query->whereJsonContains('tags', $tag);
        }
        
        $notes = $query->paginate(10);
        $allUsers = User::orderBy('name')->get();
        
        // Calculate stats
        $stats = [
            'totalNotes' => Note::count(),
            'recentNotes' => Note::whereDate('created_at', '>=', now()->subDays(7))->count(),
            'notesWithTags' => Note::whereNotNull('tags')->where('tags', '!=', '[]')->count(),
            'notesPerUser' => round(Note::count() / max(User::count(), 1), 1),
        ];
        
        // Get top tags
        $allTags = Note::whereNotNull('tags')
            ->where('tags', '!=', '[]')
            ->pluck('tags')
            ->flatMap(function($tags) {
                return is_array($tags) ? $tags : [];
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->toArray();
        
        // Check if there's a success message from create/update
        $success = session('success');
        $error = session('error');
        
        return view('admin.notes.index', [
            'notes' => $notes,
            'users' => $users,
            'allUsers' => $allUsers,
            'stats' => $stats,
            'topTags' => $allTags,
            'success' => $success,
            'error' => $error
        ]);
    }
    
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.notes.create', compact('users'));
    }
    
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'tags' => 'nullable|string|max:500',
            ]);
            
            // Convert tags from comma-separated string to array
            if (!empty($validated['tags'])) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $tags = array_unique(array_filter($tags));
                $validated['tags'] = $tags;
            } else {
                $validated['tags'] = [];
            }
            
            $note = Note::create($validated);
            
            DB::commit();
            
            return redirect()->route('admin.notes.index')
                ->with('success', 'Note created successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating note: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $note = Note::with('user')->findOrFail($id);
        
        return view('admin.notes.show', compact('note'));
    }
    
    public function edit($id)
    {
        $note = Note::findOrFail($id);
        $users = User::orderBy('name')->get();
        
        return view('admin.notes.edit', compact('note', 'users'));
    }
    
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $note = Note::findOrFail($id);
            
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'tags' => 'nullable|string|max:500',
            ]);
            
            // Convert tags from comma-separated string to array
            if (!empty($validated['tags'])) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $tags = array_unique(array_filter($tags));
                $validated['tags'] = $tags;
            } else {
                $validated['tags'] = [];
            }
            
            $note->update($validated);
            
            DB::commit();
            
            return redirect()->route('admin.notes.index')
                ->with('success', 'Note updated successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating note: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $note = Note::findOrFail($id);
            $noteTitle = $note->title;
            $note->delete();
            
            DB::commit();
            
            return redirect()->route('admin.notes.index')
                ->with('success', 'Note "' . $noteTitle . '" deleted successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting note: ' . $e->getMessage());
        }
    }
}