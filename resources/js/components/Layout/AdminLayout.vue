<template>
  <div id="admin-panel" class="visible">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="app-title">
        <i class="fas fa-shield-alt"></i>
        <span v-if="!sidebarCollapsed">PlanSync Admin</span>
      </div>
      
      <button class="sidebar-toggle" @click="toggleSidebar">
        <i :class="sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
      </button>
      
      <ul class="sidebar-menu">
        <li>
          <router-link to="/admin/dashboard" class="nav-link" :class="{ active: isActive('/admin/dashboard') }">
            <i class="fas fa-th-large"></i>
            <span v-if="!sidebarCollapsed">Dashboard</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/users" class="nav-link" :class="{ active: isActive('/admin/users') }">
            <i class="fas fa-users"></i>
            <span v-if="!sidebarCollapsed">Users</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/classes" class="nav-link" :class="{ active: isActive('/admin/classes') }">
            <i class="fas fa-book"></i>
            <span v-if="!sidebarCollapsed">Classes</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/tasks" class="nav-link" :class="{ active: isActive('/admin/tasks') }">
            <i class="fas fa-tasks"></i>
            <span v-if="!sidebarCollapsed">Tasks</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/notes" class="nav-link" :class="{ active: isActive('/admin/notes') }">
            <i class="fas fa-sticky-note"></i>
            <span v-if="!sidebarCollapsed">Notes</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/reminders" class="nav-link" :class="{ active: isActive('/admin/reminders') }">
            <i class="fas fa-bell"></i>
            <span v-if="!sidebarCollapsed">Reminders</span>
          </router-link>
        </li>
        <li>
          <router-link to="/admin/analytics" class="nav-link" :class="{ active: isActive('/admin/analytics') }">
            <i class="fas fa-chart-line"></i>
            <span v-if="!sidebarCollapsed">Analytics</span>
          </router-link>
        </li>
      </ul>
      
      <div class="sidebar-footer">
        <div class="system-info" v-if="!sidebarCollapsed">
          <div class="info-item">
            <i class="fas fa-code-branch"></i>
            <span>Version 1.0.0</span>
          </div>
          <div class="info-item">
            <i class="fas fa-database"></i>
            <span>Laravel 10.x</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" :class="{ expanded: sidebarCollapsed }">
      <!-- Header -->
      <header class="header">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="searchQuery"
            @keyup.enter="performSearch"
            placeholder="Search users, tasks, notes..."
          >
        </div>
        
        <div class="user-actions">
          <button class="notification-btn" @click="toggleNotifications">
            <i class="fas fa-bell"></i>
            <span v-if="notificationCount > 0" class="notification-badge">{{ notificationCount }}</span>
          </button>
          
          <div class="user-profile" @click="toggleUserMenu">
            <div class="user-avatar">
              {{ getUserInitials(adminUser.name) }}
            </div>
            <span class="user-name" v-if="!sidebarCollapsed">{{ adminUser.name }}</span>
            <i class="fas fa-chevron-down" v-if="!sidebarCollapsed"></i>
          </div>
          
          <button class="logout-btn" @click="handleLogout">
            <i class="fas fa-sign-out-alt"></i>
            <span v-if="!sidebarCollapsed">Logout</span>
          </button>
        </div>
        
        <!-- User Dropdown Menu -->
        <div v-if="showUserMenu" class="user-dropdown" @click.stop>
          <div class="dropdown-header">
            <div class="dropdown-avatar">
              {{ getUserInitials(adminUser.name) }}
            </div>
            <div class="dropdown-info">
              <div class="dropdown-name">{{ adminUser.name }}</div>
              <div class="dropdown-email">{{ adminUser.email }}</div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <router-link to="/admin/profile" class="dropdown-item">
            <i class="fas fa-user-circle"></i> Profile Settings
          </router-link>
          <button class="dropdown-item" @click="handleLogout">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
        </div>
        
        <!-- Notifications Dropdown -->
        <div v-if="showNotifications" class="notifications-dropdown" @click.stop>
          <div class="dropdown-header">
            <h4>Notifications</h4>
            <button class="mark-all" @click="markAllRead">Mark all as read</button>
          </div>
          <div class="notifications-list">
            <div v-for="notification in notifications" :key="notification.id" 
                 class="notification-item" :class="{ unread: !notification.read }">
              <div class="notification-icon" :class="notification.type">
                <i :class="'fas fa-' + notification.icon"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title">{{ notification.title }}</div>
                <div class="notification-message">{{ notification.message }}</div>
                <div class="notification-time">{{ timeAgo(notification.created_at) }}</div>
              </div>
            </div>
            <div v-if="notifications.length === 0" class="empty-notifications">
              <i class="fas fa-bell-slash"></i>
              <p>No notifications</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminLayout',
  data() {
    return {
      sidebarCollapsed: false,
      showUserMenu: false,
      showNotifications: false,
      searchQuery: '',
      adminUser: {
        name: 'Admin',
        email: 'admin@plansync.com'
      },
      notifications: [],
      notificationCount: 0,
      searchResults: []
    };
  },
  mounted() {
    this.loadAdminUser();
    this.loadNotifications();
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
      this.showUserMenu = false;
      this.showNotifications = false;
    });
    
    // Load saved sidebar state
    const savedState = localStorage.getItem('admin_sidebar_collapsed');
    if (savedState !== null) {
      this.sidebarCollapsed = savedState === 'true';
    }
  },
  methods: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
      localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
    },
    
    toggleUserMenu(event) {
      event.stopPropagation();
      this.showUserMenu = !this.showUserMenu;
      this.showNotifications = false;
    },
    
    toggleNotifications(event) {
      event.stopPropagation();
      this.showNotifications = !this.showNotifications;
      this.showUserMenu = false;
    },
    
    isActive(path) {
      return this.$route.path === path || this.$route.path.startsWith(path + '/');
    },
    
    async loadAdminUser() {
      try {
        // Get from localStorage first
        const storedUser = localStorage.getItem('admin_user');
        if (storedUser) {
          this.adminUser = JSON.parse(storedUser);
        }
        
        // Fetch fresh data from API
        const response = await axios.get('/admin/api/verify');
        if (response.data.user) {
          this.adminUser = response.data.user;
          localStorage.setItem('admin_user', JSON.stringify(this.adminUser));
        }
      } catch (error) {
        console.error('Error loading admin user:', error);
      }
    },
    
    async loadNotifications() {
      try {
        const response = await axios.get('/admin/api/notifications');
        this.notifications = response.data;
        this.notificationCount = this.notifications.filter(n => !n.read).length;
      } catch (error) {
        console.error('Error loading notifications:', error);
      }
    },
    
    async markAllRead() {
      try {
        await axios.post('/admin/api/notifications/mark-all-read');
        this.notifications.forEach(n => n.read = true);
        this.notificationCount = 0;
      } catch (error) {
        console.error('Error marking notifications:', error);
      }
    },
    
    async performSearch() {
      if (!this.searchQuery.trim()) return;
      
      try {
        const response = await axios.get('/admin/search/api', {
          params: { q: this.searchQuery }
        });
        
        // Navigate to search results page
        this.$router.push({
          name: 'admin.search',
          query: { q: this.searchQuery }
        });
      } catch (error) {
        console.error('Error performing search:', error);
      }
    },
    
    async handleLogout() {
      if (confirm('Are you sure you want to logout?')) {
        try {
          await axios.post('/admin/logout');
          localStorage.removeItem('admin_token');
          localStorage.removeItem('admin_user');
          localStorage.removeItem('admin_sidebar_collapsed');
          delete axios.defaults.headers.common['Authorization'];
          this.$router.push('/admin/login');
        } catch (error) {
          console.error('Error logging out:', error);
          // Force logout even if API fails
          localStorage.removeItem('admin_token');
          localStorage.removeItem('admin_user');
          this.$router.push('/admin/login');
        }
      }
    },
    
    getUserInitials(name) {
      if (!name) return 'AD';
      return name.substring(0, 2).toUpperCase();
    },
    
    timeAgo(date) {
      if (!date) return 'Recently';
      const seconds = Math.floor((new Date() - new Date(date)) / 1000);
      const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
      };
      
      for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
          return `${interval} ${unit}${interval === 1 ? '' : 's'} ago`;
        }
      }
      return 'Just now';
    }
  },
  
  watch: {
    '$route'() {
      // Close dropdowns on route change
      this.showUserMenu = false;
      this.showNotifications = false;
    }
  }
};
</script>

