<template>
  <div class="admin-users">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-users"></i> Users</h2>
      <button class="btn btn-primary" @click="openUserModal">
        <i class="fas fa-plus"></i> Add User
      </button>
    </div>

    <!-- Alert Messages -->
    <div v-if="successMessage" class="alert alert-success">
      <i class="fas fa-check-circle"></i> {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
    </div>

    <!-- Search -->
    <div class="search-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          v-model="filters.search" 
          placeholder="Search users by name or email..." 
          @keyup.enter="fetchUsers"
        >
        <button class="btn btn-primary" @click="fetchUsers">Search</button>
        <button v-if="filters.search" class="btn btn-secondary" @click="clearSearch">Clear</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading users...</p>
    </div>

    <!-- Users Table -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Classes</th>
            <th>Tasks</th>
            <th>Notes</th>
            <th>Reminders</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="usersList.length === 0">
            <td colspan="9" class="empty-state">
              <i class="fas fa-user-slash"></i>
              <p>No users found</p>
            </td>
          </tr>
          <tr v-for="user in usersList" :key="user.id">
            <td>#{{ user.id }}</td>
            <td>
              <div class="user-name">
                <div class="user-avatar" :style="{ backgroundColor: user.avatar_color || '#4361ee' }">
                  <img 
                    v-if="user.profile_image" 
                    :src="getProfileImageUrl(user.profile_image)" 
                    :alt="user.name"
                    class="avatar-image"
                  >
                  <span v-else class="avatar-initials">{{ getUserInitials(user.name) }}</span>
                </div>
                {{ user.name }}
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td><span class="badge bg-info">{{ user.class_schedules_count || 0 }}</span></td>
            <td><span class="badge bg-warning">{{ user.tasks_count || 0 }}</span></td>
            <td><span class="badge bg-success">{{ user.notes_count || 0 }}</span></td>
            <td><span class="badge bg-danger">{{ user.reminders_count || 0 }}</span></td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-sm btn-info" @click="viewUser(user)" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" @click="editUser(user)" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" @click="confirmDelete(user)" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- User Modal (Add/Edit) -->
    <div class="modal" :class="{ show: showUserModal }" @click.self="closeUserModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4>{{ isEditing ? 'Edit User' : 'Add New User' }}</h4>
            <button type="button" class="close" @click="closeUserModal">&times;</button>
          </div>
          <form @submit.prevent="saveUser">
            <div class="modal-body">
              <div class="form-group">
                <label>Full Name *</label>
                <input type="text" v-model="form.name" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" v-model="form.email" class="form-control" required>
              </div>
              <div class="form-group">
                <label>{{ isEditing ? 'New Password (leave blank to keep current)' : 'Password *' }}</label>
                <input type="password" v-model="form.password" class="form-control" :required="!isEditing">
              </div>
              <div class="form-group" v-if="!isEditing || form.password">
                <label>Confirm Password</label>
                <input type="password" v-model="form.password_confirmation" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeUserModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div class="modal" :class="{ show: showViewModal }" @click.self="closeViewModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4>User Details</h4>
            <button type="button" class="close" @click="closeViewModal">&times;</button>
          </div>
          <div class="modal-body" v-if="viewingUser">
            <div class="user-detail-header">
              <div class="user-detail-avatar" :style="{ backgroundColor: viewingUser.avatar_color || '#4361ee' }">
                <img 
                  v-if="viewingUser.profile_image" 
                  :src="getProfileImageUrl(viewingUser.profile_image)" 
                  :alt="viewingUser.name"
                  class="avatar-image-large"
                >
                <span v-else class="avatar-initials-large">{{ getUserInitials(viewingUser.name) }}</span>
              </div>
              <div>
                <h3>{{ viewingUser.name }}</h3>
                <p>{{ viewingUser.email }}</p>
              </div>
            </div>
            <div class="user-detail-stats">
              <div class="stat-item">
                <div class="stat-value">{{ viewingUser.class_schedules_count || 0 }}</div>
                <div class="stat-label">Classes</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ viewingUser.tasks_count || 0 }}</div>
                <div class="stat-label">Tasks</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ viewingUser.notes_count || 0 }}</div>
                <div class="stat-label">Notes</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ viewingUser.reminders_count || 0 }}</div>
                <div class="stat-label">Reminders</div>
              </div>
            </div>
            <div class="user-detail-info">
              <p><strong>Joined:</strong> {{ formatDate(viewingUser.created_at) }}</p>
              <p><strong>Last Updated:</strong> {{ formatDate(viewingUser.updated_at) }}</p>
              <p><strong>Avatar Color:</strong> <span :style="{ backgroundColor: viewingUser.avatar_color, display: 'inline-block', width: '20px', height: '20px', borderRadius: '4px', verticalAlign: 'middle' }"></span> {{ viewingUser.avatar_color || '#4361ee' }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeViewModal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" :class="{ show: showDeleteModal }" @click.self="closeDeleteModal">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h4>Confirm Delete</h4>
            <button type="button" class="close" @click="closeDeleteModal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete user <strong>"{{ deleteUserName }}"</strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteUser" :disabled="deleting">
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
  name: 'AdminUsers',
  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      usersList: [],
      filters: {
        search: ''
      },
      showUserModal: false,
      showViewModal: false,
      showDeleteModal: false,
      isEditing: false,
      editingId: null,
      viewingUser: null,
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      deleteUserId: null,
      deleteUserName: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      try {
        const params = {
          search: this.filters.search
        };
        const response = await axios.get('/admin/users/api', { params });
        
        console.log('Users API Response:', response.data);
        
        if (response.data.users) {
          this.usersList = response.data.users;
        } else if (Array.isArray(response.data)) {
          this.usersList = response.data;
        } else {
          this.usersList = [];
        }
        
        console.log('Users loaded:', this.usersList.length);
        
      } catch (error) {
        console.error('Error fetching users:', error);
        this.errorMessage = 'Failed to load users';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.loading = false;
      }
    },
    
    clearSearch() {
      this.filters.search = '';
      this.fetchUsers();
    },
    
    getProfileImageUrl(path) {
      if (!path) return null;
      // If path already starts with /storage, return as is
      if (path.startsWith('/storage')) {
        return path;
      }
      // Otherwise, prepend /storage
      return '/storage/' + path;
    },
    
    getUserInitials(name) {
      if (!name) return 'NA';
      return name.substring(0, 2).toUpperCase();
    },
    
    openUserModal() {
      this.isEditing = false;
      this.editingId = null;
      this.form = {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      };
      this.showUserModal = true;
    },
    
    editUser(user) {
      this.isEditing = true;
      this.editingId = user.id;
      this.form = {
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: ''
      };
      this.showUserModal = true;
    },
    
    viewUser(user) {
      this.viewingUser = user;
      this.showViewModal = true;
    },
    
    async saveUser() {
      this.saving = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`/admin/users/${this.editingId}`, this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        } else {
          response = await axios.post('/admin/users', this.form, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
          });
        }
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeUserModal();
          this.fetchUsers();
          setTimeout(() => this.successMessage = '', 3000);
        } else {
          this.errorMessage = response.data.message || 'Error saving user';
          setTimeout(() => this.errorMessage = '', 3000);
        }
      } catch (error) {
        console.error('Error saving user:', error);
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          this.errorMessage = Object.values(errors).flat()[0];
        } else {
          this.errorMessage = error.response?.data?.message || 'Error saving user';
        }
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(user) {
      this.deleteUserId = user.id;
      this.deleteUserName = user.name;
      this.showDeleteModal = true;
    },
    
    async deleteUser() {
      this.deleting = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.delete(`/admin/users/${this.deleteUserId}`, {
          headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeDeleteModal();
          this.fetchUsers();
          setTimeout(() => this.successMessage = '', 3000);
        }
      } catch (error) {
        console.error('Error deleting user:', error);
        this.errorMessage = error.response?.data?.message || 'Error deleting user';
        setTimeout(() => this.errorMessage = '', 3000);
      } finally {
        this.deleting = false;
      }
    },
    
    closeUserModal() {
      this.showUserModal = false;
    },
    
    closeViewModal() {
      this.showViewModal = false;
      this.viewingUser = null;
    },
    
    closeDeleteModal() {
      this.showDeleteModal = false;
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      const d = new Date(date);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
  }
};
</script>

