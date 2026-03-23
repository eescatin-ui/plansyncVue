<div class="sidebar">
    <div class="app-title">
        <i class="fas fa-calendar-alt"></i>
        <span>PlanSync</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('schedule.index') }}" class="{{ request()->routeIs('schedule*') ? 'active' : '' }}">
                <i class="fas fa-calendar-week"></i> <span>Class Schedule</span>
            </a>
        </li>
        <li>
            <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> <span>Homework & Tasks</span>
            </a>
        </li>
        <li>
            <a href="{{ route('notes.index') }}" class="{{ request()->routeIs('notes*') ? 'active' : '' }}">
                <i class="fas fa-sticky-note"></i> <span>Notes</span>
            </a>
        </li>
        <li>
            <a href="{{ route('reminders.index') }}" class="{{ request()->routeIs('reminders*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> <span>Reminders</span>
            </a>
        </li>
        <li>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> <span>Settings</span>
            </a>
        </li>
    </ul>
</div>