<div class="header">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-input" placeholder="Search...">
    </div>
    <div class="user-actions">
        <div class="user-profile">
            <div class="user-avatar" style="background-color: {{ Auth::user()->avatar_color }}">
                {{ substr(Auth::user()->name, 0, 2) }}
            </div>
            <span>{{ Auth::user()->name }}</span>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-small btn-danger" style="margin-left:1rem;">
                Logout
            </button>
        </form>
    </div>
</div>