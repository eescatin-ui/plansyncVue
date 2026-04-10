<template>
  <div class="admin-notes">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-sticky-note"></i> Notes</h2>
      <button class="btn btn-primary" @click="openAddModal">
        <i class="fas fa-plus"></i> Add Note
      </button>
    </div>

    <!-- Alert Messages -->
    <div v-if="successMessage" class="alert alert-success">
      <i class="fas fa-check-circle"></i> {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-row">
        <div class="filter-group">
          <label>User</label>
          <select v-model="filters.user_id" @change="fetchNotes">
            <option value="">All Users</option>
            <option v-for="user in usersList" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Search</label>
          <input type="text" v-model="filters.search" placeholder="Search notes..." @keyup.enter="fetchNotes">
        </div>
        <div class="filter-group">
          <label>&nbsp;</label>
          <button class="btn btn-secondary" @click="clearFilters">Clear</button>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <i class="fas fa-sticky-note"></i>
        <div class="stat-number">{{ stats.totalNotes || 0 }}</div>
        <div class="stat-label">Total Notes</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-week"></i>
        <div class="stat-number">{{ stats.recentNotes || 0 }}</div>
        <div class="stat-label">Last 7 Days</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-tags"></i>
        <div class="stat-number">{{ stats.notesWithTags || 0 }}</div>
        <div class="stat-label">Notes with Tags</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-chart-line"></i>
        <div class="stat-number">{{ stats.avgPerUser || 0 }}</div>
        <div class="stat-label">Avg per User</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading notes...</p>
    </div>

    <!-- Notes Table -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Tags</th>
            <th>User</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="notesList.length === 0">
            <td colspan="7" class="empty-state">
              <i class="fas fa-sticky-note"></i>
              <p>No notes found</p>
            </td>
          </tr>
          <tr v-for="note in notesList" :key="note.id">
            <td>#{{ note.id }}</td>
            <td><strong>{{ note.title }}</strong></td>
            <td>{{ truncate(note.content, 60) || '-' }}</td>
            <td>
              <div v-if="note.tags && note.tags.length" class="tags-list">
                <span v-for="tag in note.tags.slice(0, 2)" :key="tag" class="tag-badge">
                  {{ tag }}
                </span>
                <span v-if="note.tags.length > 2" class="tag-more">+{{ note.tags.length - 2 }}</span>
              </div>
              <span v-else class="no-tags">No tags</span>
            </td>
            <td>{{ note.user?.name || 'Unknown' }}</td>
            <td>{{ formatDate(note.created_at) }}</td>
            <td>
              <button class="btn btn-sm btn-info" @click="viewNote(note)" title="View">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn btn-sm btn-warning" @click="openEditModal(note)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(note)" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="pagination">
      <button class="page-btn" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">
        Previous
      </button>
      <span class="page-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button class="page-btn" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">
        Next
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" :class="{ show: showModal }" @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4>{{ isEditing ? 'Edit Note' : 'Add New Note' }}</h4>
            <button type="button" class="close" @click="closeModal">&times;</button>
          </div>
          <form @submit.prevent="saveNote">
            <div class="modal-body">
              <div class="form-group">
                <label>User *</label>
                <select v-model="form.user_id" class="form-control" required>
                  <option value="">Select User</option>
                  <option v-for="user in allUsers" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Title *</label>
                <input type="text" v-model="form.title" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Content</label>
                <textarea v-model="form.content" class="form-control" rows="6"></textarea>
              </div>
              <div class="form-group">
                <label>Tags (comma separated)</label>
                <input type="text" v-model="form.tags" class="form-control" placeholder="e.g., work, personal">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Note Modal -->
    <div class="modal" :class="{ show: showViewModal }" @click.self="closeViewModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4>Note Details</h4>
            <button type="button" class="close" @click="closeViewModal">&times;</button>
          </div>
          <div class="modal-body" v-if="viewingNote">
            <div class="detail-section">
              <div class="section-title"><i class="fas fa-sticky-note"></i> Title</div>
              <div class="detail-value">{{ viewingNote.title }}</div>
            </div>
            <div class="detail-section">
              <div class="section-title"><i class="fas fa-align-left"></i> Content</div>
              <div class="detail-value note-content">{{ viewingNote.content || 'No content' }}</div>
            </div>
            <div v-if="viewingNote.tags && viewingNote.tags.length" class="detail-section">
              <div class="section-title"><i class="fas fa-tags"></i> Tags</div>
              <div class="tags-list">
                <span v-for="tag in viewingNote.tags" :key="tag" class="tag-badge">{{ tag }}</span>
              </div>
            </div>
            <div class="detail-section">
              <div class="section-title"><i class="fas fa-user"></i> User Information</div>
              <div class="user-info-card">
                <div class="user-avatar">{{ getUserInitials(viewingNote.user?.name) }}</div>
                <div>
                  <div class="user-name">{{ viewingNote.user?.name || 'Unknown' }}</div>
                  <div class="user-email">{{ viewingNote.user?.email || '' }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeViewModal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal" :class="{ show: showDeleteModal }" @click.self="closeDeleteModal">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h4>Confirm Delete</h4>
            <button type="button" class="close" @click="closeDeleteModal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Delete note <strong>"{{ deleteNoteTitle }}"</strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteNote" :disabled="deleting">
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminNotes',
  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      notesList: [],
      usersList: [],
      allUsers: [],
      stats: {
        totalNotes: 0,
        recentNotes: 0,
        notesWithTags: 0,
        avgPerUser: 0
      },
      filters: {
        user_id: '',
        search: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      showModal: false,
      showViewModal: false,
      showDeleteModal: false,
      isEditing: false,
      editingId: null,
      viewingNote: null,
      form: {
        user_id: '',
        title: '',
        content: '',
        tags: ''
      },
      deleteNoteId: null,
      deleteNoteTitle: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchNotes();
    this.fetchUsers();
  },
  methods: {
    async fetchNotes() {
      this.loading = true;
      try {
        const params = {
          user_id: this.filters.user_id,
          search: this.filters.search
        };
        const response = await axios.get('/admin/notes/api', { params });
        
        console.log('API Response:', response.data);
        
        // Same pattern as AdminClasses - direct assignment
        if (response.data.notes) {
          // Handle paginated data
          if (response.data.notes.data) {
            this.notesList = response.data.notes.data;
            this.pagination = {
              current_page: response.data.notes.current_page || 1,
              last_page: response.data.notes.last_page || 1,
              per_page: response.data.notes.per_page || 10,
              total: response.data.notes.total || 0
            };
          } else {
            this.notesList = response.data.notes;
          }
          this.usersList = response.data.users || [];
          this.allUsers = response.data.users || [];
          this.stats = response.data.stats || this.stats;
        }
        
        console.log('Notes loaded:', this.notesList.length);
        console.log('Stats:', this.stats);
        
      } catch (error) {
        console.error('Error fetching notes:', error);
        this.errorMessage = 'Failed to load notes';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUsers() {
      try {
        const response = await axios.get('/admin/notes/users');
        this.allUsers = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    
    clearFilters() {
      this.filters = { user_id: '', search: '' };
      this.fetchNotes();
    },
    
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchNotes();
    },
    
    openAddModal() {
      this.isEditing = false;
      this.form = {
        user_id: '',
        title: '',
        content: '',
        tags: ''
      };
      this.showModal = true;
    },
    
    openEditModal(note) {
      this.isEditing = true;
      this.editingId = note.id;
      this.form = {
        user_id: note.user_id,
        title: note.title,
        content: note.content || '',
        tags: note.tags ? note.tags.join(', ') : ''
      };
      this.showModal = true;
    },
    
    viewNote(note) {
      this.viewingNote = note;
      this.showViewModal = true;
    },
    
    async saveNote() {
      this.saving = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/admin/notes/${this.editingId}`, this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        } else {
          response = await axios.post('/admin/notes', this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        }
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeModal();
          this.fetchNotes();
          setTimeout(() => this.successMessage = '', 3000);
        } else {
          this.errorMessage = response.data.message || 'Error saving note';
          setTimeout(() => this.errorMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error saving note';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(note) {
      this.deleteNoteId = note.id;
      this.deleteNoteTitle = note.title;
      this.showDeleteModal = true;
    },
    
    async deleteNote() {
      this.deleting = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.delete(`/admin/notes/${this.deleteNoteId}`, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeDeleteModal();
          this.fetchNotes();
          setTimeout(() => this.successMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error deleting note';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.deleting = false;
      }
    },
    
    closeModal() {
      this.showModal = false;
    },
    
    closeViewModal() {
      this.showViewModal = false;
      this.viewingNote = null;
    },
    
    closeDeleteModal() {
      this.showDeleteModal = false;
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString();
    },
    
    getUserInitials(name) {
      if (!name) return 'NA';
      return name.substring(0, 2).toUpperCase();
    },
    
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>

<style scoped>
.admin-notes {
  padding: 20px;
  background: #f8fafc;
  min-height: 100vh;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.module-title {
  margin: 0;
  font-size: 24px;
  color: #1e293b;
}

.module-title i {
  color: #4361ee;
  margin-right: 10px;
}

.filters-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-row {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  flex: 1;
  min-width: 150px;
}

.filter-group label {
  display: block;
  margin-bottom: 5px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
}

.filter-group select,
.filter-group input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-card i {
  font-size: 28px;
  margin-bottom: 10px;
  color: #4361ee;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: #1e293b;
}

.stat-label {
  color: #64748b;
  margin-top: 5px;
  font-size: 13px;
}

.table-container {
  background: white;
  border-radius: 12px;
  overflow-x: auto;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 12px 15px;
  text-align: left;
  background: #f8fafc;
  font-weight: 600;
  border-bottom: 1px solid #e2e8f0;
  color: #1e293b;
}

.data-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #e2e8f0;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.tag-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  background: #e2e8f0;
  color: #475569;
}

.tag-more {
  font-size: 11px;
  color: #64748b;
}

.no-tags {
  color: #94a3b8;
  font-style: italic;
  font-size: 12px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-primary {
  background: #4361ee;
  color: white;
}

.btn-primary:hover {
  background: #3451d1;
}

.btn-secondary {
  background: #64748b;
  color: white;
}

.btn-secondary:hover {
  background: #475569;
}

.btn-info {
  background: #06b6d4;
  color: white;
}

.btn-info:hover {
  background: #0891b2;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
  margin: 0 2px;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
  align-items: center;
}

.page-btn {
  padding: 6px 12px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #64748b;
}

.loading-state {
  text-align: center;
  padding: 40px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #4361ee;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

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

.modal.show {
  display: flex;
}

.modal-dialog {
  width: 90%;
  max-width: 600px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.modal-lg {
  max-width: 700px;
}

.modal-sm {
  max-width: 400px;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.modal-header h4 {
  margin: 0;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background: #f8fafc;
}

.close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #94a3b8;
}

.close:hover {
  color: #ef4444;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #1e293b;
  font-size: 14px;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #4361ee;
}

.alert {
  padding: 12px 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.text-danger {
  color: #dc2626;
  font-size: 12px;
  margin-top: 5px;
}

.detail-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.detail-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.section-title {
  font-weight: 600;
  color: #4361ee;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-value {
  color: #1e293b;
  line-height: 1.5;
}

.note-content {
  background: #f8fafc;
  padding: 15px;
  border-radius: 8px;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.user-info-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8fafc;
  border-radius: 8px;
}

.user-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 18px;
}

.user-name {
  font-weight: 600;
  font-size: 16px;
  color: #1e293b;
}

.user-email {
  font-size: 13px;
  color: #64748b;
}

@media (max-width: 768px) {
  .admin-notes { padding: 15px; }
  .filters-row { flex-direction: column; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
}
</style>