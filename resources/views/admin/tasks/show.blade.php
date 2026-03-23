@extends('layouts.admin')

@section('title', 'Task Details')

@section('content')
<div class="module" id="tasks">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-tasks"></i> Task Details</h2>
        <div>
            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="btn" style="background: var(--gray);">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <!-- Task Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Task Information</h3>
            </div>
            <div class="card-body">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%; padding: 0.5rem 0;"><strong>Title:</strong></td>
                        <td style="padding: 0.5rem 0;">{{ $task->title }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Description:</strong></td>
                        <td style="padding: 0.5rem 0;">{{ $task->description ?? 'No description' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>User:</strong></td>
                        <td style="padding: 0.5rem 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($task->user->name, 0, 1)) }}
                                </div>
                                {{ $task->user->name }} ({{ $task->user->email }})
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Due Date:</strong></td>
                        <td style="padding: 0.5rem 0;">
                            {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y g:i A') }}
                            @if($task->due_date < now() && !in_array($task->status, ['done', 'cancelled']))
                                <span style="color: var(--danger); margin-left: 0.5rem;">(Overdue)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Status:</strong></td>
                        <td style="padding: 0.5rem 0;">
                            @php
                                $statusColors = [
                                    'todo' => 'var(--gray)',
                                    'inprogress' => 'var(--warning)',
                                    'done' => 'var(--success)',
                                    'cancelled' => 'var(--danger)'
                                ];
                            @endphp
                            <span class="badge" style="background: {{ $statusColors[$task->status] ?? 'var(--gray)' }}; color: white; padding: 0.5rem 1rem; border-radius: 4px;">
                                {{ ucfirst($task->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Priority:</strong></td>
                        <td style="padding: 0.5rem 0;">
                            @php
                                $priorityColors = [
                                    'low' => 'var(--success)',
                                    'medium' => 'var(--warning)',
                                    'high' => 'var(--danger)'
                                ];
                            @endphp
                            <span class="badge" style="background: {{ $priorityColors[$task->priority] ?? 'var(--gray)' }}; color: white; padding: 0.5rem 1rem; border-radius: 4px;">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Category:</strong></td>
                        <td style="padding: 0.5rem 0;">{{ $task->category ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Created:</strong></td>
                        <td style="padding: 0.5rem 0;">{{ $task->created_at->format('F j, Y g:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Last Updated:</strong></td>
                        <td style="padding: 0.5rem 0;">{{ $task->updated_at->format('F j, Y g:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Task Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <form action="{{ route('admin.tasks.update', $task) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="done">
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-check"></i> Mark as Done
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.tasks.update', $task) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="inprogress">
                        <button type="submit" class="btn btn-warning" style="width: 100%; background: var(--warning);">
                            <i class="fas fa-spinner"></i> Mark as In Progress
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" 
                          onsubmit="return confirm('Delete this task?')" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 100%;">
                            <i class="fas fa-trash"></i> Delete Task
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Reminders -->
    @if($task->reminders->count() > 0)
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bell"></i> Related Reminders</h3>
        </div>
        <div class="card-body">
            <div class="activity-list">
                @foreach($task->reminders as $reminder)
                <div class="activity-item">
                    <i class="fas fa-bell" style="color: var(--secondary);"></i>
                    <div>
                        <strong>{{ $reminder->title }}</strong>
                        <p>{{ \Carbon\Carbon::parse($reminder->reminder_time)->format('F j, Y g:i A') }}</p>
                        <small>Status: {{ $reminder->is_active ? 'Active' : 'Inactive' }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection