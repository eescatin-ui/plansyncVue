<template>
  <div class="admin-search">
    <div class="module-header">
      <h2 class="module-title"><i class="fas fa-search"></i> Search Results</h2>
      <div class="search-box" style="width: 400px;">
        <form @submit.prevent="performSearch" style="display: flex; width: 100%;">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search across all data..." 
            style="border: none; outline: none; width: 100%;"
            autofocus
          >
        </form>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin fa-2x"></i>
      <p>Searching...</p>
    </div>

    <!-- Empty Search State -->
    <div v-else-if="!hasSearched" class="empty-state">
      <i class="fas fa-search"></i>
      <p>Enter a search term to find users, classes, tasks, notes, or reminders</p>
    </div>

    <!-- No Results State -->
    <div v-else-if="totalResults === 0" class="empty-state">
      <i class="fas fa-search-minus"></i>
      <p>No results found for "{{ searchQuery }}"</p>
      <p style="color: var(--gray); margin-top: 0.5rem;">Try different keywords</p>
    </div>

    <!-- Results -->
    <div v-else>
      <div style="margin-bottom: 2rem;">
        <h3 style="color: var(--gray); margin-bottom: 1rem;">
          Found {{ totalResults }} results for "{{ searchQuery }}"
        </h3>

        <!-- Users Results -->
        <div v-if="results.users && results.users.length > 0" class="card" style="margin-bottom: 1.5rem;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-users"></i> Users ({{ results.users.length }})
            </h3>
          </div>
          <div class="card-body">
            <div class="activity-list">
              <div 
                v-for="user in results.users" 
                :key="'user-' + user.id" 
                class="activity-item" 
                @click="navigateTo('users', user.id)"
              >
                <div class="user-avatar" :style="{ backgroundColor: getAvatarColor(user.name) }">
                  {{ getUserInitials(user.name) }}
                </div>
                <div>
                  <strong>{{ user.name }}</strong>
                  <p>{{ user.email }}</p>
                  <small>Joined {{ timeAgo(user.created_at) }}</small>
                </div>
                <div style="margin-left: auto;">
                  <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Classes Results -->
        <div v-if="results.classes && results.classes.length > 0" class="card" style="margin-bottom: 1.5rem;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-book"></i> Classes ({{ results.classes.length }})
            </h3>
          </div>
          <div class="card-body">
            <div class="activity-list">
              <div 
                v-for="classItem in results.classes" 
                :key="'class-' + classItem.id" 
                class="activity-item" 
                @click="navigateTo('classes', classItem.id)"
              >
                <i class="fas fa-book" style="color: var(--accent);"></i>
                <div>
                  <strong>{{ classItem.name }}</strong>
                  <p>{{ classItem.location }} • {{ classItem.day }} {{ formatTime(classItem.time) }}</p>
                  <small>By {{ classItem.user?.name || 'Unknown' }}</small>
                </div>
                <div style="margin-left: auto;">
                  <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tasks Results -->
        <div v-if="results.tasks && results.tasks.length > 0" class="card" style="margin-bottom: 1.5rem;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-tasks"></i> Tasks ({{ results.tasks.length }})
            </h3>
          </div>
          <div class="card-body">
            <div class="activity-list">
              <div 
                v-for="task in results.tasks" 
                :key="'task-' + task.id" 
                class="activity-item" 
                @click="navigateTo('tasks', task.id)"
              >
                <i class="fas fa-tasks" :style="{ color: getTaskStatusColor(task.status) }"></i>
                <div>
                  <strong>{{ task.title }}</strong>
                  <p>Due: {{ formatDate(task.due_date) }}</p>
                  <small>
                    Status: 
                    <span :class="['status-badge', 'status-' + task.status]">
                      {{ getStatusText(task.status) }}
                    </span>
                    • By {{ task.user?.name || 'Unknown' }}
                  </small>
                </div>
                <div style="margin-left: auto;">
                  <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Results -->
        <div v-if="results.notes && results.notes.length > 0" class="card" style="margin-bottom: 1.5rem;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-sticky-note"></i> Notes ({{ results.notes.length }})
            </h3>
          </div>
          <div class="card-body">
            <div class="activity-list">
              <div 
                v-for="note in results.notes" 
                :key="'note-' + note.id" 
                class="activity-item" 
                @click="navigateTo('notes', note.id)"
              >
                <i class="fas fa-sticky-note" style="color: var(--success);"></i>
                <div>
                  <strong>{{ note.title }}</strong>
                  <p>{{ truncate(note.content, 80) }}</p>
                  <small>By {{ note.user?.name || 'Unknown' }} • {{ formatDate(note.created_at) }}</small>
                </div>
                <div style="margin-left: auto;">
                  <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reminders Results -->
        <div v-if="results.reminders && results.reminders.length > 0" class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-bell"></i> Reminders ({{ results.reminders.length }})
            </h3>
          </div>
          <div class="card-body">
            <div class="activity-list">
              <div 
                v-for="reminder in results.reminders" 
                :key="'reminder-' + reminder.id" 
                class="activity-item" 
                @click="navigateTo('reminders', reminder.id)"
              >
                <i class="fas fa-bell" style="color: var(--secondary);"></i>
                <div>
                  <strong>{{ reminder.title }}</strong>
                  <p>{{ formatDateTime(reminder.reminder_time) }}</p>
                  <small>
                    By {{ reminder.user?.name || 'Unknown' }} • 
                    {{ reminder.is_active ? 'Active' : 'Inactive' }}
                  </small>
                </div>
                <div style="margin-left: auto;">
                  <i class="fas fa-chevron-right" style="color: var(--gray);"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Searches -->
        <div v-if="recentSearches.length > 0" class="recent-searches">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-history"></i> Recent Searches
              </h3>
              <button class="btn btn-small" @click="clearRecentSearches">
                <i class="fas fa-trash"></i> Clear
              </button>
            </div>
            <div class="card-body">
              <div class="recent-search-list">
                <div 
                  v-for="(search, index) in recentSearches" 
                  :key="index" 
                  class="recent-search-item"
                  @click="searchQuery = search; performSearch()"
                >
                  <i class="fas fa-search"></i>
                  <span>{{ search }}</span>
                  <button class="remove-search" @click.stop="removeRecentSearch(index)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
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
  name: 'AdminSearch',
  data() {
    return {
      searchQuery: '',
      loading: false,
      hasSearched: false,
      results: {
        users: [],
        classes: [],
        tasks: [],
        notes: [],
        reminders: []
      },
      totalResults: 0,
      recentSearches: []
    };
  },
  mounted() {
    this.loadRecentSearches();
    
    // Check if query param exists in URL
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('q');
    if (query) {
      this.searchQuery = query;
      this.performSearch();
    }
  },
  methods: {
    async performSearch() {
      if (!this.searchQuery.trim()) {
        return;
      }
      
      this.loading = true;
      this.hasSearched = true;
      
      try {
        const response = await axios.get('/admin/search/api', {
          params: { q: this.searchQuery }
        });
        
        this.results = response.data.results;
        this.totalResults = response.data.total;
        
        // Save to recent searches
        this.saveRecentSearch(this.searchQuery);
        
        // Update URL without reloading
        const url = new URL(window.location);
        url.searchParams.set('q', this.searchQuery);
        window.history.pushState({}, '', url);
        
      } catch (error) {
        console.error('Error performing search:', error);
        this.showError('Failed to perform search');
      } finally {
        this.loading = false;
      }
    },
    
    navigateTo(type, id) {
      const routes = {
        users: `/admin/users/${id}`,
        classes: `/admin/classes/${id}`,
        tasks: `/admin/tasks/${id}`,
        notes: `/admin/notes/${id}`,
        reminders: `/admin/reminders/${id}`
      };
      
      this.$router.push(routes[type]);
    },
    
    saveRecentSearch(query) {
      // Get existing searches
      let searches = JSON.parse(localStorage.getItem('admin_recent_searches') || '[]');
      
      // Remove if already exists
      searches = searches.filter(s => s !== query);
      
      // Add to beginning
      searches.unshift(query);
      
      // Keep only last 10
      searches = searches.slice(0, 10);
      
      localStorage.setItem('admin_recent_searches', JSON.stringify(searches));
      this.recentSearches = searches;
    },
    
    loadRecentSearches() {
      this.recentSearches = JSON.parse(localStorage.getItem('admin_recent_searches') || '[]');
    },
    
    removeRecentSearch(index) {
      this.recentSearches.splice(index, 1);
      localStorage.setItem('admin_recent_searches', JSON.stringify(this.recentSearches));
    },
    
    clearRecentSearches() {
      if (confirm('Clear all recent searches?')) {
        this.recentSearches = [];
        localStorage.removeItem('admin_recent_searches');
      }
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
    
    getTaskStatusColor(status) {
      const colors = {
        todo: '#ffc107',
        inprogress: '#fd7e14',
        done: '#198754'
      };
      return colors[status] || 'var(--gray)';
    },
    
    getStatusText(status) {
      const texts = {
        todo: 'To Do',
        inprogress: 'In Progress',
        done: 'Done'
      };
      return texts[status] || status;
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      const d = new Date(date);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    
    formatDateTime(date) {
      if (!date) return 'N/A';
      const d = new Date(date);
      return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    
    formatTime(time) {
      if (!time) return 'N/A';
      const d = new Date(`2000-01-01 ${time}`);
      return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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
    },
    
    truncate(text, length) {
      if (!text) return '';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    },
    
    showError(message) {
      console.error(message);
      // You can implement a toast notification here
    }
  },
  
  // Watch for route query changes
  watch: {
    '$route.query.q': {
      handler(newQuery) {
        if (newQuery && newQuery !== this.searchQuery) {
          this.searchQuery = newQuery;
          this.performSearch();
        }
      },
      immediate: true
    }
  }
};
</script>

<style scoped>
.admin-search {
  padding: 1rem;
}

/* Module Header */
.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--light-gray);
}

