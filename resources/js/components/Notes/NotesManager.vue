<template>
    <div class="notes-manager">
        <!-- Module Header -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-sticky-note"></i> Notes
                </h2>
                <div class="notes-summary">
                    <div class="summary-item">
                        <i class="fas fa-sticky-note"></i>
                        <span class="summary-label">Total Notes</span>
                        <span class="summary-value">{{ notes.length }}</span>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Note
            </button>
        </div>

        <!-- Search Bar -->
        <div class="quick-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="debouncedSearch"
                    placeholder="Search notes..."
                >
                <button v-if="searchQuery" class="clear-search" @click="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading notes...</p>
        </div>

        <!-- Notes Grid -->
        <div v-else class="notes-grid">
            <div 
                v-for="note in filteredNotes" 
                :key="note.id"
                class="note-card"
                @click="openEditModal(note)"
            >
                <div class="card-header">
                    <div class="note-title">{{ note.title }}</div>
                    <div class="note-date">{{ formatDate(note.created_at) }}</div>
                </div>
                <div class="card-body">
                    <div class="note-content">{{ truncateContent(note.content) }}</div>
                    <div v-if="note.tags && note.tags.length" class="note-tags">
                        <span v-for="tag in note.tags" :key="tag" class="tag">{{ tag }}</span>
                    </div>
                </div>
                <div class="card-footer" @click.stop>
                    <button class="icon-btn edit-btn" @click="openEditModal(note)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="icon-btn delete-btn" @click="deleteNote(note)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredNotes.length === 0" class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>{{ emptyMessage }}</p>
                <button class="btn btn-primary" @click="openAddModal">
                    <i class="fas fa-plus"></i> Create Your First Note
                </button>
            </div>
        </div>

        <!-- Add/Edit Note Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i :class="modalMode === 'add' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
                        {{ modalMode === 'add' ? 'Create New Note' : 'Edit Note' }}
                    </h5>
                    <button type="button" class="btn-close" @click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form @submit.prevent="saveNote">
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="form.title"
                                placeholder="Enter note title"
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.title" class="error-message">{{ errors.title[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>Content</label>
                            <textarea 
                                class="form-control" 
                                v-model="form.content"
                                rows="8"
                                placeholder="Write your note content here..."
                                :disabled="saving"
                            ></textarea>
                            <div v-if="errors.content" class="error-message">{{ errors.content[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>Tags (comma-separated)</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="form.tags"
                                placeholder="e.g., important, work, personal"
                                :disabled="saving"
                            >
                            <small class="text-muted">Separate tags with commas</small>
                            <div v-if="errors.tags" class="error-message">{{ errors.tags[0] }}</div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button 
                        v-if="modalMode === 'edit'" 
                        type="button" 
                        class="btn btn-danger" 
                        @click="deleteNoteFromModal"
                        :disabled="deleting"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="closeModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" @click="saveNote" :disabled="saving">
                        <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Create Note' : 'Update Note') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-container modal-sm" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">Delete Note</h5>
                    <button type="button" class="btn-close" @click="closeDeleteModal">×</button>
                </div>
                <div class="modal-body text-center">
                    <div class="delete-icon">
                        <i class="fas fa-trash-alt fa-4x text-danger"></i>
                    </div>
                    <p>Are you sure you want to delete "<strong>{{ noteToDelete?.title }}</strong>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" @click="confirmDelete">
                        <i v-if="deleting" class="fas fa-spinner fa-spin"></i>
                        {{ deleting ? 'Deleting...' : 'Yes, Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';

export default {
    name: 'NotesManager',
    
    data() {
        return {
            notes: [],
            searchQuery: '',
            loading: false,
            saving: false,
            deleting: false,
            modalMode: 'add',
            showModal: false,
            showDeleteModal: false,
            noteToDelete: null,
            
            form: {
                id: null,
                title: '',
                content: '',
                tags: ''
            },
            
            errors: {},
            
            searchTimeout: null
        };
    },

    computed: {
        filteredNotes() {
            let filtered = [...this.notes];
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(note => 
                    note.title.toLowerCase().includes(query) ||
                    (note.content && note.content.toLowerCase().includes(query)) ||
                    (note.tags && note.tags.some(tag => tag.toLowerCase().includes(query)))
                );
            }
            
            return filtered;
        },
        
        emptyMessage() {
            if (this.searchQuery) {
                return `No notes match "${this.searchQuery}". Try a different search term.`;
            }
            return 'No notes yet. Click "Add Note" to create your first note!';
        }
    },

    mounted() {
        this.fetchNotes();
        this.debouncedSearch = _.debounce(this.applySearch, 300);
    },

    methods: {
        async fetchNotes() {
            this.loading = true;
            try {
                const response = await axios.get('/notes');
                this.notes = response.data;
                console.log('Notes loaded:', this.notes);
            } catch (error) {
                console.error('Failed to fetch notes:', error);
                this.showNotification('Failed to load notes.', 'error');
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },

        truncateContent(content) {
            if (!content) return '';
            return content.length > 150 ? content.substring(0, 150) + '...' : content;
        },

        applySearch() {},

        clearSearch() {
            this.searchQuery = '';
        },

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
            document.body.style.overflow = 'hidden';
        },

        async openEditModal(note) {
            this.modalMode = 'edit';
            this.showModal = true;
            document.body.style.overflow = 'hidden';
            
            try {
                const response = await axios.get(`/notes/${note.id}/edit`);
                const noteData = response.data;
                
                this.form = {
                    id: noteData.id,
                    title: noteData.title,
                    content: noteData.content || '',
                    tags: noteData.tags ? noteData.tags.join(', ') : ''
                };
                
                this.errors = {};
            } catch (error) {
                console.error('Error loading note:', error);
                this.showNotification('Failed to load note data.', 'error');
                this.closeModal();
            }
        },

        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = '';
        },

        async saveNote() {
            this.saving = true;
            this.errors = {};
            
            try {
                if (!this.form.title.trim()) {
                    this.errors.title = ['Note title is required.'];
                    this.saving = false;
                    return;
                }
                
                const formData = {
                    title: this.form.title,
                    content: this.form.content,
                    tags: this.form.tags
                };
                
                let response;
                if (this.modalMode === 'add') {
                    response = await axios.post('/notes', formData);
                    this.notes.unshift(response.data);
                    this.showNotification('Note created successfully!', 'success');
                } else {
                    response = await axios.put(`/notes/${this.form.id}`, formData);
                    const index = this.notes.findIndex(n => n.id === this.form.id);
                    if (index !== -1) {
                        this.notes.splice(index, 1, response.data);
                    }
                    this.showNotification('Note updated successfully!', 'success');
                }
                
                this.closeModal();
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    const errorMessages = Object.values(this.errors).flat().join('\n');
                    this.showNotification(`Validation errors:\n${errorMessages}`, 'error');
                } else {
                    this.showNotification('Failed to save note. Please try again.', 'error');
                }
            } finally {
                this.saving = false;
            }
        },

        deleteNote(note) {
            this.noteToDelete = {
                id: note.id,
                title: note.title
            };
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        deleteNoteFromModal() {
            if (!this.form || !this.form.id) return;
            
            this.noteToDelete = {
                id: this.form.id,
                title: this.form.title
            };
            
            this.closeModal();
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.noteToDelete = null;
            this.deleting = false;
            document.body.style.overflow = '';
        },

        async confirmDelete() {
            if (!this.noteToDelete || !this.noteToDelete.id) return;
            
            this.deleting = true;
            
            try {
                await axios.delete(`/notes/${this.noteToDelete.id}`);
                
                const index = this.notes.findIndex(n => n.id === this.noteToDelete.id);
                if (index !== -1) {
                    this.notes.splice(index, 1);
                }
                
                this.showNotification('Note deleted successfully!', 'success');
                this.closeDeleteModal();
                
            } catch (error) {
                this.showNotification('Failed to delete note. Please try again.', 'error');
                this.deleting = false;
            }
        },

        showNotification(message, type = 'info') {
            alert(message);
        }
    }
};
</script>

<style scoped>
.notes-manager {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

/* Header */
.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    flex: 1;
}

.module-title {
    font-size: 2rem;
    color: #4361ee;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.notes-summary {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.summary-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.summary-value {
    font-weight: 600;
    font-size: 1.1rem;
    color: #212529;
}

/* Search */
.quick-actions {
    margin-bottom: 2rem;
}

.search-box {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 30px;
    padding: 0.5rem 1rem;
    max-width: 400px;
}

.search-box i {
    color: #6c757d;
    margin-right: 0.5rem;
}

.search-box input {
    border: none;
    background: transparent;
    flex: 1;
    outline: none;
    font-size: 0.95rem;
}

.clear-search {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
}

.clear-search:hover {
    color: #dc3545;
}

/* Notes Grid */
.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.note-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s;
    cursor: pointer;
    position: relative;
    border: 1px solid #e9ecef;
}

.note-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f3f5;
}

.note-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #4361ee;
}

.note-date {
    font-size: 0.75rem;
    color: #6c757d;
}

.card-body {
    margin-bottom: 1rem;
}

.note-content {
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.5;
    min-height: 60px;
}

.note-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.tag {
    background: #e9ecef;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    color: #6c757d;
}

.card-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.8rem;
    border-top: 1px solid #f1f3f5;
    opacity: 0;
    transition: opacity 0.2s;
}

.note-card:hover .card-footer {
    opacity: 1;
}

.icon-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.2s;
}

.icon-btn:hover {
    transform: scale(1.1);
}

.edit-btn:hover {
    background: #4361ee;
    color: white;
}

.delete-btn:hover {
    background: #dc3545;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 4rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f1f3f5;
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(4px);
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-container.modal-sm {
    max-width: 400px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.btn-close:hover {
    background: #f8f9fa;
    color: #dc3545;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    position: sticky;
    bottom: 0;
    background: white;
}

/* Form */
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
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.text-muted {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.text-danger {
    color: #dc3545;
}

.text-center {
    text-align: center;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3451d1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

/* Delete Modal */
.delete-icon {
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .notes-manager {
        padding: 1rem;
    }
    
    .module-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .notes-grid {
        grid-template-columns: 1fr;
    }
}
</style>