<style scoped>
.admin-users {
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

.search-section {
  margin-bottom: 20px;
}

.search-box {
  display: flex;
  gap: 10px;
  max-width: 500px;
  position: relative;
}

.search-box input {
  flex: 1;
  padding: 10px 12px 10px 35px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 12px;
  color: #94a3b8;
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

.user-name {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initials {
  font-size: 12px;
  font-weight: bold;
}

.avatar-image-large {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initials-large {
  font-size: 24px;
  font-weight: bold;
}

.badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.bg-info { background: #06b6d4; color: white; }
.bg-warning { background: #f59e0b; color: white; }
.bg-success { background: #10b981; color: white; }
.bg-danger { background: #ef4444; color: white; }

.action-buttons {
  display: flex;
  gap: 5px;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-primary { background: #4361ee; color: white; }
.btn-primary:hover { background: #3451d1; }
.btn-secondary { background: #64748b; color: white; }
.btn-secondary:hover { background: #475569; }
.btn-info { background: #06b6d4; color: white; }
.btn-info:hover { background: #0891b2; }
.btn-warning { background: #f59e0b; color: white; }
.btn-warning:hover { background: #d97706; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }
.btn-sm { padding: 4px 8px; font-size: 12px; }

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

.modal-lg {
  max-width: 600px;
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

.user-detail-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.user-detail-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  font-weight: bold;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.user-detail-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
  margin-bottom: 20px;
}

.user-detail-stats .stat-item {
  text-align: center;
  padding: 15px;
  background: #f8fafc;
  border-radius: 8px;
}

.user-detail-stats .stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #4361ee;
}

.user-detail-stats .stat-label {
  color: #64748b;
  margin-top: 5px;
}

.user-detail-info p {
  margin: 5px 0;
}

@media (max-width: 768px) {
  .admin-users { padding: 15px; }
  .search-box { max-width: 100%; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
  .user-detail-stats { grid-template-columns: repeat(2, 1fr); }
  .action-buttons { flex-wrap: wrap; }
}
</style>