<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlanSync Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <!-- Admin Panel -->
    <div id="admin-panel" class="visible">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="app-title">
                <i class="fas fa-shield-alt"></i>
                <span>PlanSync Admin</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
                <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="fas fa-users"></i> <span>Users</span></a></li>
                <li><a href="{{ route('admin.classes.index') }}" class="{{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"><i class="fas fa-book"></i> <span>Classes</span></a></li>
                <li><a href="{{ route('admin.tasks.index') }}" class="{{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                <li><a href="{{ route('admin.notes.index') }}" class="{{ request()->routeIs('admin.notes.*') ? 'active' : '' }}"><i class="fas fa-sticky-note"></i> <span>Notes</span></a></li>
                <li><a href="{{ route('admin.reminders.index') }}" class="{{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}"><i class="fas fa-bell"></i> <span>Reminders</span></a></li>
                <li><a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> <span>Analytics</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search...">
                </div>
                <div class="user-actions">
                    <div class="user-profile">
                        <div class="user-avatar">AD</div>
                        <span>{{ Auth::user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn" id="logout-btn">Logout</button>
                    </form>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content">
                @yield('content')
            </div>
        </div>

        <!-- Modals -->
        @include('admin.modals.user-modal')
        @include('admin.modals.class-modal')
        @include('admin.modals.task-modal')
        @include('admin.modals.note-modal')
        @include('admin.modals.reminder-modal')
    </div>

    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>