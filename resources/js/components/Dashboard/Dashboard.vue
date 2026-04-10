<template>
    <div class="dashboard">
        <!-- ========== WELCOME HEADER ========== -->
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

        <!-- ========== STATS GRID ========== -->
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

        <!-- ========== QUICK ACTIONS ========== -->
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

        <!-- ========== LOADING STATE ========== -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading dashboard...</p>
        </div>

        <!-- ========== DASHBOARD CONTENT ========== -->
        <div v-else>
            <!-- Top Row: Classes & Tasks -->
            <div class="dashboard-grid">
                <!-- Today's Classes Card -->
                <div class="card" @click="navigateTo('schedule')">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day"></i> Today's Classes
                        </h3>
                        <a href="#" class="btn-link" @click.stop="navigateTo('schedule')">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div v-if="classesToday.length > 0" class="classes-list">
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
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks Card -->
                <div class="card" @click="navigateTo('tasks')">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hourglass-half"></i> Upcoming Tasks
                        </h3>
                        <a href="#" class="btn-link" @click.stop="navigateTo('tasks')">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div v-if="tasksUpcoming.length > 0" class="tasks-list">
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Notes & Reminders -->
            <div class="two-columns">
                <!-- Recent Notes Card -->
                <div class="card" @click="navigateTo('notes')">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sticky-note"></i> Recent Notes
                        </h3>
                        <a href="#" class="btn-link" @click.stop="navigateTo('notes')">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div v-if="notesRecent.length > 0" class="notes-list">
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
                                        <span v-for="tag in note.tags.slice(0, 2)" :key="tag" class="tag">
                                            {{ tag }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="empty-card">
                            <i class="fas fa-sticky-note"></i>
                            <p>No recent notes</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Reminders Card -->
                <div class="card" @click="navigateTo('reminders')">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bell"></i> Recent Reminders
                        </h3>
                        <a href="#" class="btn-link" @click.stop="navigateTo('reminders')">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div v-if="recentReminders.length > 0" class="reminders-list">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="empty-card">
                            <i class="fas fa-bell-slash"></i>
                            <p>No upcoming reminders</p>
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
    
    data() {
        return {
            stats: {
                classes_count: 0,
                tasks_count: 0,
                notes_count: 0,
                reminders_count: 0
            },
            classesToday: [],
            tasksUpcoming: [],
            notesRecent: [],
            recentReminders: [],
            userName: 'Student',
            loading: true
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
        this.fetchDashboardData();
    },

    methods: {
        // ========== DATA FETCHING ==========
        async fetchDashboardData() {
            this.loading = true;
            try {
                const [statsRes, classesRes, tasksRes, notesRes, remindersRes] = await Promise.all([
                    axios.get('/dashboard/stats'),
                    axios.get('/schedule?today=true'),
                    axios.get('/tasks?upcoming=true'),
                    axios.get('/notes?recent=true'),
                    axios.get('/reminders?recent=true')
                ]);
                
                this.stats = statsRes.data;
                this.classesToday = classesRes.data || [];
                this.tasksUpcoming = tasksRes.data || [];
                this.notesRecent = notesRes.data || [];
                this.recentReminders = remindersRes.data || [];
                this.userName = window.userName || 'Student';
            } catch (error) {
                console.error('Failed to load dashboard:', error);
            } finally {
                this.loading = false;
            }
        },

        // ========== NAVIGATION METHODS ==========
        navigateTo(module) {
            this.$router.push(`/${module}`);
        },

        navigateToAdd(module) {
            this.$router.push(`/${module}`);
        },

        navigateToEdit(module, id) {
            this.$router.push(`/${module}/${id}/edit`);
        },

        navigateToView(module, id) {
            this.$router.push(`/${module}/${id}`);
        },

        // ========== DATE FORMATTING ==========
        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            const now = new Date();
            const diffDays = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Tomorrow';
            if (diffDays === -1) return 'Yesterday';
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        formatReminderTime(dateTime) {
            if (!dateTime) return '';
            return new Date(dateTime).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // ========== REMINDER HELPERS ==========
        isReminderToday(reminder) {
            if (!reminder.reminder_time) return false;
            return new Date(reminder.reminder_time).toDateString() === new Date().toDateString();
        },

        isUrgent(reminder) {
            if (!reminder.reminder_time) return false;
            const hoursUntil = (new Date(reminder.reminder_time) - new Date()) / (1000 * 60 * 60);
            return hoursUntil <= 2 && hoursUntil > 0;
        },

        // ========== TASK HELPERS ==========
        formatStatus(status) {
            const map = { todo: 'To Do', inprogress: 'In Progress', done: 'Done' };
            return map[status] || status;
        },

        getPriorityIcon(priority) {
            const icons = { high: 'fas fa-arrow-up', medium: 'fas fa-minus', low: 'fas fa-arrow-down' };
            return icons[priority] || 'fas fa-flag';
        },

        isOverdue(task) {
            if (task.status === 'done') return false;
            return new Date(task.due_date) < new Date();
        },

        isDueToday(task) {
            if (task.status === 'done') return false;
            return new Date(task.due_date).toDateString() === new Date().toDateString() && !this.isOverdue(task);
        },

        // ========== NOTE HELPERS ==========
        truncateContent(content) {
            if (!content) return '';
            return content.length > 60 ? content.substring(0, 60) + '...' : content;
        }
    }
};
</script>

<style scoped>
.dashboard {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
    background: #f8fafc;
    min-height: 100vh;
}

/* ========== WELCOME HEADER ========== */
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

.current-date {
    background: white;
    padding: 0.75rem 1.5rem;
    border-radius: 40px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    font-weight: 500;
    color: #4361ee;
}

/* ========== STATS GRID ========== */
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: all 0.3s;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
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

.stat-content { flex: 1; }
.stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1; }
.stat-label { color: #64748b; font-size: 0.9rem; margin-top: 0.25rem; }

.stat-trend {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    transition: all 0.3s;
}

.stat-card:hover .stat-trend {
    background: #4361ee;
    color: white;
    transform: translateX(4px);
}

/* ========== QUICK ACTIONS ========== */
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
    transition: all 0.3s;
    font-weight: 500;
    color: #475569;
}

.action-btn:hover {
    background: #4361ee;
    border-color: #4361ee;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67,97,238,0.3);
}

/* ========== CARDS ========== */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: all 0.3s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
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

.card-title i { color: #4361ee; }

.btn-link {
    color: #4361ee;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: gap 0.3s;
}

.btn-link:hover { gap: 0.6rem; }

.card-content {
    padding: 1.25rem 1.5rem;
    min-height: 280px;
}

/* ========== CLASSES LIST ========== */
.classes-list, .tasks-list, .notes-list, .reminders-list {
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
    transition: all 0.3s;
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

.class-info { flex: 1; }
.class-name { font-weight: 600; color: #1e293b; margin-bottom: 0.2rem; }
.class-location { font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.3rem; }
.class-color { width: 4px; height: 40px; border-radius: 2px; }

/* ========== TASKS LIST ========== */
.task-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s;
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

/* ========== NOTES LIST ========== */
.note-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s;
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

/* ========== REMINDERS LIST ========== */
.reminder-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s;
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

.reminder-info { flex: 1; }
.reminder-title { font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
.reminder-time { font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 0.3rem; }

/* ========== EMPTY STATE ========== */
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

/* ========== LOADING STATE ========== */
.loading-state {
    text-align: center;
    padding: 4rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f1f3f5;
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .dashboard { padding: 1rem; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .dashboard-grid { grid-template-columns: 1fr; }
    .two-columns { grid-template-columns: 1fr; gap: 1rem; }
    .action-buttons { justify-content: center; }
    .action-btn { flex: 1; justify-content: center; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .welcome-text h1 { font-size: 1.4rem; }
    .card-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
}
</style>