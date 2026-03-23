@extends('layouts.admin')

@section('content')
<!-- Tasks Module -->
<div class="module active" id="tasks">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-tasks"></i> Tasks</h2>
        <button class="btn" id="add-task-btn">
            <i class="fas fa-plus"></i> Add Task
        </button>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div style="padding: 1rem;">
            <form id="filter-form" method="GET" action="{{ route('admin.tasks.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="filter-status">Status</label>
                        <select class="form-control" id="filter-status" name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                            <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
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
                            <input type="text" id="filter-search" name="search" placeholder="Search by title or description..." 
                                   value="{{ request('search') }}" onkeyup="if(event.key === 'Enter') this.form.submit()">
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="submit" class="btn btn-small"><i class="fas fa-search"></i> Apply Filters</button>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-small btn-danger"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card" onclick="window.location='{{ route('admin.tasks.index', ['status' => 'todo']) }}'">
            <i class="fas fa-clock fa-2x" style="color: #ffc107;"></i> <!-- Yellow -->
            <div class="stat-number">{{ $pendingTasks }}</div>
            <div class="stat-label">Pending Tasks</div>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.tasks.index', ['status' => 'inprogress']) }}'">
            <i class="fas fa-spinner fa-2x" style="color: #fd7e14;"></i> <!-- Orange -->
            <div class="stat-number">{{ $inProgressTasks }}</div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.tasks.index', ['status' => 'done']) }}'">
            <i class="fas fa-check-circle fa-2x" style="color: #198754;"></i> <!-- Green -->
            <div class="stat-number">{{ $completedTasks }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card" onclick="window.location='{{ route('admin.tasks.index') }}'">
            <i class="fas fa-tasks fa-2x" style="color: var(--accent);"></i>
            <div class="stat-number">{{ $stats['totalTasks'] ?? 0 }}</div>
            <div class="stat-label">Total Tasks</div>
        </div>
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

    <!-- Tasks Table -->
    <div class="table-container">
        <table id="tasksTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>User</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr>
                    <td>#{{ $task->id }}</td>
                    <td>
                        <span>{{ $task->title }}</span>
                    </td>
                    <td>
                        @if($task->description)
                            <div style="max-width: 200px;">
                                <div class="description-truncate" title="{{ $task->description }}">
                                    {{ Str::limit($task->description, 50) }}
                                </div>
                                @if(strlen($task->description) > 50)
                                    <a href="#" class="text-primary view-more-btn" data-description="{{ $task->description }}" style="font-size: 0.8rem;">View more</a>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--gray); font-style: italic;">No description</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-calendar-alt" style="color: var(--gray);"></i>
                            {{ $task->due_date->format('M d, Y h:i A') }}
                            @if($task->due_date->isPast() && $task->status != 'done')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-left: 5px;">
                                    <i class="fas fa-exclamation-triangle"></i> Overdue
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @php
                            // Define status colors
                            $statusColors = [
                                'todo' => '#ffc107', // Yellow
                                'inprogress' => '#fd7e14', // Orange
                                'done' => '#198754' // Green
                            ];
                            $statusColor = $statusColors[$task->status] ?? 'var(--gray)';
                        @endphp
                        <span class="status-badge" style="
                            padding: 0.25rem 0.75rem;
                            border-radius: 20px;
                            font-size: 0.875rem;
                            font-weight: 500;
                            background-color: {{ $statusColor }};
                            color: {{ $task->status == 'todo' ? '#000' : 'white' }};
                        ">
                            @if($task->status == 'todo')
                                <i class="fas fa-clock"></i> To Do
                            @elseif($task->status == 'inprogress')
                                <i class="fas fa-spinner"></i> In Progress
                            @else
                                <i class="fas fa-check-circle"></i> Done
                            @endif
                        </span>
                    </td>
                    <td>
                        @php
                            // Define priority colors
                            $priorityColors = [
                                'low' => '#0d6efd',    // Blue
                                'medium' => '#ffc107', // Yellow
                                'high' => '#dc3545'    // Red
                            ];
                            $priorityColor = $priorityColors[$task->priority] ?? 'var(--gray)';
                        @endphp
                        @if($task->priority)
                            <span style="color: {{ $priorityColor }}; font-weight: 500;">
                                <i class="fas fa-flag"></i> {{ ucfirst($task->priority) }}
                            </span>
                        @else
                            <span style="color: var(--gray);">Not set</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ substr($task->user->name ?? 'NA', 0, 2) }}
                            </div>
                            <span>{{ $task->user->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-small edit-task-btn" data-id="{{ $task->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-small btn-danger delete-task-btn" data-id="{{ $task->id }}" data-title="{{ $task->title }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-tasks"></i>
                        <p>No tasks found</p>
                        @if(request()->hasAny(['status', 'user_id', 'search']))
                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-small" style="margin-top: 10px;">
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
    @if($tasks->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="color: var(--gray); font-size: 0.875rem;">
            Showing {{ $tasks->firstItem() }} to {{ $tasks->lastItem() }} of {{ $tasks->total() }} tasks
        </div>
        <div style="display: flex; gap: 5px;">
            @if($tasks->onFirstPage())
                <span class="btn btn-small" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $tasks->previousPageUrl() }}" class="btn btn-small">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif
            
            @foreach($tasks->getUrlRange(max(1, $tasks->currentPage() - 2), min($tasks->lastPage(), $tasks->currentPage() + 2)) as $page => $url)
                @if($page == $tasks->currentPage())
                    <span class="btn btn-small" style="background-color: var(--primary); color: white;">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="btn btn-small">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
            
            @if($tasks->hasMorePages())
                <a href="{{ $tasks->nextPageUrl() }}" class="btn btn-small">
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

<!-- View Full Description Modal -->
<div class="modal" id="description-modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Description</h2>
            <button class="close-modal" data-modal="description-modal">×</button>
        </div>
        <div id="full-description-content" style="padding: 1rem; white-space: pre-wrap; word-wrap: break-word;"></div>
        <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
            <button class="btn btn-small" data-modal="description-modal">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Task Modal -->
<div class="modal" id="task-modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="task-modal-title">Add New Task</h2>
            <button class="close-modal" data-modal="task-modal">×</button>
        </div>
        <form id="task-form" method="POST" action="{{ route('admin.tasks.store') }}">
            @csrf
            <input type="hidden" id="task-id" name="id">
            <input type="hidden" id="form-method" name="_method" value="POST">
            
            <div class="form-group">
                <label for="modal-task-user-id">User *</label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="modal-task-user-id" name="user_id" required>
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
                <label for="modal-task-title">Title *</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="modal-task-title" name="title" placeholder="e.g., Complete project report" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-task-description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="modal-task-description" name="description" rows="3" placeholder="Enter task description...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-task-due-date">Due Date *</label>
                <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" id="modal-task-due-date" name="due_date" value="{{ old('due_date') }}" required>
                @error('due_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-task-status">Status *</label>
                <select class="form-control @error('status') is-invalid @enderror" id="modal-task-status" name="status" required>
                    <option value="todo" {{ old('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="inprogress" {{ old('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                    <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Done</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-task-priority">Priority</label>
                <select class="form-control @error('priority') is-invalid @enderror" id="modal-task-priority" name="priority">
                    <option value="">Select Priority</option>
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn" id="modal-save-task">
                    <i class="fas fa-save"></i> <span id="modal-save-text">Save Task</span>
                </button>
                <button type="button" class="btn" data-modal="task-modal" style="margin-left: auto;">
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
            <p id="delete-confirm-message">Are you sure you want to delete this task?</p>
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

<!-- Edit Form Container (Hidden) -->
<div style="display: none;">
    @foreach($tasks as $task)
    <div id="task-data-{{ $task->id }}">
        <input type="hidden" class="task-user-id" value="{{ $task->user_id }}">
        <input type="hidden" class="task-title" value="{{ $task->title }}">
        <input type="hidden" class="task-description" value="{{ $task->description }}">
        <input type="hidden" class="task-due-date" value="{{ $task->due_date->format('Y-m-d\TH:i') }}">
        <input type="hidden" class="task-status" value="{{ $task->status }}">
        <input type="hidden" class="task-priority" value="{{ $task->priority }}">
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

.error-message {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: none;
}

.overdue {
    color: var(--danger) !important;
    font-weight: bold;
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

.description-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.view-more-btn {
    font-size: 0.8rem;
    cursor: pointer;
    color: var(--primary) !important;
}

.view-more-btn:hover {
    text-decoration: underline;
}

.user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    font-weight: bold;
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

function resetTaskForm() {
    const form = document.getElementById('task-form');
    form.reset();
    form.action = '{{ route("admin.tasks.store") }}';
    document.getElementById('task-modal-title').textContent = 'Add New Task';
    document.getElementById('modal-save-text').textContent = 'Save Task';
    document.getElementById('task-id').value = '';
    document.getElementById('form-method').value = 'POST';
    
    // Set default values
    const now = new Date();
    now.setHours(now.getHours() + 24); // Default to tomorrow
    const localDateTime = now.toISOString().slice(0, 16);
    document.getElementById('modal-task-due-date').value = localDateTime;
    document.getElementById('modal-task-status').value = 'todo';
    document.getElementById('modal-task-priority').value = '';
    
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
    // Add Task Button
    document.getElementById('add-task-btn').addEventListener('click', function() {
        resetTaskForm();
        openModal('task-modal');
    });
    
    // View "View more" buttons for descriptions
    document.querySelectorAll('.view-more-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const description = this.getAttribute('data-description');
            document.getElementById('full-description-content').textContent = description;
            openModal('description-modal');
        });
    });
    
    // Edit Task Buttons
    document.querySelectorAll('.edit-task-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            editTask(taskId);
        });
    });
    
    // Delete Task Buttons
    document.querySelectorAll('.delete-task-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            const taskTitle = this.getAttribute('data-title');
            showDeleteConfirmation(taskId, taskTitle);
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
        const taskId = form.getAttribute('data-task-id');
        deleteTask(taskId);
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

// Edit Task Function
function editTask(taskId) {
    // Get task data from hidden container
    const taskContainer = document.getElementById('task-data-' + taskId);
    if (!taskContainer) {
        alert('Task data not found');
        return;
    }
    
    // Populate form with task data
    document.getElementById('task-modal-title').textContent = 'Edit Task';
    document.getElementById('modal-save-text').textContent = 'Update Task';
    document.getElementById('task-id').value = taskId;
    document.getElementById('form-method').value = 'PUT';
    
    // Update form action
    const form = document.getElementById('task-form');
    form.action = `/admin/tasks/${taskId}`;
    
    // Set form values
    document.getElementById('modal-task-user-id').value = taskContainer.querySelector('.task-user-id').value;
    document.getElementById('modal-task-title').value = taskContainer.querySelector('.task-title').value;
    document.getElementById('modal-task-description').value = taskContainer.querySelector('.task-description').value;
    
    // Format due date for datetime-local input
    const dueDateValue = taskContainer.querySelector('.task-due-date').value;
    document.getElementById('modal-task-due-date').value = dueDateValue;
    
    document.getElementById('modal-task-status').value = taskContainer.querySelector('.task-status').value;
    document.getElementById('modal-task-priority').value = taskContainer.querySelector('.task-priority').value;
    
    // Clear any validation errors
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.classList.remove('is-invalid');
    });
    const invalidFeedback = document.querySelectorAll('.invalid-feedback');
    invalidFeedback.forEach(feedback => feedback.style.display = 'none');
    
    openModal('task-modal');
}

// Show Delete Confirmation
function showDeleteConfirmation(taskId, taskTitle) {
    document.getElementById('delete-confirm-message').innerHTML = 
        `Are you sure you want to delete the task "<strong>${escapeHtml(taskTitle)}</strong>"? This action cannot be undone.`;
    
    const form = document.getElementById('delete-form');
    form.action = `/admin/tasks/${taskId}`;
    form.setAttribute('data-task-id', taskId);
    
    openModal('delete-confirm-modal');
}

// Delete Task Function (Traditional form submission)
function deleteTask(taskId) {
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
    @if(isset($task) && $task->id)
        // If editing a task and there are errors, reopen edit modal
        setTimeout(() => {
            editTask({{ $task->id ?? 'null' }});
        }, 100);
    @else
        // If creating a task and there are errors, reopen create modal
        setTimeout(() => {
            resetTaskForm();
            openModal('task-modal');
        }, 100);
    @endif
});
@endif
</script>
@endpush