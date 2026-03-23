@extends('layouts.admin')

@section('content')
<!-- Classes Module -->
<div class="module active" id="classes">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-book"></i> Classes</h2>
        <button class="btn" id="add-class-btn" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Add Class
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
            <ul style="margin-top: 10px; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div style="padding: 1rem;">
            <form id="filter-form" method="GET" action="{{ route('admin.classes.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="filter-day">Day</label>
                        <select class="form-control" id="filter-day" name="day" onchange="this.form.submit()">
                            <option value="">All Days</option>
                            <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>Monday</option>
                            <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                            <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                            <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                            <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>Friday</option>
                            <option value="Saturday" {{ request('day') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                            <option value="Sunday" {{ request('day') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-user">User</label>
                        <select class="form-control" id="filter-user" name="user_id" onchange="this.form.submit()">
                            <option value="">All Users</option>
                            @if(isset($users) && count($users) > 0)
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No users available</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-search">Search</label>
                        <div class="search-box" style="width: 100%;">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filter-search" name="search" placeholder="Search by name or location..." 
                                   value="{{ request('search') }}" onkeyup="if(event.key === 'Enter') this.form.submit()">
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="submit" class="btn btn-small"><i class="fas fa-search"></i> Apply Filters</button>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-small btn-danger"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <i class="fas fa-calendar-alt fa-2x" style="color: var(--primary);"></i>
            <div class="stat-number">{{ $todayClasses }}</div>
            <div class="stat-label">Today's Classes</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users fa-2x" style="color: var(--accent);"></i>
            <div class="stat-number">{{ $uniqueUsers }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock fa-2x" style="color: var(--success);"></i>
            <div class="stat-number">{{ $upcomingClasses }}</div>
            <div class="stat-label">Upcoming (Next 7 days)</div>
        </div>
    </div>

    <!-- Classes Table -->
    <table id="classesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Time</th>
                <th>Location</th>
                <th>Day</th>
                <th>User</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classes as $class)
            <tr>
                <td>#{{ $class->id }}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar" style="background-color: #{{ substr(md5($class->name), 0, 6) }}; font-size: 0.8rem;">
                            {{ substr($class->name, 0, 2) }}
                        </div>
                        <span>{{ $class->name }}</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-clock" style="color: var(--gray);"></i>
                        {{ $class->time }}
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--danger);"></i>
                        {{ $class->location }}
                    </div>
                </td>
                <td>
                    @php
                        $dayColors = [
                            'Monday' => '#4361ee',
                            'Tuesday' => '#3a0ca3', 
                            'Wednesday' => '#7209b7',
                            'Thursday' => '#4cc9f0',
                            'Friday' => '#f72585',
                            'Saturday' => '#e63946',
                            'Sunday' => '#2a9d8f'
                        ];
                        $color = $dayColors[$class->day] ?? '#6c757d';
                    @endphp
                    <span class="day-badge" style="
                        padding: 0.25rem 0.75rem;
                        border-radius: 20px;
                        font-size: 0.875rem;
                        font-weight: 500;
                        background-color: {{ $color }};
                        color: white;
                    ">
                        {{ $class->day }}
                    </span>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem;">
                            {{ substr($class->user->name, 0, 2) }}
                        </div>
                        <span>{{ $class->user->name }}</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn btn-small" onclick="viewClassModal({{ $class->id }})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-small" onclick="openEditModal({{ $class->id }})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this class?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>No classes found</p>
                    @if(request()->hasAny(['day', 'user_id', 'search']))
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-small" style="margin-top: 10px;">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    @if($classes->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="color: var(--gray); font-size: 0.875rem;">
            Showing {{ $classes->firstItem() }} to {{ $classes->lastItem() }} of {{ $classes->total() }} classes
        </div>
        <div style="display: flex; gap: 5px;">
            @if($classes->onFirstPage())
                <span class="btn btn-small" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $classes->previousPageUrl() }}" class="btn btn-small">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif
            
            @foreach($classes->getUrlRange(max(1, $classes->currentPage() - 2), min($classes->lastPage(), $classes->currentPage() + 2)) as $page => $url)
                @if($page == $classes->currentPage())
                    <span class="btn btn-small" style="background-color: var(--primary); color: white;">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="btn btn-small">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
            
            @if($classes->hasMorePages())
                <a href="{{ $classes->nextPageUrl() }}" class="btn btn-small">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="btn btn-small" style="opacity: 0.5; cursor: not-allowed;">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Create Class Modal -->
<div class="modal" id="create-class-modal">
    <div class="modal-overlay" onclick="closeModal('create-class-modal')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Class</h2>
            <button class="close-modal" onclick="closeModal('create-class-modal')">×</button>
        </div>
        <form method="POST" action="{{ route('admin.classes.store') }}">
            @csrf
            <div class="form-group">
                <label for="create-user-id">User *</label>
                <select class="form-control" id="create-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    @foreach($allUsers as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="create-class-name">Class Name *</label>
                <input type="text" class="form-control" id="create-class-name" name="name" 
                       value="{{ old('name') }}" placeholder="e.g., Math 101, Physics 201" required>
            </div>
            
            <div class="form-group">
                <label for="create-class-time">Time *</label>
                <input type="text" class="form-control" id="create-class-time" name="time" 
                       value="{{ old('time') }}" placeholder="e.g., 09:00 AM - 10:30 AM, 2:00 PM" required>
                <small class="text-muted">Enter time or time range (e.g., "9:00 AM - 10:30 AM")</small>
            </div>
            
            <div class="form-group">
                <label for="create-class-location">Location *</label>
                <input type="text" class="form-control" id="create-class-location" name="location" 
                       value="{{ old('location') }}" placeholder="e.g., Room 101, Building A" required>
            </div>
            
            <div class="form-group">
                <label for="create-class-day">Day *</label>
                <select class="form-control" id="create-class-day" name="day" required>
                    <option value="Monday" {{ old('day') == 'Monday' ? 'selected' : '' }}>Monday</option>
                    <option value="Tuesday" {{ old('day') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="Wednesday" {{ old('day') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="Thursday" {{ old('day') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="Friday" {{ old('day') == 'Friday' ? 'selected' : '' }}>Friday</option>
                    <option value="Saturday" {{ old('day') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                    <option value="Sunday" {{ old('day') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Save Class
                </button>
                <button type="button" class="btn" onclick="closeModal('create-class-modal')" style="margin-left: auto;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal" id="edit-class-modal">
    <div class="modal-overlay" onclick="closeModal('edit-class-modal')"></div>
    <div class="modal-content">
        <!-- Content will be loaded dynamically -->
    </div>
</div>

<!-- View Class Modal -->
<div class="modal" id="view-class-modal">
    <div class="modal-overlay" onclick="closeModal('view-class-modal')"></div>
    <div class="modal-content">
        <!-- Content will be loaded dynamically -->
    </div>
</div>

<!-- Hidden Edit Forms for each class -->
@foreach($classes as $class)
<div class="edit-form-template" id="edit-form-{{ $class->id }}" style="display: none;">
    <div class="modal-header">
        <h2>Edit Class</h2>
        <button class="close-modal" onclick="closeModal('edit-class-modal')">×</button>
    </div>
    <form method="POST" action="{{ route('admin.classes.update', $class->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="edit-user-id-{{ $class->id }}">User *</label>
            <select class="form-control" id="edit-user-id-{{ $class->id }}" name="user_id" required>
                <option value="">Select User</option>
                @foreach($allUsers as $user)
                    <option value="{{ $user->id }}" {{ $class->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label for="edit-class-name-{{ $class->id }}">Class Name *</label>
            <input type="text" class="form-control" id="edit-class-name-{{ $class->id }}" name="name" 
                   value="{{ $class->name }}" placeholder="e.g., Math 101, Physics 201" required>
        </div>
        
        <div class="form-group">
            <label for="edit-class-time-{{ $class->id }}">Time *</label>
            <input type="text" class="form-control" id="edit-class-time-{{ $class->id }}" name="time" 
                   value="{{ $class->time }}" placeholder="e.g., 09:00 AM - 10:30 AM, 2:00 PM" required>
            <small class="text-muted">Enter time or time range (e.g., "9:00 AM - 10:30 AM")</small>
        </div>
        
        <div class="form-group">
            <label for="edit-class-location-{{ $class->id }}">Location *</label>
            <input type="text" class="form-control" id="edit-class-location-{{ $class->id }}" name="location" 
                   value="{{ $class->location }}" placeholder="e.g., Room 101, Building A" required>
        </div>
        
        <div class="form-group">
            <label for="edit-class-day-{{ $class->id }}">Day *</label>
            <select class="form-control" id="edit-class-day-{{ $class->id }}" name="day" required>
                <option value="Monday" {{ $class->day == 'Monday' ? 'selected' : '' }}>Monday</option>
                <option value="Tuesday" {{ $class->day == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                <option value="Wednesday" {{ $class->day == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                <option value="Thursday" {{ $class->day == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                <option value="Friday" {{ $class->day == 'Friday' ? 'selected' : '' }}>Friday</option>
                <option value="Saturday" {{ $class->day == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                <option value="Sunday" {{ $class->day == 'Sunday' ? 'selected' : '' }}>Sunday</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Update Class
            </button>
            <button type="button" class="btn btn-danger" onclick="deleteClass({{ $class->id }}, '{{ addslashes($class->name) }}')">
                <i class="fas fa-trash"></i> Delete
            </button>
            <button type="button" class="btn" onclick="closeModal('edit-class-modal')" style="margin-left: auto;">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </form>
</div>

<!-- Hidden View Content for each class -->
<div class="view-content-template" id="view-content-{{ $class->id }}" style="display: none;">
    <div class="modal-header">
        <h2>Class Details</h2>
        <button class="close-modal" onclick="closeModal('view-class-modal')">×</button>
    </div>
    <div style="padding: 1rem;">
        @php
            $today = now()->format('l');
            $status = $class->day == $today ? 'Today' : 'Upcoming';
            $statusClass = $class->day == $today ? 'status-today' : 'status-upcoming';
        @endphp
        
        <div class="view-class-detail">
            <div class="view-class-label">
                <i class="fas fa-book"></i> Class Information
                <span class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                    {{ $status }}
                </span>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Name</div>
                    <div class="view-class-value">{{ $class->name }}</div>
                </div>
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Day</div>
                    <div class="view-class-value">
                        <span class="day-badge" style="
                            padding: 0.25rem 0.75rem;
                            border-radius: 20px;
                            font-size: 0.875rem;
                            font-weight: 500;
                            background-color: {{ $dayColors[$class->day] ?? '#6c757d' }};
                            color: white;
                        ">
                            {{ $class->day }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="view-class-detail">
            <div class="view-class-label">
                <i class="fas fa-clock"></i> Time & Location
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Time</div>
                    <div class="view-class-value">{{ $class->time }}</div>
                </div>
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Location</div>
                    <div class="view-class-value">{{ $class->location }}</div>
                </div>
            </div>
        </div>
        
        <div class="view-class-detail">
            <div class="view-class-label">
                <i class="fas fa-user"></i> User Information
            </div>
            <div class="user-info-card">
                <div class="user-avatar">{{ substr($class->user->name, 0, 2) }}</div>
                <div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $class->user->name }}</div>
                    <div style="color: var(--gray);">{{ $class->user->email }}</div>
                </div>
            </div>
        </div>
        
        <div class="view-class-detail">
            <div class="view-class-label">
                <i class="fas fa-calendar"></i> Meta Information
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Created At</div>
                    <div class="view-class-value">{{ $class->created_at->format('F j, Y, g:i A') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.875rem; color: var(--gray);">Last Updated</div>
                    <div class="view-class-value">{{ $class->updated_at->format('F j, Y, g:i A') }}</div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
            <button class="btn btn-small" onclick="closeModal('view-class-modal')">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 2;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.close-modal {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: var(--gray);
    padding: 0;
    line-height: 1;
}

.close-modal:hover {
    color: var(--dark);
}

/* Form Styles inside Modal */
.modal-content .form-group {
    padding: 0 1.5rem;
    margin-bottom: 1.5rem;
}

.modal-content .form-group:first-of-type {
    margin-top: 1.5rem;
}

.modal-content .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark);
}

.modal-content .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--light-gray);
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.modal-content .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modal-content .form-control[required] {
    border-left: 3px solid var(--primary);
}

.modal-content .text-muted {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray);
}

/* Button Styles inside Modal */
.modal-content .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-content .btn:not(.btn-danger) {
    background-color: var(--primary);
    color: white;
}

.modal-content .btn:not(.btn-danger):hover {
    background-color: #2954e6;
}

.modal-content .btn-danger {
    background-color: var(--danger);
    color: white;
}

.modal-content .btn-danger:hover {
    background-color: #d63031;
}

.modal-content .btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* View Content Styles */
.view-class-detail {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
}

.view-class-detail:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.view-class-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.1rem;
}

.view-class-value {
    color: var(--dark);
    font-size: 1rem;
    line-height: 1.5;
}

.user-info-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: 8px;
    margin: 1rem 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-left: 10px;
}

.status-today {
    background-color: rgba(67, 97, 238, 0.1);
    color: #4361ee;
    border: 1px solid rgba(67, 97, 238, 0.2);
}

.status-upcoming {
    background-color: rgba(247, 37, 133, 0.1);
    color: #f72585;
    border: 1px solid rgba(247, 37, 133, 0.2);
}

.day-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert i {
    font-size: 1.2rem;
}
</style>
@endpush

@push('scripts')
<script>
// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Open Create Modal
function openCreateModal() {
    openModal('create-class-modal');
}

// Open Edit Modal
function openEditModal(classId) {
    // Get the edit form template for this class
    const editForm = document.getElementById(`edit-form-${classId}`);
    
    if (editForm) {
        // Get modal content container
        const modalContent = document.querySelector('#edit-class-modal .modal-content');
        
        // Set the content
        modalContent.innerHTML = editForm.innerHTML;
        
        // Open modal
        openModal('edit-class-modal');
    } else {
        alert('Class data not found');
    }
}

// View Class Modal
function viewClassModal(classId) {
    // Get the view content template for this class
    const viewContent = document.getElementById(`view-content-${classId}`);
    
    if (viewContent) {
        // Get modal content container
        const modalContent = document.querySelector('#view-class-modal .modal-content');
        
        // Set the content
        modalContent.innerHTML = viewContent.innerHTML;
        
        // Open modal
        openModal('view-class-modal');
    } else {
        alert('Class details not found');
    }
}

// Delete Class function
function deleteClass(classId, className) {
    if (confirm(`Are you sure you want to delete the class "${className}"? This action cannot be undone.`)) {
        // Create delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/classes/${classId}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfTokenInput = document.createElement('input');
        csrfTokenInput.type = 'hidden';
        csrfTokenInput.name = '_token';
        csrfTokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfTokenInput);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside (on overlay)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            closeModal(modal.id);
        }
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close all open modals
        const openModals = document.querySelectorAll('.modal.active');
        openModals.forEach(modal => {
            closeModal(modal.id);
        });
    }
});
</script>
@endpush