[file name]: index.blade.php
[file content begin]
@extends('layouts.admin')

@section('content')
<!-- Reminders Module -->
<div class="module active" id="reminders">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-bell"></i> Reminders</h2>
        <button class="btn" id="add-reminder-btn">
            <i class="fas fa-plus"></i> Add Reminder
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

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div style="padding: 1rem;">
            <form method="GET" action="{{ route('admin.reminders.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
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
                        <label for="filter-task">Task</label>
                        <select class="form-control" id="filter-task" name="task_id" onchange="this.form.submit()">
                            <option value="">All Tasks</option>
                            @if(isset($tasks) && count($tasks) > 0)
                                @foreach($tasks as $task)
                                    <option value="{{ $task->id }}" {{ request('task_id') == $task->id ? 'selected' : '' }}>
                                        {{ $task->title }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No tasks available</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-status">Status</label>
                        <select class="form-control" id="filter-status" name="status" onchange="this.form.submit()">
                            <option value="">All Reminders</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Past</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter-search">Search</label>
                        <div class="search-box" style="width: 100%;">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filter-search" name="search" placeholder="Search by title, description..." 
                                   value="{{ request('search') }}" onkeyup="if(event.key === 'Enter') this.form.submit()">
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="submit" class="btn btn-small"><i class="fas fa-search"></i> Apply Filters</button>
                    <a href="{{ route('admin.reminders.index') }}" class="btn btn-small btn-danger"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <i class="fas fa-bell fa-2x" style="color: var(--primary);"></i>
            <div class="stat-number">{{ $stats['totalReminders'] ?? 0 }}</div>
            <div class="stat-label">Total Reminders</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock fa-2x" style="color: var(--success);"></i>
            <div class="stat-number">{{ $stats['upcomingReminders'] ?? 0 }}</div>
            <div class="stat-label">Upcoming</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-history fa-2x" style="color: var(--warning);"></i>
            <div class="stat-number">{{ $stats['pastReminders'] ?? 0 }}</div>
            <div class="stat-label">Past</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-tasks fa-2x" style="color: var(--accent);"></i>
            <div class="stat-number">{{ $stats['remindersWithTasks'] ?? 0 }}</div>
            <div class="stat-label">With Tasks</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-day fa-2x" style="color: var(--danger);"></i>
            <div class="stat-number">{{ $stats['todaysReminders'] ?? 0 }}</div>
            <div class="stat-label">Today's</div>
        </div>
    </div>

    <!-- Upcoming Reminders (Next 24 Hours) -->
    @if(isset($next24Hours) && $next24Hours->count() > 0)
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Next 24 Hours</h3>
            <span class="badge" style="background-color: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 20px;">
                {{ $next24Hours->count() }} reminders
            </span>
        </div>
        <div class="activity-list">
            @foreach($next24Hours as $reminder)
            <div class="activity-item">
                <div class="user-avatar" style="background-color: var(--warning);">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <strong>{{ $reminder->title }}</strong>
                    <p>
                        <i class="fas fa-user"></i> {{ $reminder->user->name ?? 'Unknown' }} • 
                        <i class="fas fa-clock"></i> {{ $reminder->reminder_time->format('h:i A') }} • 
                        <span style="color: var(--success);">
                            <i class="fas fa-hourglass-end"></i> {{ $reminder->reminder_time->diffForHumans() }}
                        </span>
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Reminders Table -->
    <div class="table-container">
        <table id="remindersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Reminder Time</th>
                    <th>Status</th>
                    <th>User</th>
                    <th>Task</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reminders as $reminder)
                <tr>
                    <td>#{{ $reminder->id }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="background-color: #{{ substr(md5($reminder->title), 0, 6) }}; font-size: 0.8rem;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>{{ $reminder->title }}</span>
                        </div>
                    </td>
                    <td>
                        @if($reminder->description)
                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $reminder->description }}
                            </div>
                        @else
                            <span style="color: var(--gray); font-style: italic;">No description</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-calendar-alt" style="color: var(--gray);"></i>
                            {{ $reminder->reminder_time->format('M d, Y h:i A') }}
                        </div>
                        <small style="color: var(--gray);">
                            @if($reminder->reminder_time->isFuture())
                                <span style="color: var(--success);">
                                    <i class="fas fa-hourglass-end"></i> {{ $reminder->reminder_time->diffForHumans() }}
                                </span>
                            @else
                                <span style="color: var(--gray);">
                                    <i class="fas fa-history"></i> {{ $reminder->reminder_time->diffForHumans() }} ago
                                </span>
                            @endif
                        </small>
                    </td>
                    <td>
                        @if($reminder->reminder_time->isFuture())
                            <span class="status-badge" style="
                                padding: 0.25rem 0.75rem;
                                border-radius: 20px;
                                font-size: 0.875rem;
                                font-weight: 500;
                                background-color: var(--success);
                                color: white;
                            ">
                                <i class="fas fa-clock"></i> Upcoming
                            </span>
                        @else
                            <span class="status-badge" style="
                                padding: 0.25rem 0.75rem;
                                border-radius: 20px;
                                font-size: 0.875rem;
                                font-weight: 500;
                                background-color: var(--gray);
                                color: white;
                            ">
                                <i class="fas fa-history"></i> Past
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ substr($reminder->user->name ?? 'NA', 0, 2) }}
                            </div>
                            <span>{{ $reminder->user->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td>
                        @if($reminder->task)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem; background-color: var(--primary);">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <span>{{ $reminder->task->title }}</span>
                            </div>
                        @else
                            <span style="color: var(--gray); font-style: italic;">No task</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-small edit-reminder-btn" data-id="{{ $reminder->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-small btn-danger delete-reminder-btn" data-id="{{ $reminder->id }}" data-title="{{ $reminder->title }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>No reminders found</p>
                        @if(request()->hasAny(['user_id', 'task_id', 'status', 'search']))
                            <a href="{{ route('admin.reminders.index') }}" class="btn btn-small" style="margin-top: 10px;">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reminders->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="color: var(--gray); font-size: 0.875rem;">
            Showing {{ $reminders->firstItem() }} to {{ $reminders->lastItem() }} of {{ $reminders->total() }} reminders
        </div>
        <div style="display: flex; gap: 5px;">
            @if($reminders->onFirstPage())
                <span class="btn btn-small" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $reminders->previousPageUrl() }}" class="btn btn-small">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif
            
            @foreach($reminders->getUrlRange(max(1, $reminders->currentPage() - 2), min($reminders->lastPage(), $reminders->currentPage() + 2)) as $page => $url)
                @if($page == $reminders->currentPage())
                    <span class="btn btn-small" style="background-color: var(--primary); color: white;">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="btn btn-small">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
            
            @if($reminders->hasMorePages())
                <a href="{{ $reminders->nextPageUrl() }}" class="btn btn-small">
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

<!-- Add/Edit Reminder Modal -->
<div class="modal" id="reminder-modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="reminder-modal-title">Add New Reminder</h2>
            <button class="close-modal" data-modal="reminder-modal">×</button>
        </div>
        <form id="reminder-form" method="POST" action="{{ route('admin.reminders.store') }}">
            @csrf
            <input type="hidden" id="reminder-id" name="id">
            <input type="hidden" id="form-method" name="_method" value="POST">
            
            <div class="form-group">
                <label for="modal-reminder-user-id">User *</label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="modal-reminder-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    @foreach($allUsers as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-reminder-title">Title *</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="modal-reminder-title" name="title" 
                       placeholder="e.g., Meeting reminder, Task deadline" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-reminder-description">Description (Optional)</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="modal-reminder-description" 
                          name="description" rows="3" placeholder="Enter reminder description...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-reminder-time">Reminder Time *</label>
                <input type="datetime-local" class="form-control @error('reminder_time') is-invalid @enderror" id="modal-reminder-time" 
                       name="reminder_time" value="{{ old('reminder_time') }}" required>
                @error('reminder_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-reminder-task-id">Task (Optional)</label>
                <select class="form-control @error('task_id') is-invalid @enderror" id="modal-reminder-task-id" name="task_id">
                    <option value="">No Task</option>
                    @foreach($allTasks as $task)
                        <option value="{{ $task->id }}" {{ old('task_id') == $task->id ? 'selected' : '' }}>
                            {{ $task->title }} ({{ $task->user->name ?? 'Unknown' }})
                        </option>
                    @endforeach
                </select>
                @error('task_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn" id="modal-save-reminder">
                    <i class="fas fa-save"></i> <span id="modal-save-text">Save Reminder</span>
                </button>
                <button type="button" class="btn" data-modal="reminder-modal" style="margin-left: auto;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="delete-confirm-modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="close-modal" data-modal="delete-confirm-modal">×</button>
        </div>
        <div style="padding: 1rem;">
            <p id="delete-confirm-message">Are you sure you want to delete this reminder?</p>
            <form id="delete-form" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
            <button class="btn btn-small" data-modal="delete-confirm-modal">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-small btn-danger" id="confirm-delete-btn">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Hidden Container for Edit Data -->
<div style="display: none;">
    @foreach($reminders as $reminder)
    <div id="reminder-data-{{ $reminder->id }}">
        <input type="hidden" class="reminder-user-id" value="{{ $reminder->user_id }}">
        <input type="hidden" class="reminder-title" value="{{ $reminder->title }}">
        <input type="hidden" class="reminder-description" value="{{ $reminder->description }}">
        <input type="hidden" class="reminder-time" value="{{ $reminder->reminder_time->format('Y-m-d\TH:i') }}">
        <input type="hidden" class="reminder-task-id" value="{{ $reminder->task_id }}">
    </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.modal-content {
    max-height: 90vh;
    overflow-y: auto;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    border: 1px solid rgba(25, 135, 84, 0.2);
    color: #198754;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--light-gray);
    border-radius: 4px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
    color: var(--dark);
}

.is-invalid {
    border-color: var(--danger) !important;
}

.invalid-feedback {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

/* Search box */
.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
}

.search-box input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 35px;
    border: 1px solid var(--light-gray);
    border-radius: 4px;
}

/* Button styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.5rem 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.btn:hover {
    background: var(--primary-dark);
}

.btn-small {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

.btn-danger {
    background: var(--danger);
}

.btn-danger:hover {
    background: var(--danger-dark);
}

/* Stats cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    padding: 1.5rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.stat-label {
    color: var(--gray);
    font-size: 0.875rem;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid var(--light-gray);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray);
}

.close-modal:hover {
    color: var(--dark);
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--light-gray);
}

.activity-item:last-child {
    border-bottom: none;
}

.user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
// Helper functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = 'auto';
}

function resetReminderForm() {
    const form = document.getElementById('reminder-form');
    form.reset();
    form.action = '{{ route("admin.reminders.store") }}';
    document.getElementById('reminder-modal-title').textContent = 'Add New Reminder';
    document.getElementById('modal-save-text').textContent = 'Save Reminder';
    document.getElementById('reminder-id').value = '';
    document.getElementById('form-method').value = 'POST';
    
    // Set default values (tomorrow at 9 AM)
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(9, 0, 0, 0);
    
    // Format for datetime-local input
    const year = tomorrow.getFullYear();
    const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
    const day = String(tomorrow.getDate()).padStart(2, '0');
    const hours = String(tomorrow.getHours()).padStart(2, '0');
    const minutes = String(tomorrow.getMinutes()).padStart(2, '0');
    
    document.getElementById('modal-reminder-time').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('modal-reminder-task-id').value = '';
    
    // Clear validation classes
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.classList.remove('is-invalid');
    });
    const invalidFeedback = document.querySelectorAll('.invalid-feedback');
    invalidFeedback.forEach(feedback => feedback.style.display = 'none');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add Reminder Button
    document.getElementById('add-reminder-btn').addEventListener('click', function() {
        resetReminderForm();
        openModal('reminder-modal');
    });
    
    // Edit Reminder Buttons
    document.querySelectorAll('.edit-reminder-btn').forEach(button => {
        button.addEventListener('click', function() {
            const reminderId = this.getAttribute('data-id');
            editReminder(reminderId);
        });
    });
    
    // Delete Reminder Buttons
    document.querySelectorAll('.delete-reminder-btn').forEach(button => {
        button.addEventListener('click', function() {
            const reminderId = this.getAttribute('data-id');
            const reminderTitle = this.getAttribute('data-title');
            showDeleteConfirmation(reminderId, reminderTitle);
        });
    });
    
    // Close Modal Buttons
    document.querySelectorAll('.close-modal, [data-modal]').forEach(button => {
        if (button.tagName === 'BUTTON' && button.getAttribute('data-modal')) {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                closeModal(modalId);
            });
        }
    });
    
    // Confirm Delete Button
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        const form = document.getElementById('delete-form');
        const reminderId = form.getAttribute('data-reminder-id');
        deleteReminder(reminderId);
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});

// Edit Reminder Function
function editReminder(reminderId) {
    // Get reminder data from hidden container
    const reminderContainer = document.getElementById('reminder-data-' + reminderId);
    if (!reminderContainer) {
        alert('Reminder data not found');
        return;
    }
    
    // Populate form with reminder data
    document.getElementById('reminder-modal-title').textContent = 'Edit Reminder';
    document.getElementById('modal-save-text').textContent = 'Update Reminder';
    document.getElementById('reminder-id').value = reminderId;
    document.getElementById('form-method').value = 'PUT';
    
    // Update form action
    const form = document.getElementById('reminder-form');
    form.action = `/admin/reminders/${reminderId}`;
    
    // Set form values
    document.getElementById('modal-reminder-user-id').value = reminderContainer.querySelector('.reminder-user-id').value;
    document.getElementById('modal-reminder-title').value = reminderContainer.querySelector('.reminder-title').value;
    document.getElementById('modal-reminder-description').value = reminderContainer.querySelector('.reminder-description').value;
    
    // Format reminder time for datetime-local input
    const reminderTimeValue = reminderContainer.querySelector('.reminder-time').value;
    document.getElementById('modal-reminder-time').value = reminderTimeValue;
    
    document.getElementById('modal-reminder-task-id').value = reminderContainer.querySelector('.reminder-task-id').value || '';
    
    // Clear any validation errors
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.classList.remove('is-invalid');
    });
    const invalidFeedback = document.querySelectorAll('.invalid-feedback');
    invalidFeedback.forEach(feedback => feedback.style.display = 'none');
    
    openModal('reminder-modal');
}

// Show Delete Confirmation
function showDeleteConfirmation(reminderId, reminderTitle) {
    document.getElementById('delete-confirm-message').innerHTML = 
        `Are you sure you want to delete the reminder "<strong>${escapeHtml(reminderTitle)}</strong>"? This action cannot be undone.`;
    
    const form = document.getElementById('delete-form');
    form.action = `/admin/reminders/${reminderId}`;
    form.setAttribute('data-reminder-id', reminderId);
    
    openModal('delete-confirm-modal');
}

// Delete Reminder Function (Traditional form submission)
function deleteReminder(reminderId) {
    const form = document.getElementById('delete-form');
    
    // Show loading
    const deleteBtn = document.getElementById('confirm-delete-btn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    deleteBtn.disabled = true;
    
    // Submit the form
    form.submit();
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check if there are form errors and reopen modal
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    @if(old('id'))
        // If editing a reminder and there are errors, reopen edit modal
        setTimeout(() => {
            editReminder({{ old('id') }});
        }, 100);
    @else
        // If creating a reminder and there are errors, reopen create modal
        setTimeout(() => {
            resetReminderForm();
            
            // Restore old input values
            @if(old('user_id'))
                document.getElementById('modal-reminder-user-id').value = '{{ old("user_id") }}';
            @endif
            
            @if(old('title'))
                document.getElementById('modal-reminder-title').value = '{{ old("title") }}';
            @endif
            
            @if(old('description'))
                document.getElementById('modal-reminder-description').value = '{{ old("description") }}';
            @endif
            
            @if(old('reminder_time'))
                document.getElementById('modal-reminder-time').value = '{{ old("reminder_time") }}';
            @endif
            
            @if(old('task_id'))
                document.getElementById('modal-reminder-task-id').value = '{{ old("task_id") }}';
            @endif
            
            openModal('reminder-modal');
        }, 100);
    @endif
});
@endif
</script>
@endpush
[file content end]