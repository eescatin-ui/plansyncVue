<template>
  <div class="admin-tasks">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-tasks"></i> Tasks</h2>
      <button class="btn btn-primary" @click="openTaskModal">
        <i class="fas fa-plus"></i> Add Task
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
          <label>Status</label>
          <select v-model="filters.status" @change="fetchTasks">
            <option value="">All Status</option>
            <option value="todo">To Do</option>
            <option value="inprogress">In Progress</option>
            <option value="done">Done</option>
          </select>
        </div>
        <div class="filter-group">
          <label>User</label>
          <select v-model="filters.user_id" @change="fetchTasks">
            <option value="">All Users</option>
            <option v-for="user in usersList" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Search</label>
          <input type="text" v-model="filters.search" placeholder="Search tasks..." @keyup.enter="fetchTasks">
        </div>
        <div class="filter-group">
          <label>&nbsp;</label>
          <button class="btn btn-secondary" @click="clearFilters">Clear</button>
        </div>
      </div>
    </div>

    <!-- Stats Cards - Same pattern as AdminClasses -->
    <div class="stats-grid">
      <div class="stat-card" @click="filterByStatus('todo')">
        <i class="fas fa-clock"></i>
        <div class="stat-number">{{ stats.pendingTasks || 0 }}</div>
        <div class="stat-label">Pending Tasks</div>
      </div>
      <div class="stat-card" @click="filterByStatus('inprogress')">
        <i class="fas fa-spinner"></i>
        <div class="stat-number">{{ stats.inProgressTasks || 0 }}</div>
        <div class="stat-label">In Progress</div>
      </div>
      <div class="stat-card" @click="filterByStatus('done')">
        <i class="fas fa-check-circle"></i>
        <div class="stat-number">{{ stats.completedTasks || 0 }}</div>
        <div class="stat-label">Completed</div>
      </div>
      <div class="stat-card" @click="clearFilters">
        <i class="fas fa-tasks"></i>
        <div class="stat-number">{{ stats.totalTasks || 0 }}</div>
        <div class="stat-label">Total Tasks</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading tasks...</p>
    </div>

    <!-- Tasks Table -->
    <div v-else class="table-container">
      <table class="data-table">
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
          <tr v-if="tasksList.length === 0">
            <td colspan="8" class="empty-state">
              <i class="fas fa-tasks"></i>
              <p>No tasks found</p>
            </td>
          </tr>
          <tr v-for="task in tasksList" :key="task.id">
            <td>{{ task.id }}</td>
            <td><strong>{{ task.title }}</strong></td>
            <td>{{ truncate(task.description, 50) || '-' }}</td>
            <td>{{ formatDate(task.due_date) }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(task.status)]">
                {{ getStatusText(task.status) }}
              </span>
            </td>
            <td>
              <span v-if="task.priority" :class="['priority-badge', getPriorityClass(task.priority)]">
                {{ task.priority }}
              </span>
              <span v-else class="priority-badge priority-none">None</span>
            </td>
            <td>{{ task.user?.name || 'Unknown' }}</td>
            <td>
              <button class="btn btn-sm btn-warning" @click="openEditModal(task)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(task)">
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
            <h4>{{ isEditing ? 'Edit Task' : 'Add New Task' }}</h4>
            <button type="button" class="close" @click="closeModal">&times;</button>
          </div>
          <form @submit.prevent="saveTask">
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
                <label>Description</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>
              <div class="form-row">
                <div class="form-group half">
                  <label>Due Date *</label>
                  <input type="datetime-local" v-model="form.due_date" class="form-control" required>
                </div>
                <div class="form-group half">
                  <label>Status *</label>
                  <select v-model="form.status" class="form-control" required>
                    <option value="todo">To Do</option>
                    <option value="inprogress">In Progress</option>
                    <option value="done">Done</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Priority</label>
                <select v-model="form.priority" class="form-control">
                  <option value="">None</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
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

    <!-- Delete Modal -->
    <div class="modal" :class="{ show: showDeleteModal }" @click.self="closeDeleteModal">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h4>Confirm Delete</h4>
            <button type="button" class="close" @click="closeDeleteModal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Delete task <strong>"{{ deleteTaskTitle }}"</strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteTask" :disabled="deleting">
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
  name: 'AdminTasks',
  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      tasksList: [],
      usersList: [],
      allUsers: [],
      stats: {
        pendingTasks: 0,
        inProgressTasks: 0,
        completedTasks: 0,
        totalTasks: 0
      },
      filters: {
        status: '',
        user_id: '',
        search: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      },
      showModal: false,
      showDeleteModal: false,
      isEditing: false,
      editingId: null,
      form: {
        user_id: '',
        title: '',
        description: '',
        due_date: '',
        status: 'todo',
        priority: ''
      },
      deleteTaskId: null,
      deleteTaskTitle: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchTasks();
    this.fetchUsers();
  },
  methods: {
    async fetchTasks() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/tasks/api', {
          params: {
            status: this.filters.status,
            user_id: this.filters.user_id,
            search: this.filters.search
          }
        });
        
        // Same pattern as AdminClasses - direct assignment
        this.tasksList = response.data.tasks || [];
        this.usersList = response.data.users || [];
        this.allUsers = response.data.users || [];
        this.stats = response.data.stats || this.stats;
        
        // Handle pagination if it exists
        if (response.data.tasks && response.data.tasks.data) {
          this.tasksList = response.data.tasks.data;
          this.pagination = {
            current_page: response.data.tasks.current_page || 1,
            last_page: response.data.tasks.last_page || 1,
            per_page: response.data.tasks.per_page || 10,
            total: response.data.tasks.total || 0,
            from: response.data.tasks.from || 0,
            to: response.data.tasks.to || 0
          };
        }
        
        console.log('Tasks loaded:', this.tasksList.length);
        console.log('Stats:', this.stats);
        
      } catch (error) {
        console.error('Error:', error);
        this.errorMessage = 'Failed to load tasks';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUsers() {
      try {
        const response = await axios.get('/admin/tasks/users');
        this.allUsers = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    
    filterByStatus(status) {
      this.filters.status = status;
      this.fetchTasks();
    },
    
    clearFilters() {
      this.filters = { status: '', user_id: '', search: '' };
      this.fetchTasks();
    },
    
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchTasks();
    },
    
    openTaskModal() {
      this.isEditing = false;
      this.form = {
        user_id: '',
        title: '',
        description: '',
        due_date: this.getDefaultDueDate(),
        status: 'todo',
        priority: ''
      };
      this.showModal = true;
    },
    
    openEditModal(task) {
      this.isEditing = true;
      this.editingId = task.id;
      this.form = {
        user_id: task.user_id,
        title: task.title,
        description: task.description || '',
        due_date: this.formatDateTimeLocal(task.due_date),
        status: task.status,
        priority: task.priority || ''
      };
      this.showModal = true;
    },
    
    async saveTask() {
      this.saving = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/admin/tasks/${this.editingId}`, this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        } else {
          response = await axios.post('/admin/tasks', this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        }
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeModal();
          this.fetchTasks();
          setTimeout(() => this.successMessage = '', 3000);
        } else {
          this.errorMessage = response.data.message || 'Error saving task';
          setTimeout(() => this.errorMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error saving task';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(task) {
      this.deleteTaskId = task.id;
      this.deleteTaskTitle = task.title;
      this.showDeleteModal = true;
    },
    
    async deleteTask() {
      this.deleting = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.delete(`/admin/tasks/${this.deleteTaskId}`, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeDeleteModal();
          this.fetchTasks();
          setTimeout(() => this.successMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error deleting task';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.deleting = false;
      }
    },
    
    closeModal() {
      this.showModal = false;
    },
    
    closeDeleteModal() {
      this.showDeleteModal = false;
    },
    
    getStatusClass(status) {
      const classes = {
        todo: 'status-todo',
        inprogress: 'status-inprogress',
        done: 'status-done'
      };
      return classes[status] || '';
    },
    
    getStatusText(status) {
      const texts = {
        todo: 'To Do',
        inprogress: 'In Progress',
        done: 'Done'
      };
      return texts[status] || status;
    },
    
    getPriorityClass(priority) {
      const classes = {
        low: 'priority-low',
        medium: 'priority-medium',
        high: 'priority-high'
      };
      return classes[priority] || 'priority-none';
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString();
    },
    
    formatDateTimeLocal(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toISOString().slice(0, 16);
    },
    
    getDefaultDueDate() {
      const now = new Date();
      now.setDate(now.getDate() + 1);
      return now.toISOString().slice(0, 16);
    },
    
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>

<style scoped>
.admin-tasks {
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

/* Filters Card - Same as AdminClasses */
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

/* Stats Grid - 4 cards */
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
}

.stat-card i.fa-clock { color: #ffc107; }
.stat-card i.fa-spinner { color: #fd7e14; }
.stat-card i.fa-check-circle { color: #28a745; }
.stat-card i.fa-tasks { color: #4361ee; }

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

/* Table */
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

/* Status Badges */
.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  display: inline-block;
}

.status-todo {
  background: #fef3c7;
  color: #92400e;
}

.status-inprogress {
  background: #dbeafe;
  color: #1e40af;
}

.status-done {
  background: #d1fae5;
  color: #065f46;
}

/* Priority Badges */
.priority-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  display: inline-block;
}

.priority-low {
  background: #d1fae5;
  color: #065f46;
}

.priority-medium {
  background: #fef3c7;
  color: #92400e;
}

.priority-high {
  background: #fee2e2;
  color: #991b1b;
}

.priority-none {
  background: #f1f5f9;
  color: #64748b;
}

/* Buttons */
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

/* Pagination */
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

/* Loading */
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

/* Empty State */
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

/* Modal */
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
  max-width: 500px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
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

/* Form */
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

.form-row {
  display: flex;
  gap: 15px;
}

.half {
  flex: 1;
}

/* Alerts */
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

/* Responsive */
@media (max-width: 768px) {
  .admin-tasks { padding: 15px; }
  .filters-row { flex-direction: column; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .form-row { flex-direction: column; gap: 0; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
}
</style>