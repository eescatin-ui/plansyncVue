<template>
  <div class="admin-reminders">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-bell"></i> Reminders</h2>
      <button class="btn btn-primary" @click="openAddModal">
        <i class="fas fa-plus"></i> Add Reminder
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
          <select v-model="filters.user_id" @change="fetchReminders">
            <option value="">All Users</option>
            <option v-for="user in usersList" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Search</label>
          <input type="text" v-model="filters.search" placeholder="Search reminders..." @keyup.enter="fetchReminders">
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
        <i class="fas fa-bell"></i>
        <div class="stat-number">{{ stats.totalReminders || 0 }}</div>
        <div class="stat-label">Total Reminders</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-clock"></i>
        <div class="stat-number">{{ stats.upcomingReminders || 0 }}</div>
        <div class="stat-label">Upcoming</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-history"></i>
        <div class="stat-number">{{ stats.pastReminders || 0 }}</div>
        <div class="stat-label">Past</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-tasks"></i>
        <div class="stat-number">{{ stats.remindersWithTasks || 0 }}</div>
        <div class="stat-label">With Tasks</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-day"></i>
        <div class="stat-number">{{ stats.todaysReminders || 0 }}</div>
        <div class="stat-label">Today's</div>
      </div>
    </div>

    <!-- Next 24 Hours Section -->
    <div v-if="next24HoursList.length > 0" class="next-hours-card">
      <div class="card-header">
        <h3><i class="fas fa-hourglass-half"></i> Next 24 Hours</h3>
        <span class="badge">{{ next24HoursList.length }} reminders</span>
      </div>
      <div class="next-hours-list">
        <div v-for="reminder in next24HoursList" :key="reminder.id" class="next-hours-item">
          <div class="reminder-icon">
            <i class="fas fa-bell"></i>
          </div>
          <div class="reminder-info">
            <div class="reminder-title">{{ reminder.title }}</div>
            <div class="reminder-meta">
              <span><i class="fas fa-user"></i> {{ reminder.user?.name || 'Unknown' }}</span>
              <span><i class="fas fa-clock"></i> {{ formatTime(reminder.reminder_time) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading reminders...</p>
    </div>

    <!-- Reminders Table -->
    <div v-else class="table-container">
      <table class="data-table">
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
          <tr v-if="remindersList.length === 0">
            <td colspan="8" class="empty-state">
              <i class="fas fa-bell-slash"></i>
              <p>No reminders found</p>
            </td>
          </tr>
          <tr v-for="reminder in remindersList" :key="reminder.id">
            <td>#{{ reminder.id }}</td>
            <td><strong>{{ reminder.title }}</strong></td>
            <td>{{ truncate(reminder.description, 50) || '-' }}</td>
            <td>{{ formatDateTime(reminder.reminder_time) }}</td>
            <td>
              <span :class="['status-badge', isUpcoming(reminder.reminder_time) ? 'status-upcoming' : 'status-past']">
                {{ isUpcoming(reminder.reminder_time) ? 'Upcoming' : 'Past' }}
              </span>
            </td>
            <td>{{ reminder.user?.name || 'Unknown' }}</td>
            <td>{{ reminder.task?.title || '-' }}</td>
            <td>
              <button class="btn btn-sm btn-warning" @click="openEditModal(reminder)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(reminder)" title="Delete">
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
            <h4>{{ isEditing ? 'Edit Reminder' : 'Add New Reminder' }}</h4>
            <button type="button" class="close" @click="closeModal">&times;</button>
          </div>
          <form @submit.prevent="saveReminder">
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
              <div class="form-group">
                <label>Reminder Time *</label>
                <input type="datetime-local" v-model="form.reminder_time" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Task (Optional)</label>
                <select v-model="form.task_id" class="form-control">
                  <option value="">None</option>
                  <option v-for="task in allTasks" :key="task.id" :value="task.id">
                    {{ task.title }} ({{ task.user?.name || 'Unknown' }})
                  </option>
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
            <p>Delete reminder <strong>"{{ deleteReminderTitle }}"</strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteReminder" :disabled="deleting">
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
  name: 'AdminReminders',
  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      remindersList: [],
      usersList: [],
      tasksList: [],
      allUsers: [],
      allTasks: [],
      next24HoursList: [],
      stats: {
        totalReminders: 0,
        upcomingReminders: 0,
        pastReminders: 0,
        remindersWithTasks: 0,
        todaysReminders: 0
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
      showDeleteModal: false,
      isEditing: false,
      editingId: null,
      form: {
        user_id: '',
        title: '',
        description: '',
        reminder_time: '',
        task_id: ''
      },
      deleteReminderId: null,
      deleteReminderTitle: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchReminders();
    this.fetchUsers();
    this.fetchTasks();
  },
  methods: {
    async fetchReminders() {
      this.loading = true;
      try {
        const params = {
          user_id: this.filters.user_id,
          search: this.filters.search
        };
        const response = await axios.get('/admin/reminders/api', { params });
        
        console.log('API Response:', response.data);
        
        // Same pattern as AdminClasses - direct assignment
        if (response.data.reminders) {
          if (response.data.reminders.data) {
            this.remindersList = response.data.reminders.data;
            this.pagination = {
              current_page: response.data.reminders.current_page || 1,
              last_page: response.data.reminders.last_page || 1,
              per_page: response.data.reminders.per_page || 10,
              total: response.data.reminders.total || 0
            };
          } else {
            this.remindersList = response.data.reminders;
          }
          this.usersList = response.data.users || [];
          this.allUsers = response.data.users || [];
          this.tasksList = response.data.tasks || [];
          this.allTasks = response.data.tasks || [];
          this.stats = response.data.stats || this.stats;
          this.next24HoursList = response.data.next24HoursReminders || [];
        }
        
        console.log('Reminders loaded:', this.remindersList.length);
        console.log('Stats:', this.stats);
        
      } catch (error) {
        console.error('Error fetching reminders:', error);
        this.errorMessage = 'Failed to load reminders';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUsers() {
      try {
        const response = await axios.get('/admin/reminders/users');
        this.allUsers = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    
    async fetchTasks() {
      try {
        const response = await axios.get('/admin/reminders/tasks');
        this.allTasks = response.data;
      } catch (error) {
        console.error('Error fetching tasks:', error);
      }
    },
    
    clearFilters() {
      this.filters = { user_id: '', search: '' };
      this.fetchReminders();
    },
    
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchReminders();
    },
    
    openAddModal() {
      this.isEditing = false;
      this.form = {
        user_id: '',
        title: '',
        description: '',
        reminder_time: this.getDefaultDateTime(),
        task_id: ''
      };
      this.showModal = true;
    },
    
    openEditModal(reminder) {
      this.isEditing = true;
      this.editingId = reminder.id;
      this.form = {
        user_id: reminder.user_id,
        title: reminder.title,
        description: reminder.description || '',
        reminder_time: this.formatDateTimeLocal(reminder.reminder_time),
        task_id: reminder.task_id || ''
      };
      this.showModal = true;
    },
    
    async saveReminder() {
      this.saving = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/admin/reminders/${this.editingId}`, this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        } else {
          response = await axios.post('/admin/reminders', this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        }
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeModal();
          this.fetchReminders();
          setTimeout(() => this.successMessage = '', 3000);
        } else {
          this.errorMessage = response.data.message || 'Error saving reminder';
          setTimeout(() => this.errorMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error saving reminder';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(reminder) {
      this.deleteReminderId = reminder.id;
      this.deleteReminderTitle = reminder.title;
      this.showDeleteModal = true;
    },
    
    async deleteReminder() {
      this.deleting = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.delete(`/admin/reminders/${this.deleteReminderId}`, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeDeleteModal();
          this.fetchReminders();
          setTimeout(() => this.successMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error deleting reminder';
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
    
    isUpcoming(dateTime) {
      return new Date(dateTime) > new Date();
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A';
      return new Date(dateTime).toLocaleString();
    },
    
    formatDateTimeLocal(dateTime) {
      if (!dateTime) return '';
      const d = new Date(dateTime);
      return d.toISOString().slice(0, 16);
    },
    
    formatTime(dateTime) {
      if (!dateTime) return '';
      return new Date(dateTime).toLocaleTimeString();
    },
    
    getDefaultDateTime() {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      tomorrow.setHours(9, 0, 0, 0);
      return tomorrow.toISOString().slice(0, 16);
    },
    
    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    }
  }
};
</script>

<style scoped>
.admin-reminders {
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
  grid-template-columns: repeat(5, 1fr);
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

.next-hours-card {
  background: white;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.next-hours-card .card-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.next-hours-card .card-header h3 {
  margin: 0;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge {
  background: #4361ee;
  color: white;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 12px;
}

.next-hours-list {
  max-height: 200px;
  overflow-y: auto;
}

.next-hours-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px 20px;
  border-bottom: 1px solid #e2e8f0;
}

.next-hours-item:last-child {
  border-bottom: none;
}

.reminder-icon {
  width: 40px;
  height: 40px;
  background: #fef3c7;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f59e0b;
}

.reminder-info {
  flex: 1;
}

.reminder-title {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 4px;
}

.reminder-meta {
  display: flex;
  gap: 15px;
  font-size: 12px;
  color: #64748b;
}

.reminder-meta i {
  margin-right: 4px;
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

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  display: inline-block;
}

.status-upcoming {
  background: #d1fae5;
  color: #065f46;
}

.status-past {
  background: #f1f5f9;
  color: #64748b;
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

@media (max-width: 768px) {
  .admin-reminders { padding: 15px; }
  .filters-row { flex-direction: column; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
}
</style>