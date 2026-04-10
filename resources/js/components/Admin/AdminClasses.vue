<template>
  <div class="admin-classes">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-book"></i> Classes</h2>
      <button class="btn btn-primary" @click="openAddModal">
        <i class="fas fa-plus"></i> Add Class
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
          <label>Day</label>
          <select v-model="filters.day" @change="fetchClasses">
            <option value="">All Days</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
          </select>
        </div>
        <div class="filter-group">
          <label>User</label>
          <select v-model="filters.user_id" @change="fetchClasses">
            <option value="">All Users</option>
            <option v-for="user in usersList" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Search</label>
          <input type="text" v-model="filters.search" placeholder="Search..." @keyup.enter="fetchClasses">
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
        <i class="fas fa-calendar-alt"></i>
        <div class="stat-number">{{ stats.totalClasses || 0 }}</div>
        <div class="stat-label">Total Classes</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-day"></i>
        <div class="stat-number">{{ stats.todayClasses || 0 }}</div>
        <div class="stat-label">Today's Classes</div>
      </div>
      <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-number">{{ stats.uniqueUsers || 0 }}</div>
        <div class="stat-label">Active Users</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading classes...</p>
    </div>

    <!-- Classes Table -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Class Name</th>
            <th>Day</th>
            <th>Time</th>
            <th>Location</th>
            <th>User</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="classesList.length === 0">
            <td colspan="7" class="empty-state">
              <i class="fas fa-calendar-times"></i>
              <p>No classes found</p>
            </td>
          </tr>
          <tr v-for="classItem in classesList" :key="classItem.id">
            <td>{{ classItem.id }}</td>
            <td><strong>{{ classItem.name }}</strong></td>
            <td>{{ classItem.day }}</td>
            <td>{{ classItem.time }}</td>
            <td>{{ classItem.location }}</td>
            <td>{{ classItem.user?.name || 'Unknown' }}</td>
            <td>
              <button class="btn btn-sm btn-warning" @click="openEditModal(classItem)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(classItem)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" :class="{ show: showModal }" @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4>{{ isEditing ? 'Edit Class' : 'Add New Class' }}</h4>
            <button type="button" class="close" @click="closeModal">&times;</button>
          </div>
          <form @submit.prevent="saveClass">
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
                <label>Class Name *</label>
                <input type="text" v-model="form.name" class="form-control" required>
              </div>
              <div class="form-row">
                <div class="form-group half">
                  <label>Day *</label>
                  <select v-model="form.day" class="form-control" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                  </select>
                </div>
                <div class="form-group half">
                  <label>Time *</label>
                  <input type="text" v-model="form.time" class="form-control" required>
                </div>
              </div>
              <div class="form-group">
                <label>Location *</label>
                <input type="text" v-model="form.location" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Color</label>
                <input type="color" v-model="form.color" class="color-picker">
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
            <p>Delete class <strong>"{{ deleteClassName }}"</strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteClass" :disabled="deleting">
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
  name: 'AdminClasses',
  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      classesList: [],
      usersList: [],
      allUsers: [],
      stats: {
        totalClasses: 0,
        todayClasses: 0,
        uniqueUsers: 0
      },
      filters: {
        day: '',
        user_id: '',
        search: ''
      },
      showModal: false,
      showDeleteModal: false,
      isEditing: false,
      editingId: null,
      form: {
        user_id: '',
        name: '',
        day: 'Monday',
        time: '',
        location: '',
        color: '#4361ee'
      },
      deleteClassId: null,
      deleteClassName: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchClasses();
    this.fetchUsers();
  },
  methods: {
    async fetchClasses() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/classes/api', {
          params: {
            day: this.filters.day,
            user_id: this.filters.user_id,
            search: this.filters.search
          }
        });
        
        // Direct assignment - same pattern as dashboard
        this.classesList = response.data.classes || [];
        this.usersList = response.data.users || [];
        this.allUsers = response.data.users || [];
        this.stats = response.data.stats || this.stats;
        
      } catch (error) {
        console.error('Error:', error);
        this.errorMessage = 'Failed to load classes';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUsers() {
      try {
        const response = await axios.get('/admin/classes/list');
        this.allUsers = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    
    clearFilters() {
      this.filters = { day: '', user_id: '', search: '' };
      this.fetchClasses();
    },
    
    openAddModal() {
      this.isEditing = false;
      this.form = {
        user_id: '',
        name: '',
        day: 'Monday',
        time: '',
        location: '',
        color: '#4361ee'
      };
      this.showModal = true;
    },
    
    openEditModal(classItem) {
      this.isEditing = true;
      this.editingId = classItem.id;
      this.form = {
        user_id: classItem.user_id,
        name: classItem.name,
        day: classItem.day,
        time: classItem.time,
        location: classItem.location,
        color: classItem.color || '#4361ee'
      };
      this.showModal = true;
    },
    
    async saveClass() {
      this.saving = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/admin/classes/${this.editingId}`, this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        } else {
          response = await axios.post('/admin/classes', this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        }
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeModal();
          this.fetchClasses();
          setTimeout(() => this.successMessage = '', 3000);
        } else {
          this.errorMessage = response.data.message || 'Error saving class';
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error saving class';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(classItem) {
      this.deleteClassId = classItem.id;
      this.deleteClassName = classItem.name;
      this.showDeleteModal = true;
    },
    
    async deleteClass() {
      this.deleting = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.delete(`/admin/classes/${this.deleteClassId}`, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeDeleteModal();
          this.fetchClasses();
          setTimeout(() => this.successMessage = '', 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error deleting class';
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
    }
  }
};
</script>

<style scoped>
.admin-classes { padding: 20px; background: #f8fafc; min-height: 100vh; }
.module-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
.module-title { margin: 0; font-size: 24px; color: #1e293b; }
.module-title i { color: #4361ee; margin-right: 10px; }

.filters-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.filters-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group label { display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; }
.filter-group select, .filter-group input { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
.stat-card { background: white; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.stat-card i { font-size: 28px; color: #4361ee; margin-bottom: 10px; }
.stat-number { font-size: 28px; font-weight: bold; color: #1e293b; }
.stat-label { color: #64748b; margin-top: 5px; font-size: 13px; }

.table-container { background: white; border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 12px 15px; text-align: left; background: #f8fafc; font-weight: 600; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
.data-table td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }

.btn { padding: 8px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
.btn-primary { background: #4361ee; color: white; }
.btn-primary:hover { background: #3451d1; }
.btn-secondary { background: #64748b; color: white; }
.btn-secondary:hover { background: #475569; }
.btn-warning { background: #f59e0b; color: white; }
.btn-warning:hover { background: #d97706; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }
.btn-sm { padding: 4px 8px; font-size: 12px; margin: 0 2px; }

.loading-state { text-align: center; padding: 40px; }
.spinner { width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #4361ee; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; padding: 40px; color: #64748b; }
.empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }

.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal.show { display: flex; }
.modal-dialog { width: 90%; max-width: 500px; background: white; border-radius: 12px; overflow: hidden; }
.modal-sm { max-width: 400px; }
.modal-header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
.modal-header h4 { margin: 0; }
.modal-body { padding: 20px; }
.modal-footer { padding: 15px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; background: #f8fafc; }
.close { background: none; border: none; font-size: 24px; cursor: pointer; color: #94a3b8; }
.close:hover { color: #ef4444; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #1e293b; font-size: 14px; }
.form-control { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
.form-control:focus { outline: none; border-color: #4361ee; }
.form-row { display: flex; gap: 15px; }
.half { flex: 1; }
.color-picker { width: 50px; height: 40px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; }

.alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.text-danger { color: #dc2626; font-size: 12px; margin-top: 5px; }

@media (max-width: 768px) {
  .admin-classes { padding: 15px; }
  .filters-row { flex-direction: column; }
  .stats-grid { grid-template-columns: 1fr; gap: 10px; }
  .form-row { flex-direction: column; gap: 0; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
}
</style>