<style scoped>
#admin-panel {
  display: flex;
  min-height: 100vh;
  background: #f5f6fa;
}

/* Sidebar Styles */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  transition: all 0.3s ease;
  z-index: 100;
  overflow-y: auto;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar.collapsed .app-title span,
.sidebar.collapsed .sidebar-menu span,
.sidebar.collapsed .sidebar-footer {
  display: none;
}

.app-title {
  padding: 20px;
  font-size: 1.25rem;
  font-weight: 700;
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.app-title i {
  font-size: 1.5rem;
  color: #667eea;
}

.sidebar-toggle {
  position: absolute;
  right: -12px;
  top: 80px;
  width: 24px;
  height: 24px;
  background: #667eea;
  border: none;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all 0.3s ease;
  z-index: 101;
}

.sidebar-toggle:hover {
  background: #764ba2;
  transform: scale(1.1);
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu li {
  margin-bottom: 5px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-link.active {
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-left-color: white;
}

.nav-link i {
  width: 20px;
  font-size: 1.1rem;
}

.sidebar-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

/* Main Content Styles */
.main-content {
  flex: 1;
  margin-left: 260px;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.main-content.expanded {
  margin-left: 70px;
}

/* Header Styles */
.header {
  background: white;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 99;
}

.search-box {
  display: flex;
  align-items: center;
  background: #f5f6fa;
  padding: 8px 15px;
  border-radius: 8px;
  width: 300px;
}

.search-box i {
  color: #a0aec0;
  margin-right: 10px;
}

.search-box input {
  border: none;
  background: none;
  outline: none;
  width: 100%;
  font-size: 0.9rem;
}

.user-actions {
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
}

.notification-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #4a5568;
  cursor: pointer;
  position: relative;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.notification-btn:hover {
  background: #f5f6fa;
  color: #667eea;
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #e53e3e;
  color: white;
  font-size: 0.7rem;
  padding: 2px 5px;
  border-radius: 10px;
  min-width: 18px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.user-profile:hover {
  background: #f5f6fa;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1rem;
}

.user-name {
  font-weight: 500;
  color: #2d3748;
}

.logout-btn {
  background: #e53e3e;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: #c53030;
  transform: translateY(-1px);
}

/* Dropdown Menus */
.user-dropdown,
.notifications-dropdown {
  position: absolute;
  top: 60px;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 320px;
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  gap: 15px;
}

.dropdown-avatar {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: bold;
}

.dropdown-info {
  flex: 1;
}

.dropdown-name {
  font-weight: 600;
  margin-bottom: 4px;
}

.dropdown-email {
  font-size: 0.8rem;
  opacity: 0.9;
}

.dropdown-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 8px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #4a5568;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  border: none;
  background: white;
  font-size: 0.9rem;
}

.dropdown-item:hover {
  background: #f7fafc;
  color: #667eea;
}

/* Notifications */
.notifications-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 15px 20px;
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.notification-item:hover {
  background: #f7fafc;
}

.notification-item.unread {
  background: #ebf8ff;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.notification-icon.success { background: #48bb78; }
.notification-icon.primary { background: #4299e1; }
.notification-icon.warning { background: #ed8936; }
.notification-icon.danger { background: #e53e3e; }

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 4px;
}

.notification-message {
  font-size: 0.85rem;
  color: #718096;
  margin-bottom: 4px;
}

.notification-time {
  font-size: 0.7rem;
  color: #a0aec0;
}

.mark-all {
  background: none;
  border: none;
  color: #667eea;
  font-size: 0.8rem;
  cursor: pointer;
}

.empty-notifications {
  text-align: center;
  padding: 40px;
  color: #a0aec0;
}

.empty-notifications i {
  font-size: 3rem;
  margin-bottom: 10px;
  opacity: 0.5;
}

/* Content Area */
.content {
  padding: 30px;
  min-height: calc(100vh - 70px);
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  
  .sidebar.mobile-open {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .header {
    padding: 15px 20px;
  }
  
  .search-box {
    width: 200px;
  }
  
  .user-name {
    display: none;
  }
  
  .content {
    padding: 20px;
  }
}

/* Custom Scrollbar */
.sidebar::-webkit-scrollbar {
  width: 5px;
}

.sidebar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 5px;
}

.notifications-list::-webkit-scrollbar {
  width: 5px;
}

.notifications-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notifications-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 5px;
}
</style>