.module-title {
  margin: 0;
  font-size: 1.5rem;
  color: var(--dark);
}

.module-title i {
  color: var(--primary);
  margin-right: 0.5rem;
}

/* Search Box */
.search-box {
  position: relative;
}

.search-box form {
  display: flex;
  align-items: center;
  background: white;
  border: 1px solid #ced4da;
  border-radius: 8px;
  padding: 8px 12px;
  gap: 8px;
  transition: all 0.2s ease;
}

.search-box form:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.search-box i {
  color: #6c757d;
}

.search-box input {
  border: none;
  outline: none;
  flex: 1;
  background: transparent;
  font-size: 14px;
}

/* Cards */
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--light-gray);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafbfc;
}

.card-title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-body {
  padding: 0;
}

/* Activity List */
.activity-list {
  max-height: 500px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--light-gray);
  transition: background 0.2s ease;
  cursor: pointer;
}

.activity-item:hover {
  background: #f8f9fa;
}

.activity-item > div:first-of-type {
  flex: 1;
}

.activity-item strong {
  display: block;
  margin-bottom: 0.25rem;
  color: var(--dark);
}

.activity-item p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--gray);
}

.activity-item small {
  font-size: 0.75rem;
  color: var(--gray);
}

/* User Avatar */
.user-avatar {
  width: 48px;
  height: 48px;
  min-width: 48px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
}

