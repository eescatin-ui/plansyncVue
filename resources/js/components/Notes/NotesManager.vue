<template>
    <div class="notes-container">
        <!-- Module Header -->
        <div class="module-header">
            <h2 class="module-title">
                <i class="fas fa-sticky-note"></i> Notes
            </h2>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Note
            </button>
        </div>

        <!-- Search Box -->
        <div class="search-box-wrapper" style="margin-bottom: 2rem; max-width: 400px;">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="onSearchInput"
                    placeholder="Search notes..." 
                    class="form-control"
                >
                <button @click="performSearch" class="btn btn-small">Search</button>
                <button 
                    v-if="searchQuery" 
                    @click="clearSearch" 
                    class="btn btn-small btn-secondary"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Notes Grid -->
        <div v-else class="notes-grid">
            <div 
                v-for="note in notes" 
                :key="note.id" 
                class="note-card"
                @click="handleNoteClick(note)"
            >
                <div class="note-card-header">
                    <div class="note-title">{{ note.title }}</div>
                    <div class="note-date">{{ formatDate(note.created_at) }}</div>
                </div>
                <div class="note-card-content">
                    {{ truncateContent(note.content) }}
                </div>
                <div v-if="note.tags && note.tags.length" class="note-tags">
                    <span v-for="tag in note.tags" :key="tag" class="tag">
                        {{ tag }}
                    </span>
                </div>
                <div class="note-actions" @click.stop>
                    <button 
                        type="button" 
                        class="btn btn-small btn-edit" 
                        @click="openEditModal(note)"
                    >
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-small btn-danger" 
                        @click="deleteNote(note)"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="notes.length === 0" class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>{{ emptyStateMessage }}</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="pagination-container mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                            Previous
                        </a>
                    </li>
                    <li 
                        v-for="page in pagination.last_page" 
                        :key="page"
                        class="page-item" 
                        :class="{ active: pagination.current_page === page }"
                    >
                        <a class="page-link" href="#" @click.prevent="changePage(page)">
                            {{ page }}
                        </a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Add/Edit Note Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">{{ modalMode === 'add' ? 'Add New Note' : 'Edit Note' }}</h5>
                    <button type="button" class="btn-close" @click="closeModal" :disabled="saving">×</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveNote">
                        <div class="form-group">
                            <label for="note_title">Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="note_title" 
                                v-model="form.title" 
                                required
                                :disabled="saving"
                                placeholder="Enter note title"
                            >
                            <div v-if="errors.title" class="text-danger mt-1">{{ errors.title[0] }}</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="note_content">Content <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control" 
                                id="note_content" 
                                v-model="form.content" 
                                rows="8" 
                                required
                                :disabled="saving"
                                placeholder="Write your note content here..."
                            ></textarea>
                            <div v-if="errors.content" class="text-danger mt-1">{{ errors.content[0] }}</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="note_tags">Tags (comma-separated)</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="note_tags" 
                                v-model="form.tags" 
                                placeholder="e.g., math, exam, project"
                                :disabled="saving"
                            >
                            <small class="text-muted">Separate tags with commas (e.g., important, personal, work)</small>
                            <div v-if="errors.tags" class="text-danger mt-1">{{ errors.tags[0] }}</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="saving">
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        @click="saveNote" 
                        :disabled="saving"
                    >
                        <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status"></span>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Save Note' : 'Update Note') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-container modal-sm" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">Delete Note</h5>
                    <button type="button" class="btn-close" @click="closeDeleteModal" :disabled="deleting">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<strong>{{ noteToDelete?.title }}</strong>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal" :disabled="deleting">
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-danger" 
                        @click="confirmDelete"
                        :disabled="deleting"
                    >
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'NotesManager',
    
    data() {
        return {
            notes: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 12,
                total: 0
            },
            searchQuery: '',
            loading: true,
            saving: false,
            deleting: false,
            modalMode: 'add', 
            showModal: false,
            showDeleteModal: false,
            form: {
                id: null,
                title: '',
                content: '',
                tags: ''
            },
            errors: {},
            noteToDelete: null,
            searchTimeout: null
        };
    },

    computed: {
        emptyStateMessage() {
            return this.searchQuery 
                ? 'No notes found matching your search.' 
                : 'No notes yet. Click "Add Note" to create your first note!';
        }
    },

    mounted() {
        console.log('NotesManager mounted');
        this.fetchNotes();
    },

    methods: {
        // Search with debounce
        onSearchInput() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 500);
        },

        // Fetch notes from API
        async fetchNotes(page = 1) {
            this.loading = true;
            try {
                const response = await axios.get('/notes', {
                    params: {
                        q: this.searchQuery,
                        page: page
                    }
                });
                
                console.log('Notes fetched:', response.data);
                
                // Handle both paginated and non-paginated responses
                if (response.data.data) {
                    this.notes = response.data.data;
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        per_page: response.data.per_page,
                        total: response.data.total
                    };
                } else {
                    this.notes = response.data;
                }
            } catch (error) {
                console.error('Failed to fetch notes:', error);
                this.showNotification('Failed to load notes. Please refresh the page.', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Format date for display
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },

        // Truncate long content
        truncateContent(content) {
            return content.length > 200 ? content.substring(0, 200) + '...' : content;
        },

        // Handle note click (optional - you can navigate to detail view)
        handleNoteClick(note) {
            console.log('Note clicked:', note);

        },

        // Search functionality
        async performSearch() {
            await this.fetchNotes(1);
        },

        // Clear search
        clearSearch() {
            this.searchQuery = '';
            this.fetchNotes(1);
        },

        // Change page
        async changePage(page) {
            if (page < 1 || page > this.pagination.last_page) return;
            await this.fetchNotes(page);
        },

        // Open modal for adding a new note
        openAddModal() {
            this.modalMode = 'add';
            this.form = {
                id: null,
                title: '',
                content: '',
                tags: ''
            };
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        },

        // Open modal for editing a note
        openEditModal(note) {
            this.modalMode = 'edit';
            this.form = {
                id: note.id,
                title: note.title,
                content: note.content,
                tags: note.tags ? note.tags.join(', ') : ''
            };
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        },

        // Close modal
        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = ''; // Restore scrolling
        },

        // Save note (create or update)
       async saveNote() {
    this.saving = true;
    this.errors = {};
    
    try {
        // Send tags as a string (comma-separated) - NOT as an array
        const formData = {
            title: this.form.title,
            content: this.form.content,
            tags: this.form.tags // Send as string, not array
        };

        let response;
        if (this.modalMode === 'add') {
            // Create new note
            response = await axios.post('/notes', formData);
            this.notes.unshift(response.data); // Add to beginning of list
            this.showNotification('Note created successfully!', 'success');
        } else {
            // Update existing note
            response = await axios.put(`/notes/${this.form.id}`, formData);
            const index = this.notes.findIndex(n => n.id === this.form.id);
            if (index !== -1) {
                this.notes.splice(index, 1, response.data); // Replace with updated note
            }
            this.showNotification('Note updated successfully!', 'success');
        }
        
        this.closeModal();
    } catch (error) {
        if (error.response && error.response.status === 422) {
            // Validation errors
            this.errors = error.response.data.errors;
        } else {
            console.error('Save failed:', error);
            this.showNotification('Failed to save note. Please try again.', 'error');
        }
    } finally {
        this.saving = false;
    }
},

        // Delete note (show confirmation)
        deleteNote(note) {
            this.noteToDelete = note;
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        },

        // Close delete modal
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.noteToDelete = null;
            document.body.style.overflow = ''; // Restore scrolling
        },

        // Confirm delete
        async confirmDelete() {
            this.deleting = true;
            
            try {
                await axios.delete(`/notes/${this.noteToDelete.id}`);
                
                // Remove from list
                const index = this.notes.findIndex(n => n.id === this.noteToDelete.id);
                if (index !== -1) {
                    this.notes.splice(index, 1);
                }
                
                this.showNotification('Note deleted successfully!', 'success');
                this.closeDeleteModal();
            } catch (error) {
                console.error('Delete failed:', error);
                this.showNotification('Failed to delete note. Please try again.', 'error');
            } finally {
                this.deleting = false;
            }
        },

        // Show notification 
        showNotification(message, type = 'info') {
            alert(message);
        }
    }
};
</script>

