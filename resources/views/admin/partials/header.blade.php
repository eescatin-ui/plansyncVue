<div class="header">
    <button class="toggle-sidebar" id="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-input" placeholder="Search users, classes, tasks...">
    </div>
    
    <div class="user-actions">
        <div class="user-profile">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <span>{{ Auth::user()->name }}</span>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>