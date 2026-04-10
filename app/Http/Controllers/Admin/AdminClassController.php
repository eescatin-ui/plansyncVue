<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminClassController extends Controller
{
    /**
     * API: Get all classes (returns format that Vue expects)
     */
    public function api(Request $request)
    {
        $query = ClassSchedule::with('user');
        
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
                  ->orWhere('time', 'like', "%{$search}%");
            });
        }
        
        $classes = $query->orderBy('day')->orderBy('time')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        
        $today = now()->format('l');
        $stats = [
            'totalClasses' => ClassSchedule::count(),
            'todayClasses' => ClassSchedule::where('day', $today)->count(),
            'uniqueUsers' => ClassSchedule::distinct('user_id')->count('user_id'),
        ];
        
        // Return format that Vue expects
        return response()->json([
            'classes' => $classes,
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * API: Get single class
     */
    public function show($id)
    {
        try {
            $class = ClassSchedule::with('user')->findOrFail($id);
            return response()->json($class);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Class not found'], 404);
        }
    }
    
    /**
     * API: Get list of classes for dropdown
     */
    public function list()
    {
        $classes = ClassSchedule::select('id', 'name')->orderBy('name')->get();
        return response()->json($classes);
    }
    
    /**
     * API: Store new class
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $class = ClassSchedule::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'time' => $request->time,
                'location' => $request->location,
                'day' => $request->day,
                'color' => $request->color ?? '#4361ee'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Class created successfully',
                'class' => $class
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating class: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update class
     */
    public function update(Request $request, $id)
    {
        try {
            $class = ClassSchedule::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Class not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $class->update([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'time' => $request->time,
                'location' => $request->location,
                'day' => $request->day,
                'color' => $request->color ?? $class->color
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Class updated successfully',
                'class' => $class
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating class: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Delete class
     */
    public function destroy($id)
    {
        try {
            $class = ClassSchedule::findOrFail($id);
            $className = $class->name;
            $class->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Class '{$className}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting class: ' . $e->getMessage()
            ], 500);
        }
    }
}