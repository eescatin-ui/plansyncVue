@extends('layouts.admin')

@section('title', 'Search Results')

@section('content')
<div class="module" id="search">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-search"></i> Search Results</h2>
        <div class="search-box" style="width: 400px;">
            <form action="{{ route('admin.search') }}" method="GET" style="display: flex; width: 100%;">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ $query }}" placeholder="Search across all data..." 
                       style="border: none; outline: none; width: 100%;">
            </form>
        </div>
    </div>
    
    @if(!$query)
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <p>Enter a search term to find users, classes, tasks, notes, or reminders</p>
    </div>
    @elseif($totalResults === 0)
    <div class="empty-state">
        <i class="fas fa-search-minus"></i>
        <p>No results found for "{{ $query }}"</p>
        <p style="color: var(--gray); margin-top: 0.5rem;">Try different keywords</p>
    </div>
    @else
    <div style="margin-bottom: 2rem;">
        <h3 style="color: var(--gray); margin-bottom: 1rem;">
            Found {{ $totalResults }} results for "{{ $query }}"
        </h3>
        
        <!-- Users Results -->
        @if(count($results['users']) > 0)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Users ({{ count($results['users']) }})</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @foreach($results['users'] as $user)
                    <div class="activity-item" onclick="window.location.href='{{ route('admin.users.show', $user) }}'" 
                         style="cursor: pointer;">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <p>{{ $user->email }}</p>
                            <small>Joined {{ $user->created_at->diffForHumans() }}</small>
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Classes Results -->
        @if(count($results['classes']) > 0)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book"></i> Classes ({{ count($results['classes']) }})</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @foreach($results['classes'] as $class)
                    <div class="activity-item" onclick="window.location.href='{{ route('admin.classes.edit', $class) }}'" 
                         style="cursor: pointer;">
                        <i class="fas fa-book" style="color: var(--accent);"></i>
                        <div>
                            <strong>{{ $class->name }}</strong>
                            <p>{{ $class->location }} • {{ $class->day }} {{ \Carbon\Carbon::parse($class->time)->format('h:i A') }}</p>
                            <small>By {{ $class->user->name }}</small>
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Tasks Results -->
        @if(count($results['tasks']) > 0)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Tasks ({{ count($results['tasks']) }})</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @foreach($results['tasks'] as $task)
                    <div class="activity-item" onclick="window.location.href='{{ route('admin.tasks.show', $task) }}'" 
                         style="cursor: pointer;">
                        <i class="fas fa-tasks" style="color: var(--warning);"></i>
                        <div>
                            <strong>{{ $task->title }}</strong>
                            <p>Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</p>
                            <small>Status: {{ ucfirst($task->status) }} • By {{ $task->user->name }}</small>
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Notes Results -->
        @if(count($results['notes']) > 0)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes ({{ count($results['notes']) }})</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @foreach($results['notes'] as $note)
                    <div class="activity-item" onclick="window.location.href='{{ route('admin.notes.show', $note) }}'" 
                         style="cursor: pointer;">
                        <i class="fas fa-sticky-note" style="color: var(--success);"></i>
                        <div>
                            <strong>{{ $note->title }}</strong>
                            <p>{{ Str::limit($note->content, 80) }}</p>
                            <small>By {{ $note->user->name }} • {{ $note->created_at->format('M d, Y') }}</small>
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Reminders Results -->
        @if(count($results['reminders']) > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell"></i> Reminders ({{ count($results['reminders']) }})</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @foreach($results['reminders'] as $reminder)
                    <div class="activity-item" onclick="window.location.href='{{ route('admin.reminders.show', $reminder) }}'" 
                         style="cursor: pointer;">
                        <i class="fas fa-bell" style="color: var(--secondary);"></i>
                        <div>
                            <strong>{{ $reminder->title }}</strong>
                            <p>{{ \Carbon\Carbon::parse($reminder->reminder_time)->format('M d, Y g:i A') }}</p>
                            <small>By {{ $reminder->user->name }} • {{ $reminder->is_active ? 'Active' : 'Inactive' }}</small>
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection