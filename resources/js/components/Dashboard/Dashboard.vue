<template>
    <div class="dashboard">
        <!-- Module Header -->
        <div class="module-header">
            <h2 class="module-title">
                <i class="fas fa-th-large"></i> Dashboard
            </h2>
            <p class="module-subtitle">Overview of your academic schedule</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" @click="navigateTo('schedule')">
                <i class="fas fa-book-open fa-2x stat-icon" style="color: #4361ee;"></i>
                <div class="stat-number">{{ stats.classes_count }}</div>
                <div class="stat-label">Classes Today</div>
            </div>
            <div class="stat-card" @click="navigateTo('tasks')">
                <i class="fas fa-tasks fa-2x stat-icon" style="color: #f72585;"></i>
                <div class="stat-number">{{ stats.tasks_count }}</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
            <div class="stat-card" @click="navigateTo('notes')">
                <i class="fas fa-sticky-note fa-2x stat-icon" style="color: #4cc9f0;"></i>
                <div class="stat-number">{{ stats.notes_count }}</div>
                <div class="stat-label">Notes</div>
            </div>
            <div class="stat-card" @click="navigateTo('reminders')">
                <i class="fas fa-bell fa-2x stat-icon" style="color: #7209b7;"></i>
                <div class="stat-number">{{ stats.reminders_count }}</div>
                <div class="stat-label">Upcoming Reminders</div>
            </div>
        </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Today's Classes Card -->
            <div class="card" @click="navigateTo('schedule')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day"></i> Today's Classes
                    </h3>
                    <a :href="route('schedule.index')" class="btn btn-small" @click.stop>View All</a>
                </div>
                <div class="today-classes">
                    <div v-if="loading.classes" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <template v-else>
                        <div 
                            v-for="classItem in classesToday" 
                            :key="classItem.id"
                            class="class-item"
                            @click.stop="navigateToEdit('schedule', classItem.id)"
                        >
                            <div class="class-time">{{ classItem.time }}</div>
                            <div class="class-name">{{ classItem.name }}</div>
                            <div class="class-location">{{ classItem.location }}</div>
                        </div>
                        <div v-if="classesToday.length === 0" class="empty-state">
                            <i class="fas fa-calendar"></i>
                            <p>No classes today</p>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Upcoming Tasks Card -->
            <div class="card" @click="navigateTo('tasks')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hourglass-half"></i> Upcoming Tasks
                    </h3>
                    <a :href="route('tasks.index')" class="btn btn-small" @click.stop>View All</a>
                </div>
                <div class="upcoming-tasks">
                    <div v-if="loading.tasks" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <template v-else>
                        <div 
                            v-for="task in tasksUpcoming" 
                            :key="task.id"
                            class="task-item"
                            @click.stop="navigateToEdit('tasks', task.id)"
                        >
                            <div class="task-title">{{ task.title }}</div>
                            <div class="task-due">
                                Due: {{ formatDate(task.due_date) }}
                            </div>
                            <span class="task-status" :class="getStatusClass(task.status)">
                                {{ formatStatus(task.status) }}
                            </span>
                        </div>
                        <div v-if="tasksUpcoming.length === 0" class="empty-state">
                            <i class="fas fa-tasks"></i>
                            <p>No upcoming tasks</p>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Recent Notes Card -->
            <div class="card" @click="navigateTo('notes')">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sticky-note"></i> Recent Notes
                    </h3>
                    <a :href="route('notes.index')" class="btn btn-small" @click.stop>View All</a>
                </div>
                <div class="recent-notes">
                    <div v-if="loading.notes" class="loading-spinner">
                        <div class="spinner-small"></div>
                    </div>
                    <template v-else>
                        <div 
                            v-for="note in notesRecent" 
                            :key="note.id"
                            class="note-item"
                            @click.stop="navigateToView('notes', note.id)"
                        >
                            <div class="note-title">{{ note.title }}</div>
                            <div class="note-content">
                                {{ truncateContent(note.content) }}
                            </div>
                            <div v-if="note.tags && note.tags.length" class="note-tags">
                                <span v-for="tag in note.tags" :key="tag" class="tag">
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                        <div v-if="notesRecent.length === 0" class="empty-state">
                            <i class="fas fa-sticky-note"></i>
                            <p>No recent notes</p>
                        </div>
                    </template>
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
        }
    },

    data() {
        return {
            stats: this.initialStats,
            classesToday: this.initialClasses,
            tasksUpcoming: this.initialTasks,
            notesRecent: this.initialNotes,
            loading: {
                classes: false,
                tasks: false,
                notes: false
            }
        };
    },

    mounted() {
        console.log('Dashboard mounted');
        this.startAutoRefresh();
    },

    methods: {
        // Navigation methods
        navigateTo(module) {
            window.location.href = `/${module}`;
        },

        navigateToEdit(module, id) {
            if (module === 'schedule') {
                window.location.href = `/schedule/${id}/edit`;
            } else if (module === 'tasks') {
                window.location.href = `/tasks/${id}/edit`;
            }
        },

        navigateToView(module, id) {
            if (module === 'notes') {
                window.location.href = `/notes/${id}`;
            }
        },

        route(name) {
            // Simple route helper 
            const routes = {
                'schedule.index': '/schedule',
                'tasks.index': '/tasks',
                'notes.index': '/notes',
                'reminders.index': '/reminders'
            };
            return routes[name] || '/';
        },

        // Formatting methods
        formatDate(date) {
            if (!date) return 'No date';
            const dateObj = new Date(date);
            return dateObj.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatStatus(status) {
            const statusMap = {
                'todo': 'To Do',
                'inprogress': 'In Progress',
                'done': 'Done'
            };
            return statusMap[status] || status;
        },

        getStatusClass(status) {
            const classMap = {
                'todo': 'status-todo',
                'inprogress': 'status-inprogress',
                'done': 'status-done'
            };
            return classMap[status] || '';
        },

        truncateContent(content) {
            if (!content) return '';
            return content.length > 50 ? content.substring(0, 50) + '...' : content;
        },

        // Data refresh methods
        async refreshClasses() {
            this.loading.classes = true;
            try {
                const response = await axios.get('/schedule?today=true');
                if (response.data) {
                    this.classesToday = Array.isArray(response.data) ? response.data : [];
                }
            } catch (error) {
                console.error('Failed to refresh classes:', error);
            } finally {
                this.loading.classes = false;
            }
        },

        async refreshTasks() {
            this.loading.tasks = true;
            try {
                const response = await axios.get('/tasks?upcoming=true');
                if (response.data) {
                    this.tasksUpcoming = Array.isArray(response.data) ? response.data : [];
                }
            } catch (error) {
                console.error('Failed to refresh tasks:', error);
            } finally {
                this.loading.tasks = false;
            }
        },

        async refreshNotes() {
            this.loading.notes = true;
            try {
                const response = await axios.get('/notes?recent=true');
                if (response.data) {
                    this.notesRecent = Array.isArray(response.data) ? response.data : [];
                }
            } catch (error) {
                console.error('Failed to refresh notes:', error);
            } finally {
                this.loading.notes = false;
            }
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

        async refreshAll() {
            await Promise.all([
                this.refreshClasses(),
                this.refreshTasks(),
                this.refreshNotes(),
                this.refreshStats()
            ]);
        },

        startAutoRefresh() {
            // Refresh data every 5 minutes
            setInterval(() => {
                this.refreshAll();
            }, 5 * 60 * 1000);
        }
    }
};
</script>

<style scoped>
.dashboard {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Module Header */
.module-header {
    margin-bottom: 2rem;
}

.module-title {
    font-size: 2rem;
    color: var(--primary, #4361ee);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.module-subtitle {
    color: #6c757d;
    font-size: 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #e9ecef;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stat-icon {
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    margin: 0.5rem 0;
}

.stat-label {
    color: #6c757d;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

/* Cards */
.card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f1f3f5;
}

.card-title {
    font-size: 1.2rem;
    color: var(--primary, #4361ee);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.card-title i {
    font-size: 1.2rem;
}

/* Content Areas */
.today-classes,
.upcoming-tasks,
.recent-notes {
    flex: 1;
    max-height: 350px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

/* Scrollbar Styling */
.today-classes::-webkit-scrollbar,
.upcoming-tasks::-webkit-scrollbar,
.recent-notes::-webkit-scrollbar {
    width: 6px;
}

.today-classes::-webkit-scrollbar-track,
.upcoming-tasks::-webkit-scrollbar-track,
.recent-notes::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 10px;
}

.today-classes::-webkit-scrollbar-thumb,
.upcoming-tasks::-webkit-scrollbar-thumb,
.recent-notes::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.today-classes::-webkit-scrollbar-thumb:hover,
.upcoming-tasks::-webkit-scrollbar-thumb:hover,
.recent-notes::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Class Items */
.class-item {
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-radius: 8px;
    background-color: rgba(67, 97, 238, 0.05);
    transition: all 0.2s;
    border-left: 4px solid var(--primary, #4361ee);
    cursor: pointer;
}

.class-item:hover {
    transform: translateY(-2px);
    background-color: rgba(67, 97, 238, 0.1);
}

.class-time {
    font-weight: 600;
    color: var(--primary, #4361ee);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.class-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #212529;
}

.class-location {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Task Items */
.task-item {
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-radius: 8px;
    background-color: rgba(247, 37, 133, 0.05);
    transition: all 0.2s;
    border-left: 4px solid #f72585;
    cursor: pointer;
}

.task-item:hover {
    transform: translateY(-2px);
    background-color: rgba(247, 37, 133, 0.1);
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
}

.task-due {
    color: #f72585;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.task-status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-todo {
    background-color: #fff3cd;
    color: #856404;
}

.status-inprogress {
    background-color: #cce5ff;
    color: #004085;
}

.status-done {
    background-color: #d4edda;
    color: #155724;
}

/* Note Items */
.note-item {
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-radius: 8px;
    background-color: rgba(76, 201, 240, 0.05);
    transition: all 0.2s;
    border-left: 4px solid #4cc9f0;
    cursor: pointer;
}

.note-item:hover {
    transform: translateY(-2px);
    background-color: rgba(76, 201, 240, 0.1);
}

.note-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
}

.note-content {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.note-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag {
    background-color: #e9ecef;
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    font-size: 0.8rem;
    color: #495057;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.75rem;
    color: #dee2e6;
}

.empty-state p {
    margin: 0;
    font-size: 0.95rem;
}

/* Buttons */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-small {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}

.btn:hover {
    transform: translateY(-2px);
    text-decoration: none;
}

.card-header .btn {
    background-color: var(--primary, #4361ee);
    color: white;
}

.card-header .btn:hover {
    background-color: #3451d1;
}

/* Loading States */
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
    border-top-color: var(--primary, #4361ee);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.class-item,
.task-item,
.note-item {
    animation: slideIn 0.3s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .card {
        padding: 1rem;
    }
    
    .card-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .card-header .btn {
        align-self: flex-start;
    }
}

@media (max-width: 480px) {
    .module-title {
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-size: 1.75rem;
    }
}
</style>