<style scoped>
.notes-container {
    width: 100%;
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.note-card {
    background-color: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    height: 300px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.note-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.note-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray, #e9ecef);
}

.note-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--primary, #4361ee);
    flex: 1;
    word-break: break-word;
}

.note-date {
    font-size: 0.8rem;
    color: var(--gray, #6c757d);
    white-space: nowrap;
    margin-left: 1rem;
}

.note-card-content {
    flex: 1;
    overflow-y: auto;
    color: var(--dark, #212529);
    line-height: 1.6;
    margin-bottom: 1rem;
    word-break: break-word;
}

.note-tags {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.tag {
    background-color: var(--light-gray, #e9ecef);
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    color: var(--gray, #6c757d);
}

.note-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--light-gray, #e9ecef);
}

.search-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--light-gray, #f8f9fa);
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.search-input-group .form-control {
    border: none;
    background: transparent;
    flex: 1;
}

.search-input-group .form-control:focus {
    outline: none;
    box-shadow: none;
}

.search-input-group i {
    color: var(--gray, #6c757d);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray, #6c757d);
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--light-gray, #dee2e6);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modal-container {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-container.modal-sm {
    max-width: 400px;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close:hover {
    color: #dc3545;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.btn-close:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    position: sticky;
    bottom: 0;
    background: white;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary, #4361ee);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

textarea.form-control {
    resize: vertical;
    min-height: 150px;
}

.text-muted {
    color: var(--gray, #6c757d);
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.text-danger {
    color: #dc3545;
    font-size: 0.875rem;
}

/* Button Styles */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background-color: var(--primary, #4361ee);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: var(--secondary, #3a0ca3);
}

.btn-secondary {
    background-color: var(--gray, #6c757d);
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #5a6268;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background-color: #c82333;
}

.btn-edit {
    background-color: #28a745;
    color: white;
}

.btn-edit:hover:not(:disabled) {
    background-color: #218838;
}

.btn-small {
    padding: 0.25rem 0.75rem;
    font-size: 0.85rem;
}

/* Pagination Styles */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination {
    display: flex;
    gap: 0.25rem;
    list-style: none;
    padding: 0;
}

.page-item {
    margin: 0;
}

.page-item.active .page-link {
    background-color: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
}

.page-item.disabled .page-link {
    color: var(--gray, #6c757d);
    pointer-events: none;
    cursor: not-allowed;
    background-color: #fff;
    border-color: #dee2e6;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    color: var(--primary, #4361ee);
    text-decoration: none;
    transition: all 0.2s;
    display: block;
}

.page-link:hover:not(.disabled .page-link) {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

/* Loading Spinner */
.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    vertical-align: text-bottom;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

.text-center {
    text-align: center;
}

.py-5 {
    padding-top: 3rem;
    padding-bottom: 3rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.mt-1 {
    margin-top: 0.25rem;
}
</style>