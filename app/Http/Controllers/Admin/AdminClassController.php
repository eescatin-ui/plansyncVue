<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminClassController extends Controller
{
    public function index(Request $request)
    {
        // Get all users for the filter dropdown
        $users = User::orderBy('name')->get();
        
        // Start building the query
        $query = ClassSchedule::with('user')->latest();
        
        // Apply filters
        if ($request->has('day') && $request->day) {
            $query->where('day', $request->day);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('time', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $classes = $query->paginate(10);
        $allUsers = User::orderBy('name')->get(); // For the modal dropdown
        
        // Stats
        $today = now()->format('l');
        $todayClasses = ClassSchedule::where('day', $today)->count();
        $uniqueUsers = ClassSchedule::distinct('user_id')->count('user_id');
        $upcomingClasses = ClassSchedule::where('day', '>=', $today)->count();
        
        return view('admin.classes.index', [
            'classes' => $classes,
            'users' => $users,
            'allUsers' => $allUsers,
            'todayClasses' => $todayClasses,
            'uniqueUsers' => $uniqueUsers,
            'upcomingClasses' => $upcomingClasses,
        ]);
    }
    
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
        
        $class = ClassSchedule::create($validated);
        
        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully!');
        
    } catch (\Exception $e) {
        return redirect()->route('admin.classes.index')
            ->with('error', 'Error creating class: ' . $e->getMessage());
    }
}

public function update(Request $request, $id)
{
    try {
        $class = ClassSchedule::findOrFail($id);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
        
        $class->update($validated);
        
        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully!');
        
    } catch (\Exception $e) {
        return redirect()->route('admin.classes.index')
            ->with('error', 'Error updating class: ' . $e->getMessage());
    }
}

public function destroy($id)
{
    try {
        $class = ClassSchedule::findOrFail($id);
        $className = $class->name;
        $class->delete();
        
        return redirect()->route('admin.classes.index')
            ->with('success', 'Class "' . $className . '" deleted successfully!');
        
    } catch (\Exception $e) {
        return redirect()->route('admin.classes.index')
            ->with('error', 'Error deleting class: ' . $e->getMessage());
    }
}
}