/* Status Badges */
.status-badge {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
}

.status-done {
  background: #d4edda;
  color: #155724;
}

.status-inprogress {
  background: #fff3cd;
  color: #856404;
}

.status-todo {
  background: #d1ecf1;
  color: #0c5460;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray);
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.empty-state p {
  margin: 0;
  font-size: 1rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray);
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: var(--primary);
}

.loading-state p {
  margin: 0;
  font-size: 1rem;
}

/* Recent Searches */
.recent-searches {
  margin-top: 2rem;
}

.recent-search-list {
  padding: 0.5rem;
}

.recent-search-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
  border-radius: 6px;
}

.recent-search-item:hover {
  background: #f8f9fa;
}

.recent-search-item i {
  color: var(--gray);
}

.recent-search-item span {
  flex: 1;
  color: var(--dark);
}

.remove-search {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--gray);
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.remove-search:hover {
  background: var(--light-gray);
  color: var(--danger);
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--light-gray);
  color: var(--dark);
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.btn:hover {
  background: #e0e0e0;
}

.btn-small {
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
}

/* Icons */
.fa-chevron-right {
  font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
  .module-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .search-box {
    width: 100% !important;
  }
  
  .activity-item {
    flex-wrap: wrap;
  }
  
  .activity-item > div:first-of-type {
    flex: 1 1 100%;
  }
  
  .activity-item > div:last-child {
    margin-left: 0 !important;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    font-size: 0.75rem;
  }
}
</style>