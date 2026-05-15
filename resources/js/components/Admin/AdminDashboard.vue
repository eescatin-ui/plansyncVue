<template>
  <div class="admin-dashboard">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-th-large"></i> Dashboard</h2>
      <div class="header-actions">
        <button class="btn btn-small" @click="refreshDashboard" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading dashboard data...</p>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card" @click="navigateTo('users')">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ formatNumber(stats.totalUsers) }}</div>
            <div class="stat-label">Total Users</div>
            <div class="stat-trend" v-if="stats.newUsersThisMonth">
              <i class="fas fa-arrow-up"></i>
              +{{ stats.newUsersThisMonth }} this month
            </div>
          </div>
        </div>

        <div class="stat-card" @click="navigateTo('tasks')">
          <div class="stat-icon">
            <i class="fas fa-tasks"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ formatNumber(stats.totalTasks) }}</div>
            <div class="stat-label">Total Tasks</div>
            <div class="stat-trend">
              <span class="completed">{{ stats.completedTasks || 0 }} completed</span>
            </div>
          </div>
        </div>

        <div class="stat-card" @click="navigateTo('notes')">
          <div class="stat-icon">
            <i class="fas fa-sticky-note"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ formatNumber(stats.totalNotes) }}</div>
            <div class="stat-label">Total Notes</div>
          </div>
        </div>

        <div class="stat-card" @click="navigateTo('reminders')">
          <div class="stat-icon">
            <i class="fas fa-bell"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ formatNumber(stats.totalReminders) }}</div>
            <div class="stat-label">Total Reminders</div>
          </div>
        </div>

        <div class="stat-card" @click="navigateTo('classes')">
          <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ formatNumber(stats.totalClasses) }}</div>
            <div class="stat-label">Total Classes</div>
          </div>
        </div>
      </div>

      <!-- Recent Activity Grid -->
      <div class="dashboard-grid">
        <!-- Recent Users -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-users"></i> Recent Users</h3>
            <button class="btn-text" @click="navigateTo('users')">View All →</button>
          </div>
          <div class="card-body">
            <div v-if="recentUsersLoading" class="skeleton-loader">
              <div v-for="i in 3" :key="i" class="skeleton-item"></div>
            </div>
            <div v-else-if="recentUsers.length === 0" class="empty-state">
              <i class="fas fa-user-slash"></i>
              <p>No users found</p>
            </div>
            <div v-else class="user-list">
              <div v-for="user in recentUsers" :key="user.id" class="user-item">
<div class="user-avatar" :style="{ backgroundColor: user.avatar_color || '#4361ee' }">
  <img 
    v-if="user.profile_image" 
    :src="getProfileImageUrl(user.profile_image)" 
    :alt="user.name"
    class="avatar-img"
  >
  <span v-else>{{ getUserInitials(user.name) }}</span>
