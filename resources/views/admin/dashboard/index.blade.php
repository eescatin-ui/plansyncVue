@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="module active" id="dashboard">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-th-large"></i> Dashboard</h2>
        <div class="header-actions">
            <button class="btn btn-small" onclick="Admin.refreshDashboardStats()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card" onclick="window.location='{{ route('admin.users.index') }}'">
            <i class="fas fa-users fa-2x" style="color: #4361ee;"></i>
            <div class="stat-number" id="total-users-count">{{ $stats['totalUsers'] ?? 0 }}</div>
            <div class="stat-label">Total Users</div>
            <small class="stat-growth">+{{ $userGrowth }}% this month</small>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.classes.index') }}'">
            <i class="fas fa-book-open fa-2x" style="color: #7209b7;"></i>
            <div class="stat-number" id="total-classes-count">{{ $stats['totalClasses'] ?? 0  }}</div>
            <div class="stat-label">Total Classes</div>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.tasks.index') }}'">
            <i class="fas fa-tasks fa-2x" style="color: #f72585;"></i>
            <div class="stat-number" id="total-tasks-count">{{ $stats['totalTasks'] ?? 0  }}</div>
            <div class="stat-label">Total Tasks</div>
            <small class="stat-growth">65% completed</small>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.notes.index') }}'">
            <i class="fas fa-sticky-note fa-2x" style="color: #4cc9f0;"></i>
            <div class="stat-number" id="total-notes-count">{{ $stats['totalNotes'] ?? 0  }}</div>
            <div class="stat-label">Total Notes</div>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.reminders.index') }}'">
            <i class="fas fa-bell fa-2x" style="color: #3a0ca3;"></i>
            <div class="stat-number" id="total-reminders-count">{{ $stats['totalReminders'] ?? 0  }}</div>
            <div class="stat-label">Total Reminders</div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Recent Users</h3>
                <a href="{{ route('admin.users.index') }}" class="btn btn-small">View All</a>
            </div>
            <div class="activity-list">
                @forelse($recentUsers as $user)
                <div class="activity-item">
                    <div class="user-avatar">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p>{{ $user->email }}</p>
                        <small>Joined: {{ $user->created_at->format('M d, Y') }}</small>
                    </div>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-small">View</a>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No users yet</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Recent Tasks</h3>
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-small">View All</a>
            </div>
            <div class="activity-list">
                @forelse($recentTasks as $task)
                <div class="activity-item">
                    <div class="user-avatar" style="background-color: 
                        @if($task->status == 'done') #4cc9f0
                        @elseif($task->status == 'inprogress') #f72585
                        @else #4361ee
                        @endif;">
                        {{ strtoupper(substr($task->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <strong>{{ $task->title }}</strong>
                        <p>By: {{ $task->user->name }}</p>
                        <small>
                            Due: {{ $task->due_date->format('M d, Y') }} | 
                            Status: <span class="status-badge status-{{ $task->status }}">{{ ucfirst($task->status) }}</span>
                        </small>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <p>No tasks yet</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> System Status</h3>
            </div>
            <div class="system-stats">
                <div class="stat-item">
                    <span>Server Load</span>
                    <div class="progress-bar">
                        <div class="progress" style="width: 45%"></div>
                    </div>
                    <small>45%</small>
                </div>
                <div class="stat-item">
                    <span>Database Size</span>
                    <div class="progress-bar">
                        <div class="progress" style="width: 28%"></div>
                    </div>
                    <small>28% (2.8GB/10GB)</small>
                </div>
                <div class="stat-item">
                    <span>Active Sessions</span>
                    <span class="stat-value">{{ $activeSessions ?? 12 }}</span>
                </div>
                <div class="stat-item">
                    <span>Uptime</span>
                    <span class="stat-value">99.8%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.system-stats {
    padding: 1rem;
}
.stat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--light-gray);
}
.progress-bar {
    flex: 1;
    height: 8px;
    background: var(--light-gray);
    border-radius: 4px;
    margin: 0 1rem;
    overflow: hidden;
}
.progress-bar .progress {
    height: 100%;
    background: var(--primary);
    border-radius: 4px;
}
.stat-value {
    font-weight: bold;
    color: var(--primary);
}
.status-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-done {
    background: #d4edda;
    color: #155724;
}
.status-inprogress {
    background: #fff3cd;
    color: #856404;
}
.status-todo {
    background: #d1ecf1;
    color: #0c5460;
}
</style>
@endpush