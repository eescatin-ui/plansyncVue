@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="users">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-users"></i> Users</h2>
        <a href="{{ route('admin.users.create') }}" class="btn" id="add-user-btn">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
    
    <div class="search-box" style="margin-bottom: 2rem; max-width: 400px;">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" 
                       placeholder="Search users..." class="form-control">
                <button type="submit" class="btn btn-small">Search</button>
                @if(request('q'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-small btn-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table id="usersTable" class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Classes</th>
                    <th>Tasks</th>
                    <th>Notes</th>
                    <th>Reminders</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>#{{ $user->id }}</td>
                    <td>
                        <div class="user-avatar-small">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        {{ $user->name }}
                    </td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ $user->class_schedules_count }}</span></td>
                    <td><span class="badge bg-warning">{{ $user->tasks_count }}</span></td>
                    <td><span class="badge bg-success">{{ $user->notes_count }}</span></td>
                    <td><span class="badge bg-danger">{{ $user->reminders_count }}</span></td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.users.show', $user->id) }}" 
                               class="btn btn-small btn-view" 
                               data-bs-toggle="modal" 
                               data-bs-target="#userDetailsModal"
                               onclick="loadUserDetails({{ $user->id }})">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" 
                               class="btn btn-small btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-small btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No users found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">
                    <i class="fas fa-user"></i> User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading user details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .users {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .module-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .module-title {
        margin: 0;
        font-size: 1.5rem;
        color: #2c3e50;
    }
    
    .module-title i {
        color: #4361ee;
        margin-right: 10px;
    }
    
    #usersTable {
        width: 100%;
        border-collapse: collapse;
    }
    
    #usersTable th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    #usersTable td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    #usersTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .user-avatar-small {
        display: inline-block;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #4361ee;
        color: white;
        text-align: center;
        line-height: 32px;
        font-weight: bold;
        margin-right: 10px;
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .bg-info { background-color: #17a2b8 !important; }
    .bg-warning { background-color: #ffc107 !important; color: #212529; }
    .bg-success { background-color: #28a745 !important; }
    .bg-danger { background-color: #dc3545 !important; }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    
    .btn-small {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
    
    .btn-view {
        background-color: #17a2b8;
        color: white;
    }
    
    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-primary {
        background-color: #4361ee;
        color: white;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .search-input-group {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        gap: 8px;
    }
    
    .search-input-group input {
        border: none;
        outline: none;
        flex: 1;
        background: transparent;
    }
    
    .search-input-group i {
        color: #6c757d;
    }
    
    .empty-state {
        text-align: center;
        padding: 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 10px;
        opacity: 0.3;
    }
    
    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
</style>

<script>
function loadUserDetails(userId) {
    fetch(`/admin/users/${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('userDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading user details. Please try again.
                </div>
            `;
        });
}

// Show success/error messages
@if(session('success'))
    showAlert('success', '{{ session('success') }}');
@endif

@if($errors->any())
    showAlert('error', '{{ $errors->first() }}');
@endif

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.users').insertBefore(alertDiv, document.querySelector('.module-header'));
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endsection