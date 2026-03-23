<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\Task;
use App\Models\Note;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false)
                    ->withCount(['classSchedules', 'tasks', 'notes', 'reminders']);
        
        // Search functionality
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }

        /**
     * Extract start time from time range
     */
    private function extractStartTime($timeRange)
    {
        if (!$timeRange) {
            return null;
        }
        
        // Handle time ranges like "9:00 PM - 10:00 PM"
        $parts = explode('-', $timeRange);
        if (count($parts) > 0) {
            $startTime = trim($parts[0]);
            
            // Try to parse as time
            try {
                return Carbon::parse($startTime)->format('h:i A');
            } catch (\Exception $e) {
                // If parsing fails, return the original start time
                return $startTime;
            }
        }
        
        return $timeRange;
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::where('is_admin', false)
                    ->withCount(['classSchedules', 'tasks', 'notes', 'reminders'])
                    ->with([
                        'classSchedules' => function($q) {
                            $q->latest()->limit(5);
                        },
                        'tasks' => function($q) {
                            $q->latest()->limit(5);
                        },
                        'notes' => function($q) {
                            $q->latest()->limit(5);
                        },
                        'reminders' => function($q) {
                            $q->latest()->limit(5);
                        }
                    ])->findOrFail($id);
        
        // Get task statistics
        $taskStats = DB::table('tasks')
            ->select('status', DB::raw('count(*) as count'))
            ->where('user_id', $id)
            ->groupBy('status')
            ->get();
            
        // Get class schedules by day - FIXED: Using correct column name 'day'
        $classByDay = DB::table('class_schedules')
            ->select('day', DB::raw('count(*) as count'))
            ->where('user_id', $id)
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();
        
        return view('admin.users.show', compact('user', 'taskStats', 'classByDay'));
    }

    public function edit($id)
    {
        $user = User::where('is_admin', false)->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('is_admin', false)->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::where('is_admin', false)->findOrFail($id);
        
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function list()
    {
        $users = User::select('id', 'name')->where('is_admin', false)->get();
        return response()->json($users);
    }
}