</div>
                <div class="user-info">
                  <div class="user-name">{{ user.name }}</div>
                  <div class="user-email">{{ user.email }}</div>
                  <div class="user-date">Joined {{ formatDate(user.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Tasks -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Recent Tasks</h3>
            <button class="btn-text" @click="navigateTo('tasks')">View All →</button>
          </div>
          <div class="card-body">
            <div v-if="recentTasksLoading" class="skeleton-loader">
              <div v-for="i in 3" :key="i" class="skeleton-item"></div>
            </div>
            <div v-else-if="recentTasks.length === 0" class="empty-state">
              <i class="fas fa-check-circle"></i>
              <p>No tasks found</p>
            </div>
            <div v-else class="task-list">
              <div v-for="task in recentTasks" :key="task.id" class="task-item">
                <div class="task-status" :class="task.status"></div>
                <div class="task-info">
                  <div class="task-title">{{ task.title }}</div>
                  <div class="task-meta">
                    <span class="task-user">
                      <i class="fas fa-user"></i> {{ task.user?.name || 'Unknown' }}
                    </span>
                    <span class="task-date">
                      <i class="fas fa-calendar"></i> Due: {{ formatDate(task.due_date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Status Summary -->
      <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
          <h3><i class="fas fa-chart-pie"></i> Task Status Overview</h3>
        </div>
        <div class="card-body">
          <div class="task-stats">
            <div class="task-stat-item">
              <div class="task-stat-label">
                <span class="status-dot pending"></span>
                Pending
              </div>
              <div class="task-stat-bar">
                <div class="task-stat-progress" :style="{ width: getTaskPercentage('pending') + '%', background: '#ffc107' }"></div>
              </div>
              <div class="task-stat-count">{{ stats.pendingTasks || 0 }}</div>
            </div>
            <div class="task-stat-item">
              <div class="task-stat-label">
                <span class="status-dot in-progress"></span>
                In Progress
              </div>
              <div class="task-stat-bar">
                <div class="task-stat-progress" :style="{ width: getTaskPercentage('inProgress') + '%', background: '#fd7e14' }"></div>
              </div>
              <div class="task-stat-count">{{ stats.inProgressTasks || 0 }}</div>
            </div>
            <div class="task-stat-item">
              <div class="task-stat-label">
                <span class="status-dot completed"></span>
                Completed
              </div>
              <div class="task-stat-bar">
                <div class="task-stat-progress" :style="{ width: getTaskPercentage('completed') + '%', background: '#28a745' }"></div>
              </div>
              <div class="task-stat-count">{{ stats.completedTasks || 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminDashboard',
  data() {
    return {
      loading: true,
      recentUsersLoading: true,
      recentTasksLoading: true,
      stats: {
        totalUsers: 0,
        totalClasses: 0,
        totalTasks: 0,
        totalNotes: 0,
        totalReminders: 0,
        newUsersThisMonth: 0,
        pendingTasks: 0,
        inProgressTasks: 0,
        completedTasks: 0
      },
      recentUsers: [],
      recentTasks: []
    };
  },
  mounted() {
    this.loadDashboard();
    this.loadRecentUsers();
    this.loadRecentTasks();
  },
  methods: {
    async loadDashboard() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/api/dashboard/stats');
        if (response.data.success) {
          this.stats = response.data.data;
        } else {
          console.error('Failed to load stats:', response.data.message);
        }
      } catch (error) {
        console.error('Error loading dashboard stats:', error);
        this.showError('Failed to load dashboard statistics');
      } finally {
        this.loading = false;
      }
    },
    
    async loadRecentUsers() {
      this.recentUsersLoading = true;
      try {
        const response = await axios.get('/admin/api/dashboard/recent-users');
        if (response.data.success) {
          this.recentUsers = response.data.data;
        }
      } catch (error) {
        console.error('Error loading recent users:', error);
      } finally {
        this.recentUsersLoading = false;
      }
    },
    
    async loadRecentTasks() {
      this.recentTasksLoading = true;
      try {
        const response = await axios.get('/admin/api/dashboard/recent-tasks');
        if (response.data.success) {
          this.recentTasks = response.data.data;
        }
      } catch (error) {
        console.error('Error loading recent tasks:', error);
      } finally {
        this.recentTasksLoading = false;
      }
    },
    
    async refreshDashboard() {
      await Promise.all([
        this.loadDashboard(),
        this.loadRecentUsers(),
        this.loadRecentTasks()
      ]);
    },

    getProfileImageUrl(path) {
  if (!path) return null;
  if (path.startsWith('/storage')) {
    return path;
  }
  return '/storage/' + path;
},
    
    getTaskPercentage(status) {
      const total = this.stats.totalTasks || 0;
      if (total === 0) return 0;
      
      let count = 0;
      switch(status) {
        case 'pending':
          count = this.stats.pendingTasks || 0;
          break;
        case 'inProgress':
          count = this.stats.inProgressTasks || 0;
          break;
        case 'completed':
          count = this.stats.completedTasks || 0;
          break;
      }
      return (count / total) * 100;
    },
    
    navigateTo(section) {
      this.$router.push(`/admin/${section}`);
    },
    
    formatNumber(num) {
      if (!num) return '0';
      return num.toLocaleString();
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      const d = new Date(date);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    
    getUserInitials(name) {
      if (!name) return 'NA';
      return name.substring(0, 2).toUpperCase();
    },
    
    getAvatarColor(name) {
      const colors = ['#4361ee', '#7209b7', '#f72585', '#4cc9f0', '#3a0ca3', '#ff6d00'];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    },
    
    showError(message) {
      console.error(message);
      // You can implement a toast notification here
      alert(message);
    }
  }
};
</script>

<style scoped>
.admin-dashboard {
  padding: 1.5rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: white;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
  margin-top: 0.25rem;
}

.stat-trend {
  font-size: 0.75rem;
  color: #48bb78;
  margin-top: 0.5rem;
}

.stat-trend i {
  font-size: 0.7rem;
}

.stat-trend .completed {
  color: #718096;
}

/* Dashboard Grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

/* Cards */
.card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.125rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-header h3 i {
  color: #667eea;
}

.btn-text {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-text:hover {
  text-decoration: underline;
}

.card-body {
  padding: 1rem;
  max-height: 400px;
  overflow-y: auto;
}

/* User List */
.user-list, .task-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.user-item, .task-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-radius: 12px;
  transition: all 0.2s;
}

.user-item:hover, .task-item:hover {
  background: #f7fafc;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1rem;
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 4px;
}

.user-email {
  font-size: 0.8rem;
  color: #718096;
  margin-bottom: 2px;
}

.user-date {
  font-size: 0.7rem;
  color: #a0aec0;
}

/* Task Item */
.task-status {
  width: 4px;
  height: 40px;
  border-radius: 2px;
  flex-shrink: 0;
}

.task-status.todo { background: #ffc107; }
.task-status.inprogress { background: #fd7e14; }
.task-status.done { background: #28a745; }

.task-info {
  flex: 1;
}

.task-title {
  font-weight: 500;
  color: #2d3748;
  margin-bottom: 6px;
}

.task-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.7rem;
  color: #718096;
}

.task-meta i {
  margin-right: 4px;
}

/* Task Stats */
.task-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.task-stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.task-stat-label {
  width: 100px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  color: #4a5568;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.status-dot.pending { background: #ffc107; }
.status-dot.in-progress { background: #fd7e14; }
.status-dot.completed { background: #28a745; }

.task-stat-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.task-stat-progress {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.task-stat-count {
  width: 50px;
  text-align: right;
  font-weight: 600;
  color: #2d3748;
}

/* Loading States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.skeleton-loader {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.skeleton-item {
  height: 60px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 8px;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #a0aec0;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-dashboard {
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .task-stat-item {
    flex-wrap: wrap;
  }
  
  .task-stat-label {
    width: 100%;
  }
}
</style>