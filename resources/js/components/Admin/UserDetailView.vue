<template>
  <div class="user-details-modern">
    <!-- User Profile Header -->
    <div class="user-profile-header">
      <div class="profile-avatar-section">
        <div class="profile-avatar">
          {{ getUserInitials(user.name) }}
        </div>
        <div class="profile-badge">
          <i class="fas fa-user"></i> Regular User
        </div>
      </div>
      
      <div class="profile-info">
        <h1 class="profile-name">{{ user.name }}</h1>
        <p class="profile-email">
          <i class="fas fa-envelope"></i> {{ user.email }}
        </p>
        <p class="profile-meta">
          <i class="fas fa-calendar-plus"></i> Joined {{ formatFullDate(user.created_at) }}
          <span class="meta-divider">•</span>
          <i class="fas fa-clock"></i> {{ timeAgo(user.created_at) }}
        </p>
      </div>
      
      <div class="profile-stats">
        <div class="stat-item">
          <div class="stat-number">{{ user.class_schedules_count || 0 }}</div>
          <div class="stat-label">Classes</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">{{ user.tasks_count || 0 }}</div>
          <div class="stat-label">Tasks</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">{{ user.notes_count || 0 }}</div>
          <div class="stat-label">Notes</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">{{ user.reminders_count || 0 }}</div>
          <div class="stat-label">Reminders</div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid-large-calendar">
      <!-- Left Column - Calendar -->
      <div class="calendar-column">
        <!-- Weekly Schedule Calendar -->
        <div class="content-card">
          <div class="card-header">
            <h3><i class="fas fa-calendar-week"></i> Weekly Schedule</h3>
          </div>
          <div class="card-body">
            <div class="calendar-view-large">
              <div class="week-days-large">
                <div v-for="day in daysOfWeek" :key="day" class="day-column-large">
                  <div class="day-header-large">{{ day }}</div>
                  <div class="day-content-large">
                    <div v-for="classItem in getClassesByDay(day)" :key="classItem.id" 
                         class="calendar-class-item-large" 
                         :style="{ borderLeftColor: classItem.color || '#4f46e5' }">
                      <div class="class-time-large">{{ classItem.time }}</div>
                      <div class="class-name-large">{{ classItem.name }}</div>
                      <div v-if="classItem.location" class="class-location-large">{{ classItem.location }}</div>
                    </div>
                    <div v-if="getClassesByDay(day).length === 0" class="no-classes-large">
                      No classes scheduled
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Task Statistics -->
        <div class="content-card" v-if="taskStats.length > 0">
          <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Task Distribution</h3>
          </div>
          <div class="card-body">
            <div class="stats-visual">
              <div v-for="stat in taskStats" :key="stat.status" class="stat-bar-item">
                <div class="stat-label">
                  <span :class="['status-dot', getStatusClass(stat.status)]"></span>
                  {{ getStatusText(stat.status) }}
                </div>
                <div class="stat-bar-container">
                  <div class="stat-bar" :style="{ width: stat.percentage + '%', '--bar-color': getStatusColor(stat.status) }">
                    <span class="stat-count">{{ stat.count }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Recent Activity & Quick Actions -->
      <div class="sidebar-column">
        <!-- Recent Activity -->
        <div class="content-card compact">
          <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
          </div>
          <div class="card-body compact">
            <div class="activity-feed-compact">
              <div v-for="activity in recentActivities" :key="activity.id" class="activity-item-compact">
                <div :class="['activity-icon-compact', activity.type]">
                  <i :class="'fas fa-' + activity.icon"></i>
                </div>
                <div class="activity-content-compact">
                  <div class="activity-header-compact">
                    <h4 class="activity-title-compact">{{ activity.title }}</h4>
                    <span :class="['activity-badge', 'badge-' + activity.color]">
                      {{ activity.action }}
                    </span>
                  </div>
                  <span class="activity-time-compact">
                    <i class="fas fa-clock icon-small"></i>
                    {{ timeAgo(activity.time) }}
                  </span>
                </div>
              </div>
              <div v-if="recentActivities.length === 0" class="empty-state-compact">
                <i class="fas fa-inbox"></i>
                <p>No recent activity</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card compact">
          <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
          </div>
          <div class="card-body compact">
            <div class="quick-actions-compact">
              <button class="quick-action-compact" @click="$emit('close')">
                <div class="action-icon-compact">
                  <i class="fas fa-arrow-left"></i>
                </div>
                <div class="action-content-compact">
                  <h4>Back to Users</h4>
                </div>
              </button>
              
              <button class="quick-action-compact" @click="editUser">
                <div class="action-icon-compact">
                  <i class="fas fa-user-edit"></i>
                </div>
                <div class="action-content-compact">
                  <h4>Edit User</h4>
                </div>
              </button>
              
              <button class="quick-action-compact" @click="sendEmail">
                <div class="action-icon-compact">
                  <i class="fas fa-envelope"></i>
                </div>
                <div class="action-content-compact">
                  <h4>Send Email</h4>
                </div>
              </button>
              
              <button class="quick-action-compact danger" @click="confirmDelete">
                <div class="action-icon-compact">
                  <i class="fas fa-trash"></i>
                </div>
                <div class="action-content-compact">
                  <h4>Delete User</h4>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Tabs -->
    <div class="content-tabs-section">
      <div class="tabs-header">
        <button class="tab-btn" :class="{ active: activeTab === 'tasks' }" @click="activeTab = 'tasks'">
          <i class="fas fa-tasks"></i> Tasks ({{ user.tasks ? user.tasks.length : 0 }})
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'notes' }" @click="activeTab = 'notes'">
          <i class="fas fa-sticky-note"></i> Notes ({{ user.notes ? user.notes.length : 0 }})
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'reminders' }" @click="activeTab = 'reminders'">
          <i class="fas fa-bell"></i> Reminders ({{ user.reminders ? user.reminders.length : 0 }})
        </button>
      </div>
      
      <div class="tabs-content">
        <!-- Tasks Tab -->
        <div v-show="activeTab === 'tasks'" class="tab-pane">
          <div v-if="user.tasks && user.tasks.length > 0" class="items-table">
            <table>
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Priority</th>
                  <th>Due Date</th>
                  <th>Last Updated</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="task in user.tasks" :key="task.id">
                  <td>
                    <div class="item-title">
                      <i class="fas fa-tasks"></i>
                      {{ task.title }}
                    </div>
                    <div v-if="task.description" class="item-description">
                      {{ truncate(task.description, 100) }}
                    </div>
                  </td>
                  <td>
                    <span :class="['status-badge', 'status-' + task.status]">
                      {{ getTaskStatusText(task.status) }}
                    </span>
                  </td>
                  <td>
                    <span v-if="task.priority" :class="['priority-badge', 'priority-' + task.priority]">
                      {{ capitalize(task.priority) }}
                    </span>
                    <span v-else class="priority-badge priority-none">None</span>
                  </td>
                  <td>{{ formatDate(task.due_date) }}</td>
                  <td>{{ timeAgo(task.updated_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-tab">
            <i class="fas fa-tasks"></i>
            <p>No tasks found</p>
          </div>
        </div>
        
        <!-- Notes Tab -->
        <div v-show="activeTab === 'notes'" class="tab-pane">
          <div v-if="user.notes && user.notes.length > 0" class="notes-grid">
            <div v-for="note in user.notes" :key="note.id" class="note-card">
              <div class="note-header">
                <div class="note-icon">
                  <i class="fas fa-sticky-note"></i>
                </div>
                <div class="note-meta">
                  <span>{{ timeAgo(note.updated_at) }}</span>
                </div>
              </div>
              <div class="note-content">
                <h4>{{ note.title }}</h4>
                <p class="note-text">{{ truncate(note.content, 150) }}</p>
                <div v-if="note.tags && note.tags.length" class="note-tags">
                  <span v-for="tag in note.tags" :key="tag" class="tag">{{ tag }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-tab">
            <i class="fas fa-sticky-note"></i>
            <p>No notes found</p>
          </div>
        </div>
        
        <!-- Reminders Tab -->
        <div v-show="activeTab === 'reminders'" class="tab-pane">
          <div v-if="user.reminders && user.reminders.length > 0" class="reminders-list">
            <div v-for="reminder in user.reminders" :key="reminder.id" class="reminder-item">
              <div class="reminder-icon">
                <i class="fas fa-bell"></i>
              </div>
              <div class="reminder-content">
                <div class="reminder-header">
                  <h4>{{ reminder.title }}</h4>
                  <span class="reminder-time">
                    <i class="fas fa-clock"></i>
                    {{ formatDateTime(reminder.reminder_time) }}
                  </span>
                </div>
                <div v-if="reminder.is_task_reminder" class="reminder-task">
                  <i class="fas fa-link"></i>
                  <span>Linked to task</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-tab">
            <i class="fas fa-bell"></i>
            <p>No reminders found</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserDetailView',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      activeTab: 'tasks',
      daysOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    };
  },
  computed: {
    taskStats() {
      if (!this.user.tasks || this.user.tasks.length === 0) return [];
      
      const statuses = ['todo', 'inprogress', 'done'];
      const total = this.user.tasks.length;
      
      return statuses.map(status => {
        const count = this.user.tasks.filter(t => t.status === status).length;
        return {
          status,
          count,
          percentage: total > 0 ? (count / total) * 100 : 0
        };
      }).filter(s => s.count > 0);
    },
    recentActivities() {
      const activities = [];
      
      // Add tasks
      if (this.user.tasks) {
        this.user.tasks.slice(0, 3).forEach(task => {
          activities.push({
            id: `task-${task.id}`,
            type: 'task',
            icon: 'tasks',
            color: 'warning',
            title: task.title,
            action: task.status === 'done' ? 'completed' : 'updated',
            time: task.updated_at
          });
        });
      }
      
      // Add notes
      if (this.user.notes) {
        this.user.notes.slice(0, 2).forEach(note => {
          activities.push({
            id: `note-${note.id}`,
            type: 'note',
            icon: 'sticky-note',
            color: 'success',
            title: note.title,
            action: 'created',
            time: note.updated_at
          });
        });
      }
      
      // Add classes
      if (this.user.class_schedules) {
        this.user.class_schedules.slice(0, 2).forEach(classItem => {
          activities.push({
            id: `class-${classItem.id}`,
            type: 'class',
            icon: 'calendar-alt',
            color: 'info',
            title: classItem.name,
            action: 'scheduled',
            time: classItem.updated_at
          });
        });
      }
      
      // Add reminders
      if (this.user.reminders) {
        this.user.reminders.slice(0, 2).forEach(reminder => {
          activities.push({
            id: `reminder-${reminder.id}`,
            type: 'reminder',
            icon: 'bell',
            color: 'danger',
            title: reminder.title,
            action: 'set',
            time: reminder.reminder_time
          });
        });
      }
      
      // Sort by time (most recent first) and take top 4
      return activities.sort((a, b) => new Date(b.time) - new Date(a.time)).slice(0, 4);
    }
  },
  methods: {
    getClassesByDay(day) {
      if (!this.user.class_schedules) return [];
      return this.user.class_schedules.filter(c => c.day === day);
    },
    getStatusClass(status) {
      const classes = {
        todo: 'status-pending',
        inprogress: 'status-in_progress',
        done: 'status-completed'
      };
      return classes[status] || 'status-pending';
    },
    getStatusColor(status) {
      const colors = {
        todo: '#f59e0b',
        inprogress: '#3b82f6',
        done: '#10b981'
      };
      return colors[status] || '#6b7280';
    },
    getStatusText(status) {
      const texts = {
        todo: 'Pending',
        inprogress: 'In Progress',
        done: 'Completed'
      };
      return texts[status] || status;
    },
    getTaskStatusText(status) {
      const texts = {
        todo: 'To Do',
        inprogress: 'In Progress',
        done: 'Done'
      };
      return texts[status] || status;
    },
    getUserInitials(name) {
      if (!name) return 'NA';
      return name.substring(0, 2).toUpperCase();
    },
    formatFullDate(date) {
      if (!date) return 'N/A';
      const d = new Date(date);
      return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
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
    capitalize(str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
    editUser() {
      this.$emit('edit', this.user);
    },
    sendEmail() {
      window.location.href = `mailto:${this.user.email}`;
    },
    confirmDelete() {
      if (confirm(`Are you sure you want to delete user "${this.user.name}"? This action cannot be undone.`)) {
        this.$emit('delete', this.user);
      }
    }
  }
};
</script>

<style scoped>
.user-details-modern {
  padding: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Profile Header */
.user-profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 40px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 40px;
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.user-profile-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.profile-avatar-section {
  position: relative;
  z-index: 1;
}

.profile-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(45deg, #ffffff, #e6e6e6);
  color: #667eea;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  font-weight: bold;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  border: 4px solid rgba(255, 255, 255, 0.3);
}

.profile-badge {
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.profile-info {
  flex: 1;
  z-index: 1;
}

.profile-name {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-email {
  font-size: 16px;
  opacity: 0.9;
  margin: 0 0 12px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.profile-meta {
  font-size: 14px;
  opacity: 0.8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.meta-divider {
  opacity: 0.5;
}

.profile-stats {
  display: flex;
  gap: 30px;
  z-index: 1;
  background: rgba(255, 255, 255, 0.1);
  padding: 20px;
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Main Layout */
.content-grid-large-calendar {
  display: grid;
  grid-template-columns: 70% 30%;
  gap: 25px;
  margin-bottom: 40px;
}

@media (max-width: 1200px) {
  .content-grid-large-calendar {
    grid-template-columns: 1fr;
  }
}

.content-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.content-card.compact {
  margin-bottom: 20px;
}

.card-header {
  padding: 18px 20px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-body {
  padding: 20px;
}

.card-body.compact {
  padding: 15px;
}

/* Calendar View */
.week-days-large {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 12px;
  margin-top: 15px;
}

.day-column-large {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  min-height: 350px;
  background: #f9fafb;
}

.day-header-large {
  background: #4f46e5;
  color: white;
  padding: 12px;
  text-align: center;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.day-content-large {
  padding: 12px;
  height: calc(100% - 44px);
  overflow-y: auto;
}

.calendar-class-item-large {
  background: white;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
  border-left: 4px solid;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  cursor: pointer;
  transition: all 0.3s ease;
}

.calendar-class-item-large:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.class-time-large {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
}

.class-name-large {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 3px;
  color: #1f2937;
}

.class-location-large {
  font-size: 12px;
  color: #6b7280;
}

.no-classes-large {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
  font-size: 14px;
  font-style: italic;
}

/* Stats Visual */
.stats-visual {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-bar-item {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-label {
  width: 120px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-pending { background: #f59e0b; }
.status-in_progress { background: #3b82f6; }
.status-completed { background: #10b981; }

.stat-bar-container {
  flex: 1;
  height: 24px;
  background: #f3f4f6;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
}

.stat-bar {
  height: 100%;
  background: linear-gradient(90deg, var(--bar-color), color-mix(in srgb, var(--bar-color) 80%, white));
  border-radius: 12px;
  transition: width 1s ease;
  position: relative;
}

.stat-count {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  font-size: 12px;
  font-weight: 600;
}

/* Activity Feed */
.activity-feed-compact {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.activity-item-compact {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.activity-item-compact:hover {
  background: #f3f4f6;
}

.activity-icon-compact {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: white;
}

.activity-icon-compact.task { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.activity-icon-compact.note { background: linear-gradient(135deg, #10b981, #34d399); }
.activity-icon-compact.class { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.activity-icon-compact.reminder { background: linear-gradient(135deg, #ef4444, #f87171); }

.activity-content-compact {
  flex: 1;
  min-width: 0;
}

.activity-header-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.activity-title-compact {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-badge {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 500;
}

.badge-warning { background: #fef3c7; color: #92400e; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-danger { background: #fee2e2; color: #991b1b; }

.activity-time-compact {
  font-size: 11px;
  color: #9ca3af;
  display: block;
}

.empty-state-compact {
  text-align: center;
  padding: 30px 15px;
  color: #9ca3af;
}

.empty-state-compact i {
  font-size: 32px;
  margin-bottom: 10px;
  opacity: 0.3;
}

/* Quick Actions */
.quick-actions-compact {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.quick-action-compact {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
  border: none;
  width: 100%;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.quick-action-compact:hover {
  background: #f3f4f6;
  border-color: #e5e7eb;
}

.quick-action-compact.danger:hover {
  border-color: #fecaca;
  background: #fef2f2;
}

.action-icon-compact {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 6px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.action-content-compact h4 {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
}

/* Tabs Section */
.content-tabs-section {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-top: 30px;
}

.tabs-header {
  display: flex;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.tab-btn {
  flex: 1;
  padding: 18px;
  background: none;
  border: none;
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.tab-btn:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.tab-btn.active {
  color: #4f46e5;
  background: white;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2px;
  background: #4f46e5;
}

.tabs-content {
  padding: 20px;
}

.tab-pane {
  display: block;
}

/* Items Table */
.items-table {
  overflow-x: auto;
}

.items-table table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th {
  padding: 12px 16px;
  text-align: left;
  background: #f9fafb;
  color: #6b7280;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e5e7eb;
}

.items-table td {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: top;
}

.item-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 6px;
}

.item-description {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-todo { background: #fef3c7; color: #92400e; }
.status-inprogress { background: #dbeafe; color: #1e40af; }
.status-done { background: #d1fae5; color: #065f46; }

.priority-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.priority-high { background: #fee2e2; color: #991b1b; }
.priority-medium { background: #fef3c7; color: #92400e; }
.priority-low { background: #d1fae5; color: #065f46; }
.priority-none { background: #f3f4f6; color: #6b7280; }

/* Notes Grid */
.notes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.note-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.note-card:hover {
  border-color: #4f46e5;
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.1);
  transform: translateY(-4px);
}

.note-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.note-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #f3f4f6;
  color: #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.note-meta {
  font-size: 12px;
  color: #9ca3af;
}

.note-content h4 {
  margin: 0 0 10px 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.note-text {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 15px 0;
  line-height: 1.5;
}

.note-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag {
  padding: 4px 10px;
  background: #e5e7eb;
  color: #374151;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
}

/* Reminders List */
.reminders-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.reminder-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.reminder-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.reminder-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #ef4444, #f87171);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.reminder-content {
  flex: 1;
}

.reminder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  flex-wrap: wrap;
  gap: 10px;
}

.reminder-header h4 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #1f2937;
}

.reminder-time {
  font-size: 12px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 4px;
}

.reminder-task {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #6b7280;
}

/* Empty States */
.empty-tab {
  text-align: center;
  padding: 48px 20px;
  color: #9ca3af;
}

.empty-tab i {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.3;
}

.empty-tab p {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .user-profile-header {
    flex-direction: column;
    text-align: center;
    padding: 30px 20px;
    gap: 20px;
  }
  
  .profile-stats {
    width: 100%;
    justify-content: space-around;
    gap: 20px;
  }
  
  .week-days-large {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  
  .day-column-large {
    min-height: 250px;
  }
  
  .tabs-header {
    flex-direction: column;
  }
  
  .tab-btn {
    padding: 15px;
    justify-content: flex-start;
  }
  
  .notes-grid {
    grid-template-columns: 1fr;
  }
  
  .reminder-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>