@extends('layouts.admin')

@section('content')
<!-- Notes Module -->
<div class="module active" id="notes">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-sticky-note"></i> Notes</h2>
        <button class="btn" id="add-note-btn">
            <i class="fas fa-plus"></i> Add Note
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
            <form id="filter-form" method="GET" action="{{ route('admin.notes.index') }}">
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
                        <label for="filter-search">Search</label>
                        <div class="search-box" style="width: 100%;">
                            <i class="fas fa-search"></i>
                            <input type="text" id="filter-search" name="search" placeholder="Search by title, content, or tags..." 
                                   value="{{ request('search') }}" onkeyup="if(event.key === 'Enter') this.form.submit()">
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="submit" class="btn btn-small"><i class="fas fa-search"></i> Apply Filters</button>
                    <a href="{{ route('admin.notes.index') }}" class="btn btn-small btn-danger"><i class="fas fa-times"></i> Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card" onclick="window.location='{{ route('admin.notes.index') }}'">
            <i class="fas fa-sticky-note fa-2x" style="color: var(--success);"></i>
            <div class="stat-number">{{ $stats['totalNotes'] ?? 0 }}</div>
            <div class="stat-label">Total Notes</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-week fa-2x" style="color: var(--primary);"></i>
            <div class="stat-number">{{ $stats['recentNotes'] ?? 0 }}</div>
            <div class="stat-label">Last 7 Days</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-tags fa-2x" style="color: var(--accent);"></i>
            <div class="stat-number">{{ $stats['notesWithTags'] ?? 0 }}</div>
            <div class="stat-label">Notes with Tags</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-edit fa-2x" style="color: var(--warning);"></i>
            <div class="stat-number">{{ $stats['notesPerUser'] ?? 0 }}</div>
            <div class="stat-label">Avg per User</div>
        </div>
    </div>

    <!-- Top Tags (if available) -->
    @if(isset($topTags) && count($topTags) > 0)
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tags"></i> Popular Tags</h3>
        </div>
        <div style="padding: 1rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                @foreach($topTags as $tag => $count)
                @php
                    $colors = [
                        '#4361ee', '#3a0ca3', '#7209b7', '#4cc9f0', '#f72585', '#e63946',
                        '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653', '#2a9d8f'
                    ];
                    $hash = 0;
                    for ($i = 0; $i < strlen($tag); $i++) {
                        $hash = ord($tag[$i]) + (($hash << 5) - $hash);
                    }
                    $color = $colors[abs($hash) % count($colors)];
                @endphp
                <a href="{{ route('admin.notes.index', ['tag' => $tag]) }}" 
                   class="tag-badge" 
                   style="padding: 0.25rem 0.75rem; background-color: {{ $color }}; color: white; border-radius: 20px; text-decoration: none; font-size: 0.875rem;">
                    {{ $tag }} <span style="background-color: rgba(255,255,255,0.2); padding: 0 0.5rem; border-radius: 10px; margin-left: 5px;">{{ $count }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Notes Table -->
    <div class="table-container">
        <table id="notesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Content Preview</th>
                    <th>Tags</th>
                    <th>User</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notes as $note)
                <tr>
                    <td>#{{ $note->id }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="background-color: #{{ substr(md5($note->title), 0, 6) }}; font-size: 0.8rem;">
                                {{ substr($note->title, 0, 2) }}
                            </div>
                            <span>{{ $note->title }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ strip_tags($note->content) }}
                        </div>
                        <small style="color: var(--gray);">{{ strlen(strip_tags($note->content)) }} characters</small>
                    </td>
                    <td>
                        @if($note->tags && count($note->tags) > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 5px; max-width: 200px;">
                                @foreach($note->tags as $tag)
                                    @if(trim($tag))
                                        @php
                                            $colors = [
                                                '#4361ee', '#3a0ca3', '#7209b7', '#4cc9f0', '#f72585', '#e63946',
                                                '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653', '#2a9d8f'
                                            ];
                                            $hash = 0;
                                            for ($i = 0; $i < strlen(trim($tag)); $i++) {
                                                $hash = ord(trim($tag)[$i]) + (($hash << 5) - $hash);
                                            }
                                            $color = $colors[abs($hash) % count($colors)];
                                        @endphp
                                        <span class="tag-badge" 
                                              style="padding: 0.125rem 0.5rem; background-color: {{ $color }}; color: white; border-radius: 12px; font-size: 0.75rem;">
                                            {{ trim($tag) }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span style="color: var(--gray); font-style: italic;">No tags</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ substr($note->user->name ?? 'NA', 0, 2) }}
                            </div>
                            <span>{{ $note->user->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-calendar-alt" style="color: var(--gray);"></i>
                            {{ $note->created_at->format('M d, Y') }}
                        </div>
                        <small style="color: var(--gray);">{{ $note->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-small view-note-btn" data-id="{{ $note->id }}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-small edit-note-btn" data-id="{{ $note->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-small btn-danger delete-note-btn" data-id="{{ $note->id }}" data-title="{{ $note->title }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-sticky-note"></i>
                        <p>No notes found</p>
                        @if(request()->hasAny(['user_id', 'search', 'tag']))
                            <a href="{{ route('admin.notes.index') }}" class="btn btn-small" style="margin-top: 10px;">
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
    @if($notes->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="color: var(--gray); font-size: 0.875rem;">
            Showing {{ $notes->firstItem() }} to {{ $notes->lastItem() }} of {{ $notes->total() }} notes
        </div>
        <div style="display: flex; gap: 5px;">
            @if($notes->onFirstPage())
                <span class="btn btn-small" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $notes->previousPageUrl() }}" class="btn btn-small">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif
            
            @foreach($notes->getUrlRange(max(1, $notes->currentPage() - 2), min($notes->lastPage(), $notes->currentPage() + 2)) as $page => $url)
                @if($page == $notes->currentPage())
                    <span class="btn btn-small" style="background-color: var(--primary); color: white;">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="btn btn-small">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
            
            @if($notes->hasMorePages())
                <a href="{{ $notes->nextPageUrl() }}" class="btn btn-small">
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

<!-- View Note Modal -->
<div class="modal" id="view-note-modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h2>Note Details</h2>
            <button class="close-modal" data-modal="view-note-modal">×</button>
        </div>
        <div id="view-note-content" style="padding: 1rem;">
            <!-- Content will be loaded here -->
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
            <button class="btn btn-small" data-modal="view-note-modal">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Note Modal -->
<div class="modal" id="note-modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2 id="note-modal-title">Add New Note</h2>
            <button class="close-modal" data-modal="note-modal">×</button>
        </div>
        <form id="note-form" method="POST">
            @csrf
            <input type="hidden" id="note-id" name="id">
            <input type="hidden" id="form-method" name="_method" value="POST">
            
            <div class="form-group">
                <label for="modal-note-user-id">User *</label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="modal-note-user-id" name="user_id" required>
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
                <label for="modal-note-title">Title *</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="modal-note-title" name="title" placeholder="e.g., Meeting notes, Project ideas, Todo list" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-note-content">Content *</label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="modal-note-content" name="content" rows="8" placeholder="Write your note here..." required>{{ old('content') }}</textarea>
                <small class="text-muted">You can use basic HTML tags for formatting.</small>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="modal-note-tags">Tags (Optional)</label>
                <input type="text" class="form-control @error('tags') is-invalid @enderror" id="modal-note-tags" name="tags" value="{{ old('tags') }}" placeholder="e.g., work, personal, ideas, meeting (comma separated)">
                <small class="text-muted">Separate tags with commas. Example: "work, meeting, important"</small>
                @error('tags')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn" id="modal-save-note">
                    <i class="fas fa-save"></i> <span id="modal-save-text">Save Note</span>
                </button>
                <button type="button" class="btn" data-modal="note-modal" style="margin-left: auto;">
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
            <p id="delete-confirm-message">Are you sure you want to delete this note?</p>
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

<!-- Hidden Data Container for Edit (like in Tasks) -->
<div style="display: none;">
    @foreach($notes as $note)
    <div id="note-data-{{ $note->id }}">
        <input type="hidden" class="note-user-id" value="{{ $note->user_id }}">
        <input type="hidden" class="note-title" value="{{ $note->title }}">
        <input type="hidden" class="note-content" value="{{ $note->content }}">
        <input type="hidden" class="note-tags" value="{{ is_array($note->tags) ? implode(', ', $note->tags) : '' }}">
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

.tag-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    transition: transform 0.2s;
}

.tag-badge:hover {
    transform: translateY(-2px);
}

.error-message {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: none;
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

.view-note-detail {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray);
}

.view-note-detail:last-child {
    border-bottom: none;
}

.view-note-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-note-value {
    color: var(--dark);
    font-size: 1.1rem;
}

.note-content {
    background: var(--light-gray);
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1rem 0;
    max-height: 400px;
    overflow-y: auto;
    line-height: 1.6;
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

.is-invalid {
    border-color: var(--danger) !important;
}

.invalid-feedback {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
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

function resetNoteForm() {
    const form = document.getElementById('note-form');
    form.reset();
    form.action = '{{ route("admin.notes.store") }}';
    document.getElementById('note-modal-title').textContent = 'Add New Note';
    document.getElementById('modal-save-text').textContent = 'Save Note';
    document.getElementById('note-id').value = '';
    document.getElementById('form-method').value = 'POST';
    
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
    // Add Note Button
    document.getElementById('add-note-btn').addEventListener('click', function() {
        resetNoteForm();
        openModal('note-modal');
    });
    
    // View Note Buttons
    document.querySelectorAll('.view-note-btn').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            viewNote(noteId);
        });
    });
    
    // Edit Note Buttons
    document.querySelectorAll('.edit-note-btn').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            editNote(noteId);
        });
    });
    
    // Delete Note Buttons
    document.querySelectorAll('.delete-note-btn').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            const noteTitle = this.getAttribute('data-title');
            showDeleteConfirmation(noteId, noteTitle);
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
        form.submit();
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

