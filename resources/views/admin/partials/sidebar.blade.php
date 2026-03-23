<div class="sidebar" id="admin-sidebar">
    <div class="app-title">
        <i class="fas fa-shield-alt"></i>
        <span>PlanSync Admin</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> <span>Users</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.classes.index') }}" class="{{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
                <i class="fas fa-book"></i> <span>Classes</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.tasks.index') }}" class="{{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> <span>Tasks</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.notes.index') }}" class="{{ request()->routeIs('admin.notes*') ? 'active' : '' }}">
                <i class="fas fa-sticky-note"></i> <span>Notes</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reminders.index') }}" class="{{ request()->routeIs('admin.reminders*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> <span>Reminders</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> <span>Analytics</span>
            </a>
        </li>
    </ul>
</div>