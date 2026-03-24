<template>
    <div class="dashboard">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <div class="welcome-text">
                <h1>Welcome back, <span class="user-name">{{ userName }}</span>!</h1>
                <p>Here's what's happening with your academic journey today.</p>
            </div>
            <div class="current-date">
                <i class="fas fa-calendar-day"></i>
                <span>{{ formattedDate }}</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" @click="navigateTo('schedule')">
                <div class="stat-icon class-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ stats.classes_count }}</div>
                    <div class="stat-label">Classes Today</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <div class="stat-card" @click="navigateTo('tasks')">
                <div class="stat-icon task-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ stats.tasks_count }}</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <div class="stat-card" @click="navigateTo('notes')">
                <div class="stat-icon note-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ stats.notes_count }}</div>
                    <div class="stat-label">Notes</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
            <div class="stat-card" @click="navigateTo('reminders')">
                <div class="stat-icon reminder-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ stats.reminders_count }}</div>
                    <div class="stat-label">Upcoming Reminders</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <button class="action-btn" @click="navigateToAdd('task')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Task</span>
                </button>
                <button class="action-btn" @click="navigateToAdd('note')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Note</span>
                </button>
                <button class="action-btn" @click="navigateToAdd('class')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Class</span>
                </button>
                <button class="action-btn" @click="navigateToAdd('reminder')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Reminder</span>
                </button>
            </div>
        </div>

        <!-- Dashboard Grid - First Row -->
        <div class="dashboard-grid">
            <!-- Today's Classes Card -->
            <div class="card today-classes-card" @click="navigateTo('schedule')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day"></i> Today's Classes
                    </h3>
                    <a :href="route('schedule.index')" class="btn-link" @click.stop>View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="card-content">
                    <div v-if="loading.classes" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <div v-else-if="classesToday.length > 0" class="classes-list">
                        <div 
                            v-for="classItem in classesToday" 
                            :key="classItem.id"
                            class="class-item"
                            @click.stop="navigateToEdit('schedule', classItem.id)"
                        >
                            <div class="class-time-badge">
                                <i class="fas fa-clock"></i>
                                <span>{{ classItem.time }}</span>
                            </div>
                            <div class="class-info">
                                <div class="class-name">{{ classItem.name }}</div>
                                <div class="class-location">
                                    <i class="fas fa-map-marker-alt"></i> {{ classItem.location }}
                                </div>
                            </div>
                            <div class="class-color" :style="{ backgroundColor: classItem.color }"></div>
                        </div>
                    </div>
                    <div v-else class="empty-card">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No classes today</p>
                        <button class="btn-empty" @click.stop="navigateToAdd('class')">Add Class</button>
                    </div>
                </div>
            </div>

            <!-- Upcoming Tasks Card -->
            <div class="card upcoming-tasks-card" @click="navigateTo('tasks')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hourglass-half"></i> Upcoming Tasks
                    </h3>
                    <a :href="route('tasks.index')" class="btn-link" @click.stop>View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="card-content">
                    <div v-if="loading.tasks" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <div v-else-if="tasksUpcoming.length > 0" class="tasks-list">
                        <div 
                            v-for="task in tasksUpcoming" 
                            :key="task.id"
                            class="task-item"
                            :class="{
                                'priority-high': task.priority === 'high',
                                'priority-medium': task.priority === 'medium',
                                'priority-low': task.priority === 'low',
                                'overdue': isOverdue(task),
                                'due-today': isDueToday(task)
                            }"
                            @click.stop="navigateToEdit('tasks', task.id)"
                        >
                            <div class="task-priority-icon">
                                <i :class="getPriorityIcon(task.priority)"></i>
                            </div>
                            <div class="task-info">
                                <div class="task-title">{{ task.title }}</div>
                                <div class="task-meta">
                                    <span class="task-due">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ formatDate(task.due_date) }}
                                    </span>
                                    <span class="task-status" :class="`status-${task.status}`">
                                        {{ formatStatus(task.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-card">
                        <i class="fas fa-tasks"></i>
                        <p>No upcoming tasks</p>
                        <button class="btn-empty" @click.stop="navigateToAdd('task')">Add Task</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid - Second Row (Recent Notes & Recent Reminders) -->
        <div class="dashboard-grid two-columns">
            <!-- Recent Notes Card -->
            <div class="card recent-notes-card" @click="navigateTo('notes')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sticky-note"></i> Recent Notes
                    </h3>
                    <a :href="route('notes.index')" class="btn-link" @click.stop>View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="card-content">
                    <div v-if="loading.notes" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <div v-else-if="notesRecent.length > 0" class="notes-list">
                        <div 
                            v-for="note in notesRecent" 
                            :key="note.id"
                            class="note-item"
                            @click.stop="navigateToView('notes', note.id)"
                        >
                            <div class="note-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="note-info">
                                <div class="note-title">{{ note.title }}</div>
                                <div class="note-preview">{{ truncateContent(note.content) }}</div>
                                <div v-if="note.tags && note.tags.length" class="note-tags">
                                    <span v-for="tag in note.tags.slice(0, 2)" :key="tag" class="tag">{{ tag }}</span>
                                    <span v-if="note.tags.length > 2" class="tag-more">+{{ note.tags.length - 2 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-card">
                        <i class="fas fa-sticky-note"></i>
                        <p>No recent notes</p>
                        <button class="btn-empty" @click.stop="navigateToAdd('note')">Add Note</button>
                    </div>
                </div>
            </div>

            <!-- Recent Personal Reminders Card -->
            <div class="card reminders-card" @click="navigateTo('reminders')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Recent Personal Reminders
                    </h3>
                    <a :href="route('reminders.index')" class="btn-link" @click.stop>View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="card-content">
                    <div v-if="loading.reminders" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <div v-else-if="recentReminders.length > 0" class="reminders-list">
                        <div 
                            v-for="reminder in recentReminders" 
                            :key="reminder.id"
                            class="reminder-item"
                            :class="{
                                'reminder-urgent': isUrgent(reminder),
                                'reminder-today': isReminderToday(reminder)
                            }"
                            @click.stop="navigateToEdit('reminder', reminder.id)"
                        >
                            <div class="reminder-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="reminder-info">
                                <div class="reminder-title">{{ reminder.title }}</div>
                                <div class="reminder-time">
                                    <i class="far fa-clock"></i>
                                    {{ formatReminderTime(reminder.reminder_time) }}
                                    <span v-if="isReminderToday(reminder)" class="reminder-badge today-badge">Today</span>
                                    <span v-else-if="isUrgent(reminder)" class="reminder-badge urgent-badge">Urgent</span>
                                </div>
                            </div>
                            <div class="reminder-actions" @click.stop>
                                <button class="icon-btn small" @click="navigateToEdit('reminder', reminder.id)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-card">
                        <i class="fas fa-bell-slash"></i>
                        <p>No personal reminders</p>
                        <button class="btn-empty" @click.stop="navigateToAdd('reminder')">Add Reminder</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card - Full Width -->
        <div class="card activity-card full-width">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Recent Activity
                </h3>
            </div>
            <div class="card-content">
                <div v-if="recentActivities.length === 0" class="empty-card">
                    <i class="fas fa-history"></i>
                    <p>No recent activity</p>
                </div>
                <div v-else class="activity-timeline">
                    <div 
                        v-for="activity in recentActivities" 
                        :key="activity.id"
                        class="activity-item"
                    >
                        <div class="activity-icon" :class="activity.type">
                            <i :class="activity.icon"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">{{ activity.text }}</div>
                            <div class="activity-time">{{ activity.time }}</div>
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
    name: 'Dashboard',
    
    props: {
        initialStats: {
            type: Object,
            default: () => ({
                classes_count: 0,
                tasks_count: 0,
                notes_count: 0,
                reminders_count: 0
            })
        },
        initialClasses: {
            type: Array,
            default: () => []
        },
        initialTasks: {
            type: Array,
            default: () => []
        },
        initialNotes: {
            type: Array,
            default: () => []
        },
        initialReminders: {
            type: Array,
            default: () => []
        },
        userName: {
            type: String,
            default: 'Student'
        }
    },

    data() {
        return {
            stats: this.initialStats,
            classesToday: this.initialClasses,
            tasksUpcoming: this.initialTasks,
            notesRecent: this.initialNotes,
            recentReminders: this.initialReminders,
            recentActivities: [],
            loading: {
                classes: false,
                tasks: false,
                notes: false,
                reminders: false
            }
        };
    },

    computed: {
        formattedDate() {
            const now = new Date();
            return now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    },

    mounted() {
        console.log('Dashboard mounted');
        this.generateRecentActivities();
        this.startAutoRefresh();
    },

    methods: {
        navigateTo(module) {
            window.location.href = `/${module}`;
        },

        navigateToAdd(module) {
            if (module === 'task') {
                window.location.href = '/tasks';
            } else if (module === 'note') {
                window.location.href = '/notes';
            } else if (module === 'class') {
                window.location.href = '/schedule';
            } else if (module === 'reminder') {
                window.location.href = '/reminders';
            }
        },

        navigateToEdit(module, id) {
            if (module === 'schedule') {
                window.location.href = `/schedule/${id}/edit`;
            } else if (module === 'tasks') {
                window.location.href = `/tasks/${id}/edit`;
            } else if (module === 'reminder') {
                window.location.href = `/reminders/${id}/edit`;
            }
        },

        navigateToView(module, id) {
            if (module === 'notes') {
                window.location.href = `/notes/${id}`;
            }
        },

        route(name) {
            const routes = {
                'schedule.index': '/schedule',
                'tasks.index': '/tasks',
                'notes.index': '/notes',
                'reminders.index': '/reminders'
            };
            return routes[name] || '/';
        },

        formatDate(date) {
            if (!date) return 'No date';
            const dateObj = new Date(date);
            const now = new Date();
            const diffDays = Math.ceil((dateObj - now) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Tomorrow';
            if (diffDays === -1) return 'Yesterday';
            
            return dateObj.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        },

        formatReminderTime(dateTime) {
            if (!dateTime) return 'No date';
            const date = new Date(dateTime);
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            if (date.toDateString() === now.toDateString()) {
                return 'Today at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else if (date.toDateString() === tomorrow.toDateString()) {
                return 'Tomorrow at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        isReminderToday(reminder) {
            if (!reminder.reminder_time) return false;
            const reminderDate = new Date(reminder.reminder_time);
            const today = new Date();
            return reminderDate.toDateString() === today.toDateString();
        },

        isUrgent(reminder) {
            if (!reminder.reminder_time) return false;
            const reminderDate = new Date(reminder.reminder_time);
            const now = new Date();
            const hoursUntil = (reminderDate - now) / (1000 * 60 * 60);
            return hoursUntil <= 2 && hoursUntil > 0;
        },

        formatStatus(status) {
            const statusMap = {
                'todo': 'To Do',
                'inprogress': 'In Progress',
                'done': 'Done'
            };
            return statusMap[status] || status;
        },

        getPriorityIcon(priority) {
            const icons = {
                'high': 'fas fa-arrow-up',
                'medium': 'fas fa-minus',
                'low': 'fas fa-arrow-down'
            };
            return icons[priority] || 'fas fa-flag';
        },

        isOverdue(task) {
            if (task.status === 'done') return false;
            const dueDate = new Date(task.due_date);
            const now = new Date();
            return dueDate < now;
        },

        isDueToday(task) {
            if (task.status === 'done') return false;
            const dueDate = new Date(task.due_date);
            const today = new Date();
            return dueDate.toDateString() === today.toDateString() && !this.isOverdue(task);
        },

        truncateContent(content) {
            if (!content) return '';
            return content.length > 60 ? content.substring(0, 60) + '...' : content;
        },

        generateRecentActivities() {
            const activities = [];
            
            // Add class activities
            this.classesToday.forEach(classItem => {
                activities.push({
                    id: `class-${classItem.id}`,
                    type: 'class',
                    icon: 'fas fa-book-open',
                    text: `Class "${classItem.name}" starts at ${classItem.time}`,
                    time: 'Today'
                });
            });
            
            // Add task activities
            this.tasksUpcoming.forEach(task => {
                const dueText = this.isOverdue(task) ? 'Overdue' : 
                               this.isDueToday(task) ? 'Due today' : 
                               `Due ${this.formatDate(task.due_date)}`;
                activities.push({
                    id: `task-${task.id}`,
                    type: 'task',
                    icon: 'fas fa-tasks',
                    text: `Task "${task.title}" - ${dueText}`,
                    time: this.formatDate(task.due_date)
                });
            });
            
            // Add note activities
            this.notesRecent.forEach(note => {
                const date = new Date(note.created_at);
                const timeAgo = this.getTimeAgo(date);
                activities.push({
                    id: `note-${note.id}`,
                    type: 'note',
                    icon: 'fas fa-sticky-note',
                    text: `Created new note: "${note.title}"`,
                    time: timeAgo
                });
            });
            
            // Add reminder activities
            this.recentReminders.forEach(reminder => {
                const reminderDate = new Date(reminder.reminder_time);
                const timeText = this.isReminderToday(reminder) ? 'Today' : this.formatReminderTime(reminder.reminder_time);
                activities.push({
                    id: `reminder-${reminder.id}`,
                    type: 'reminder',
                    icon: 'fas fa-bell',
                    text: `Reminder: "${reminder.title}"`,
                    time: timeText
                });
            });
            
            // Sort by time (most recent first) and limit to 5
            this.recentActivities = activities.slice(0, 5);
        },

        getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} min ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        },

        async refreshStats() {
            try {
                const response = await axios.get('/dashboard/stats');
                if (response.data) {
                    this.stats = response.data;
                }
            } catch (error) {
                console.error('Failed to refresh stats:', error);
            }
        },

        startAutoRefresh() {
            setInterval(() => {
                this.refreshStats();
            }, 5 * 60 * 1000);
        }
    }
};
</script>

<style scoped>
.dashboard {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    background: #f8fafc;
    min-height: 100vh;
}

/* Welcome Header */
.welcome-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.welcome-text h1 {
    font-size: 1.8rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.user-name {
    color: #4361ee;
    font-weight: 700;
    position: relative;
    display: inline-block;
}

.user-name::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #4361ee;
    border-radius: 2px;
}

.welcome-text p {
    color: #64748b;
    font-size: 1rem;
}

.current-date {
    background: white;
    padding: 0.75rem 1.5rem;
    border-radius: 40px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    font-weight: 500;
    color: #4361ee;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.class-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: white; }
.task-icon { background: linear-gradient(135deg, #f72585, #e63946); color: white; }
.note-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); color: white; }
.reminder-icon { background: linear-gradient(135deg, #7209b7, #560bad); color: white; }

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.stat-trend {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-trend {
    background: #4361ee;
    color: white;
    transform: translateX(4px);
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 2rem;
}

.quick-actions h3 {
    font-size: 1.2rem;
    color: #334155;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 40px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #475569;
}

.action-btn:hover {
    background: #4361ee;
    border-color: #4361ee;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.dashboard-grid.two-columns {
    grid-template-columns: repeat(2, 1fr);
}

/* Cards */
.card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.card.full-width {
    grid-column: span 2;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1e293b;
}

.card-title i {
    color: #4361ee;
}

.btn-link {
    color: #4361ee;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: gap 0.3s ease;
}

.btn-link:hover {
    gap: 0.6rem;
}

.card-content {
    padding: 1.25rem 1.5rem;
    min-height: 280px;
}

/* Reminders List */
.reminders-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.reminder-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.reminder-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.reminder-item.reminder-urgent {
    background: linear-gradient(135deg, #fff5f5, #ffeaea);
    border-left: 3px solid #e63946;
}

.reminder-item.reminder-today {
    background: linear-gradient(135deg, #fff9e6, #fff3cd);
    border-left: 3px solid #ffb300;
}

.reminder-icon {
    width: 40px;
    height: 40px;
    background: #e2e8f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f72585;
    font-size: 1.2rem;
}

.reminder-info {
    flex: 1;
}

.reminder-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.reminder-time {
    font-size: 0.75rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.reminder-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
}

.today-badge {
    background: #ffb300;
    color: #212529;
}

.urgent-badge {
    background: #e63946;
    color: white;
}

.reminder-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.reminder-item:hover .reminder-actions {
    opacity: 1;
}

.icon-btn.small {
    width: 28px;
    height: 28px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #4361ee;
    transition: all 0.2s;
}

.icon-btn.small:hover {
    background: #4361ee;
    color: white;
}

/* Classes, Tasks, Notes Lists */
.classes-list, .tasks-list, .notes-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.class-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.class-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.class-time-badge {
    background: #e2e8f0;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.class-info {
    flex: 1;
}

.class-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.2rem;
}

.class-location {
    font-size: 0.8rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.class-color {
    width: 4px;
    height: 40px;
    border-radius: 2px;
}

.task-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 3px solid transparent;
}

.task-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.task-item.priority-high { border-left-color: #e63946; }
.task-item.priority-medium { border-left-color: #ffb300; }
.task-item.priority-low { border-left-color: #28a745; }
.task-item.overdue { background: #fff5f5; }
.task-item.due-today { background: #fff9e6; }

.task-priority-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.task-item.priority-high .task-priority-icon { background: #e63946; color: white; }
.task-item.priority-medium .task-priority-icon { background: #ffb300; color: #212529; }
.task-item.priority-low .task-priority-icon { background: #28a745; color: white; }

.task-info { flex: 1; }
.task-title { font-weight: 600; color: #1e293b; margin-bottom: 0.3rem; }

.task-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    font-size: 0.75rem;
}

.task-due {
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-status {
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
}

.status-todo { background: #fff3cd; color: #856404; }
.status-inprogress { background: #cce5ff; color: #004085; }
.status-done { background: #d4edda; color: #155724; }

.note-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.note-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.note-icon {
    width: 40px;
    height: 40px;
    background: #e2e8f0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.2rem;
}

.note-info { flex: 1; }
.note-title { font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
.note-preview { font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem; line-height: 1.4; }

.note-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag {
    background: #e2e8f0;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    color: #475569;
}

.tag-more {
    font-size: 0.7rem;
    color: #64748b;
}

/* Activity Timeline */
.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.activity-icon.class { background: #e0e7ff; color: #4361ee; }
.activity-icon.task { background: #ffe4e6; color: #f72585; }
.activity-icon.note { background: #dcfce7; color: #28a745; }
.activity-icon.reminder { background: #f3e8ff; color: #7209b7; }

.activity-content {
    flex: 1;
}

.activity-text {
    color: #1e293b;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.activity-time {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Empty State */
.empty-card {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

.empty-card i {
    font-size: 3rem;
    margin-bottom: 0.75rem;
    color: #cbd5e1;
}

.empty-card p {
    margin-bottom: 1rem;
    color: #64748b;
}

.btn-empty {
    background: transparent;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 1rem;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.85rem;
    color: #4361ee;
    transition: all 0.3s ease;
}

.btn-empty:hover {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

/* Loading Spinner */
.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}

.spinner-small {
    width: 30px;
    height: 30px;
    border: 3px solid #f1f3f5;
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard {
        padding: 1rem;
    }
    
    .welcome-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-grid.two-columns {
        grid-template-columns: 1fr;
    }
    
    .card.full-width {
        grid-column: span 1;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .action-btn {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-text h1 {
        font-size: 1.4rem;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>