// View Note Function (Basic - for modal)
function viewNote(noteId) {
    // Get note data from hidden container
    const noteContainer = document.getElementById('note-data-' + noteId);
    if (!noteContainer) {
        alert('Note data not found');
        return;
    }
    
    const title = noteContainer.querySelector('.note-title').value;
    const content = noteContainer.querySelector('.note-content').value;
    const tags = noteContainer.querySelector('.note-tags').value;
    
    let tagsHtml = '';
    if (tags) {
        const tagsArray = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
        tagsHtml = tagsArray.map(tag => {
            const colors = [
                '#4361ee', '#3a0ca3', '#7209b7', '#4cc9f0', '#f72585', '#e63946',
                '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653', '#2a9d8f'
            ];
            let hash = 0;
            for (let i = 0; i < tag.length; i++) {
                hash = tag.charCodeAt(i) + ((hash << 5) - hash);
            }
            const color = colors[Math.abs(hash) % colors.length];
            
            return `<span class="tag-badge" style="background-color: ${color}; color: white; margin: 2px;">
                ${escapeHtml(tag)}
            </span>`;
        }).join('');
    }
    
    const htmlContent = `
        <div class="view-note-detail">
            <div class="view-note-label">
                <i class="fas fa-sticky-note"></i> Title
            </div>
            <div class="view-note-value">${escapeHtml(title)}</div>
        </div>
        
        <div class="view-note-detail">
            <div class="view-note-label">
                <i class="fas fa-align-left"></i> Content
            </div>
            <div class="note-content">
                ${escapeHtml(content).replace(/\n/g, '<br>')}
            </div>
        </div>
        
        ${tagsHtml ? `
        <div class="view-note-detail">
            <div class="view-note-label">
                <i class="fas fa-tags"></i> Tags
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 0.5rem;">
                ${tagsHtml}
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('view-note-content').innerHTML = htmlContent;
    openModal('view-note-modal');
}

