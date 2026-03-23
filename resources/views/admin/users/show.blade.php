<div class="user-details-modern">
    <!-- Header with Back Button -->
    <div class="user-header-bar">
        <a href="{{ route('admin.users.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
        <div class="header-actions">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-action btn-edit">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>

    <!-- User Profile Header -->
    <div class="user-profile-header">
        <div class="profile-avatar-section">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="profile-badge">
                <i class="fas fa-user"></i> Regular User
            </div>
        </div>
        
        <div class="profile-info">
            <h1 class="profile-name">{{ $user->name }}</h1>
            <p class="profile-email">
                <i class="fas fa-envelope"></i> {{ $user->email }}
            </p>
            <p class="profile-meta">
                <i class="fas fa-calendar-plus"></i> Joined {{ $user->created_at->format('F d, Y') }}
                <span class="meta-divider">•</span>
                <i class="fas fa-clock"></i> {{ $user->created_at->diffForHumans() }}
            </p>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number">{{ $user->class_schedules_count }}</div>
                <div class="stat-label">Classes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $user->tasks_count }}</div>
                <div class="stat-label">Tasks</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $user->notes_count }}</div>
                <div class="stat-label">Notes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $user->reminders_count }}</div>
                <div class="stat-label">Reminders</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid - Adjusted for larger calendar -->
    <div class="content-grid-large-calendar">
        <!-- Left Column - Calendar (70%) -->
        <div class="calendar-column">
            <!-- Weekly Schedule Calendar -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-week"></i> Weekly Schedule</h3>
                    <div class="card-actions">
                        <button class="btn-filter active" data-view="week">Week</button>
                        <button class="btn-filter" data-view="month">Month</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar-view-large">
                        @php
                            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $classesByDay = [];
                            
                            // Group classes by day
                            foreach ($user->classSchedules as $class) {
                                $classesByDay[$class->day][] = $class;
                            }
                        @endphp
                        
                        <div class="week-days-large">
                            @foreach($daysOfWeek as $day)
                            <div class="day-column-large">
                                <div class="day-header-large">{{ $day }}</div>
                                <div class="day-content-large">
                                    @if(isset($classesByDay[$day]) && count($classesByDay[$day]) > 0)
                                        @foreach($classesByDay[$day] as $class)
                                            <div class="calendar-class-item-large" style="background-color: {{ $class->color }}">
                                                <div class="class-time-large">{{ $class->time }}</div>
                                                <div class="class-name-large">{{ $class->name }}</div>
                                                @if($class->location)
                                                    <div class="class-location-large">{{ $class->location }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="no-classes-large">No classes scheduled</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Statistics -->
            @if($taskStats->isNotEmpty())
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Task Distribution</h3>
                </div>
                <div class="card-body">
                    <div class="stats-visual">
                        @foreach($taskStats as $stat)
                        <div class="stat-bar-item">
                            <div class="stat-label">
                                <span class="status-dot status-{{ strtolower($stat->status) }}"></span>
                                {{ ucfirst($stat->status) }}
                            </div>
                            <div class="stat-bar-container">
                                <div class="stat-bar" style="width: {{ ($stat->count / max(1, $user->tasks_count)) * 100 }}%">
                                    <span class="stat-count">{{ $stat->count }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Recent Activity & Quick Actions (30%) -->
        <div class="sidebar-column">
            <!-- Recent Activity - Compact -->
<div class="content-card compact">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Activity</h3>
    </div>
    <div class="card-body compact">
        <div class="activity-feed-compact">
            @php
                // Combine recent activities
                $recentActivities = collect();
                
                // Add tasks
                if($user->tasks->count() > 0) {
                    foreach($user->tasks->take(3) as $task) {
                        $recentActivities->push([
                            'type' => 'task',
                            'item' => $task,
                            'time' => $task->updated_at,
                            'icon' => 'tasks',
                            'color' => 'warning',
                            'title' => $task->title,
                            'action' => $task->status == 'completed' ? 'completed' : 'updated'
                        ]);
                    }
                }
                
                // Add notes
                if($user->notes->count() > 0) {
                    foreach($user->notes->take(2) as $note) {
                        $recentActivities->push([
                            'type' => 'note',
                            'item' => $note,
                            'time' => $note->updated_at,
                            'icon' => 'sticky-note',
                            'color' => 'success',
                            'title' => $note->title,
                            'action' => 'created'
                        ]);
                    }
                }
                
                // Add classes
                if($user->classSchedules->count() > 0) {
                    foreach($user->classSchedules->take(2) as $class) {
                        $recentActivities->push([
                            'type' => 'class',
                            'item' => $class,
                            'time' => $class->updated_at,
                            'icon' => 'calendar-alt',
                            'color' => 'info',
                            'title' => $class->name,
                            'action' => 'scheduled'
                        ]);
                    }
                }
                
                // Add reminders
                if($user->reminders->count() > 0) {
                    foreach($user->reminders->take(2) as $reminder) {
                        $recentActivities->push([
                            'type' => 'reminder',
                            'item' => $reminder,
                            'time' => $reminder->reminder_time,
                            'icon' => 'bell',
                            'color' => 'danger',
                            'title' => $reminder->title,
                            'action' => $reminder->reminder_time && $reminder->reminder_time->isPast() ? 'triggered' : 'set'
                        ]);
                    }
                }
                
                // Sort by time (most recent first)
                $recentActivities = $recentActivities->sortByDesc('time')->take(4);
            @endphp
            
            @if($recentActivities->count() > 0)
                @foreach($recentActivities as $activity)
                <div class="activity-item-compact" data-type="{{ $activity['type'] }}">
                    <div class="activity-icon-compact {{ $activity['type'] }}">
                        <i class="fas fa-{{ $activity['icon'] }}"></i>
                    </div>
                    <div class="activity-content-compact">
                        <div class="activity-header-compact">
                            <h4 class="activity-title-compact">
                                <i class="fas fa-{{ $activity['icon'] }} icon-small"></i>
                                {{ Str::limit($activity['title'], 20) }}
                            </h4>
                            <span class="activity-badge badge-{{ $activity['color'] }}">
                                {{ ucfirst($activity['action']) }}
                            </span>
                        </div>
                        <span class="activity-time-compact">
                            <i class="fas fa-clock icon-small"></i>
                            {{ $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->diffForHumans() : 'Recently' }}
                        </span>
                    </div>
                </div>
                @endforeach
            @else
            <div class="empty-state-compact">
                <i class="fas fa-inbox"></i>
                <p>No recent activity</p>
            </div>
            @endif
        </div>
    </div>
</div>

            <!-- Quick Actions - Compact -->
            <div class="content-card compact">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body compact">
                    <div class="quick-actions-compact">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="quick-action-compact">
                            <div class="action-icon-compact">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="action-content-compact">
                                <h4>Edit User</h4>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action-compact" data-action="email">
                            <div class="action-icon-compact">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="action-content-compact">
                                <h4>Send Email</h4>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action-compact danger" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this user?')) { document.getElementById('delete-form-{{ $user->id }}').submit(); }">
                            <div class="action-icon-compact">
                                <i class="fas fa-trash"></i>
                            </div>
                            <div class="action-content-compact">
                                <h4>Delete User</h4>
                            </div>
                        </a>
                        
                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Content Tabs -->
    <div class="content-tabs-section">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="tasks">
                <i class="fas fa-tasks"></i> Tasks ({{ $user->tasks_count }})
            </button>
            <button class="tab-btn" data-tab="notes">
                <i class="fas fa-sticky-note"></i> Notes ({{ $user->notes_count }})
            </button>
            <button class="tab-btn" data-tab="reminders">
                <i class="fas fa-bell"></i> Reminders ({{ $user->reminders_count }})
            </button>
        </div>
        
        <div class="tabs-content">
            <!-- Tasks Tab -->
            <div class="tab-pane active" id="tasks-tab">
                @if($user->tasks->count() > 0)
                <div class="items-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->tasks as $task)
                            <tr>
                                <td>
                                    <div class="item-title">
                                        <i class="fas fa-tasks"></i>
                                        {{ $task->title }}
                                    </div>
                                    @if($task->description)
                                    <div class="item-description">
                                        {{ Str::limit($task->description, 100) }}
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($task->status) }}">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge priority-{{ strtolower($task->priority) }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                                </td>
                                <td>
                                    {{ $task->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-tab">
                    <i class="fas fa-tasks"></i>
                    <p>No tasks found</p>
                </div>
                @endif
            </div>
            
            <!-- Notes Tab -->
            <div class="tab-pane" id="notes-tab">
                @if($user->notes->count() > 0)
                <div class="notes-grid">
                    @foreach($user->notes as $note)
                    <div class="note-card">
                        <div class="note-header">
                            <div class="note-icon">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="note-meta">
                                <span class="note-date">{{ $note->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="note-content">
                            <h4>{{ $note->title }}</h4>
                            <p class="note-text">{{ Str::limit($note->content, 150) }}</p>
                            @if($note->tags && is_array($note->tags) && count($note->tags) > 0)
                            <div class="note-tags">
                                @foreach($note->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-tab">
                    <i class="fas fa-sticky-note"></i>
                    <p>No notes found</p>
                </div>
                @endif
            </div>
            
            <!-- Reminders Tab -->
            <div class="tab-pane" id="reminders-tab">
                @if($user->reminders->count() > 0)
                <div class="reminders-list">
                    @foreach($user->reminders as $reminder)
                    <div class="reminder-item">
                        <div class="reminder-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="reminder-content">
                            <div class="reminder-header">
                                <h4>{{ $reminder->title }}</h4>
                                <span class="reminder-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $reminder->reminder_time ? \Carbon\Carbon::parse($reminder->reminder_time)->format('M d, Y h:i A') : 'No time set' }}
                                </span>
                            </div>
                            @if($reminder->is_task_reminder)
                            <div class="reminder-task">
                                <i class="fas fa-link"></i>
                                <span>Linked to task</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-tab">
                    <i class="fas fa-bell"></i>
                    <p>No reminders found</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .user-details-modern {
        padding: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    /* Header Bar with Back Button */
    .user-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f3f4f6;
        color: #4b5563;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #e5e7eb;
        color: #374151;
        transform: translateX(-2px);
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    /* Profile Header */
    .user-profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .user-profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .profile-avatar-section {
        position: relative;
        z-index: 1;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(45deg, #ffffff, #e6e6e6);
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: bold;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        border: 4px solid rgba(255, 255, 255, 0.3);
    }

    .profile-badge {
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .profile-info {
        flex: 1;
        z-index: 1;
    }

    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .profile-email {
        font-size: 16px;
        opacity: 0.9;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-meta {
        font-size: 14px;
        opacity: 0.8;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .meta-divider {
        opacity: 0.5;
    }

    .profile-stats {
        display: flex;
        gap: 30px;
        z-index: 1;
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 14px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-action {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .btn-action:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        color: white;
    }

    /* MAIN LAYOUT ADJUSTMENT - 70/30 split */
    .content-grid-large-calendar {
        display: grid;
        grid-template-columns: 70% 30%;
        gap: 25px;
        margin-bottom: 40px;
    }

    @media (max-width: 1200px) {
        .content-grid-large-calendar {
            grid-template-columns: 1fr;
        }
    }

    .content-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .content-card.compact {
        margin-bottom: 20px;
    }

    .content-card.compact:last-child {
        margin-bottom: 0;
    }

    .card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 20px;
    }

    .card-body.compact {
        padding: 15px;
    }

    /* LARGE CALENDAR VIEW */
    .calendar-view-large {
        width: 100%;
    }

    .week-days-large {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
        margin-top: 15px;
    }

    .day-column-large {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        min-height: 350px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .day-column-large:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .day-header-large {
        background: #4f46e5;
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .day-content-large {
        padding: 12px;
        height: calc(100% - 44px);
        overflow-y: auto;
    }

    .calendar-class-item-large {
        background: white;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        border-left: 4px solid;
        border-color: inherit;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .calendar-class-item-large:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .class-time-large {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
    }

    .class-name-large {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 3px;
        color: #1f2937;
    }

    .class-location-large {
        font-size: 12px;
        color: #6b7280;
        opacity: 0.8;
    }

    .no-classes-large {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
        font-size: 14px;
        font-style: italic;
    }

    /* COMPACT ACTIVITY FEED */
    .activity-feed-compact {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .activity-item-compact {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .activity-item-compact:hover {
        background: #f3f4f6;
    }

    .activity-icon-compact {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: white;
    }

    .activity-icon-compact.task { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .activity-icon-compact.note { background: linear-gradient(135deg, #10b981, #34d399); }
    .activity-icon-compact.class { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .activity-icon-compact.reminder { background: linear-gradient(135deg, #ef4444, #f87171); }

    .activity-content-compact {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .activity-title-compact {
        margin: 0 0 3px 0;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-time-compact {
        font-size: 11px;
        color: #9ca3af;
        display: block;
    }

    .empty-state-compact {
        text-align: center;
        padding: 30px 15px;
        color: #9ca3af;
    }

    .empty-state-compact i {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.3;
    }

    .empty-state-compact p {
        margin: 0;
        font-size: 14px;
        font-weight: 500;
    }

    /* COMPACT QUICK ACTIONS */
    .quick-actions-compact {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .quick-action-compact {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 8px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .quick-action-compact:hover {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    .quick-action-compact.danger:hover {
        border-color: #fecaca;
        background: #fef2f2;
    }

    .action-icon-compact {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 6px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .action-content-compact h4 {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
    }

    /* Stats Visual */
    .stats-visual {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .stat-bar-item {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-label {
        width: 120px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-pending { background: #f59e0b; }
    .status-in_progress { background: #3b82f6; }
    .status-completed { background: #10b981; }

    .stat-bar-container {
        flex: 1;
        height: 24px;
        background: #f3f4f6;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }

    .stat-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--bar-color), color-mix(in srgb, var(--bar-color) 80%, white));
        border-radius: 12px;
        transition: width 1s ease;
        position: relative;
    }

    .stat-count {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 12px;
        font-weight: 600;
    }

    /* Content Tabs */
    .content-tabs-section {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-top: 30px;
    }

    .tabs-header {
        display: flex;
        background: #f9fafb;
        padding: 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .tab-btn {
        flex: 1;
        padding: 18px;
        background: none;
        border: none;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .tab-btn:hover {
        background: #f3f4f6;
        color: #4f46e5;
    }

    .tab-btn.active {
        color: #4f46e5;
        background: white;
    }

    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: #4f46e5;
    }

    .tabs-content {
        padding: 20px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Tasks Table */
    .items-table {
        overflow-x: auto;
    }

    .items-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th {
        padding: 12px 16px;
        text-align: left;
        background: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e5e7eb;
    }

    .items-table td {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }

    .items-table tr:hover {
        background: #f9fafb;
    }

    .item-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .item-description {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .priority-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .priority-high { background: #fee2e2; color: #991b1b; }
    .priority-medium { background: #fef3c7; color: #92400e; }
    .priority-low { background: #d1fae5; color: #065f46; }

    /* Notes Grid */
    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .note-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .note-card:hover {
        border-color: #4f46e5;
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.1);
        transform: translateY(-4px);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .note-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f3f4f6;
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .note-meta {
        font-size: 12px;
        color: #9ca3af;
    }

    .note-content h4 {
        margin: 0 0 10px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .note-text {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 15px 0;
        line-height: 1.5;
    }

    .note-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .tag {
        padding: 4px 10px;
        background: #e5e7eb;
        color: #374151;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Reminders List */
    .reminders-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .reminder-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .reminder-item:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }

    .reminder-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .reminder-content {
        flex: 1;
    }

    .reminder-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .reminder-header h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    .reminder-time {
        font-size: 12px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .reminder-task {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    /* Empty States */
    .empty-state, .empty-tab {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }

    .empty-state i, .empty-tab i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .empty-state p, .empty-tab p {
        margin: 0;
        font-size: 16px;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-profile-header {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
            gap: 20px;
        }
        
        .profile-stats {
            width: 100%;
            justify-content: space-around;
            gap: 20px;
        }
        
        .week-days-large {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .day-column-large {
            min-height: 250px;
        }
        
        .tabs-header {
            flex-direction: column;
        }
        
        .tab-btn {
            padding: 15px;
            justify-content: flex-start;
        }
        
        .notes-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .week-days-large {
            grid-template-columns: 1fr;
        }
        
        .profile-stats {
            flex-direction: column;
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
    }
</style>

<script>
    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and panes
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding pane
            const tabId = button.getAttribute('data-tab');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    // Calendar view switching
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', () => {
            const view = button.getAttribute('data-view');
            document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            if (view === 'month') {
                alert('Month view not implemented yet');
            }
            // Week view is already shown
        });
    });

    // Email action
    document.querySelectorAll('[data-action="email"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const email = "{{ $user->email }}";
            window.location.href = `mailto:${email}`;
        });
    });

    // Class item click
    document.querySelectorAll('.calendar-class-item-large').forEach(item => {
        item.addEventListener('click', () => {
            const className = item.querySelector('.class-name-large').textContent;
            const classTime = item.querySelector('.class-time-large').textContent;
            const location = item.querySelector('.class-location-large')?.textContent || 'No location specified';
            
            alert(`Class: ${className}\nTime: ${classTime}\nLocation: ${location}`);
        });
    });
</script>