// Edit Note Function
function editNote(noteId) {
    // Get note data from hidden container
    const noteContainer = document.getElementById('note-data-' + noteId);
    if (!noteContainer) {
        alert('Note data not found');
        return;
    }
    
    // Populate form with note data
    document.getElementById('note-modal-title').textContent = 'Edit Note';
    document.getElementById('modal-save-text').textContent = 'Update Note';
    document.getElementById('note-id').value = noteId;
    document.getElementById('form-method').value = 'PUT';
    
    // Update form action
    const form = document.getElementById('note-form');
    form.action = `/admin/notes/${noteId}`;
    
    // Set form values
    document.getElementById('modal-note-user-id').value = noteContainer.querySelector('.note-user-id').value;
    document.getElementById('modal-note-title').value = noteContainer.querySelector('.note-title').value;
    document.getElementById('modal-note-content').value = noteContainer.querySelector('.note-content').value;
    document.getElementById('modal-note-tags').value = noteContainer.querySelector('.note-tags').value;
    
    // Clear any validation errors
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.classList.remove('is-invalid');
    });
    const invalidFeedback = document.querySelectorAll('.invalid-feedback');
    invalidFeedback.forEach(feedback => feedback.style.display = 'none');
    
    openModal('note-modal');
}

// Show Delete Confirmation
function showDeleteConfirmation(noteId, noteTitle) {
    document.getElementById('delete-confirm-message').innerHTML = 
        `Are you sure you want to delete the note "<strong>${escapeHtml(noteTitle)}</strong>"? This action cannot be undone.`;
    
    const form = document.getElementById('delete-form');
    form.action = `/admin/notes/${noteId}`;
    
    openModal('delete-confirm-modal');
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
    @if(isset($editNote) && $editNote->id)
        // If editing a note and there are errors, reopen edit modal
        setTimeout(() => {
            editNote({{ $editNote->id ?? 'null' }});
        }, 100);
    @else
        // If creating a note and there are errors, reopen create modal
        setTimeout(() => {
            resetNoteForm();
            openModal('note-modal');
        }, 100);
    @endif
});
@endif
</script>
@endpush