<template>
    <div class="reminder-manager">
        <!-- Module Header with Summary -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-bell"></i> Reminders
                </h2>
                <div class="reminder-summary">
                    <div class="summary-item" @click="setFilter('class')" :class="{ active: currentFilter === 'class' }">
                        <i class="fas fa-book summary-icon class"></i>
                        <span class="summary-label">Classes</span>
                        <span class="summary-value">{{ getClassCount }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('task')" :class="{ active: currentFilter === 'task' }">
                        <i class="fas fa-tasks summary-icon task"></i>
                        <span class="summary-label">Tasks</span>
                        <span class="summary-value">{{ getTaskCount }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('personal')" :class="{ active: currentFilter === 'personal' }">
                        <i class="fas fa-user summary-icon personal"></i>
                        <span class="summary-label">Personal</span>
                        <span class="summary-value">{{ getPersonalCount }}</span>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Reminder
            </button>
        </div>

        <!-- Quick Actions Bar -->
        <div class="quick-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="debouncedSearch"
                    placeholder="Search reminders..."
                >
                <button v-if="searchQuery" class="clear-search" @click="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="view-controls">
                <button 
                    class="view-toggle" 
                    :class="{ active: viewMode === 'grid' }"
                    @click="viewMode = 'grid'"
                >
                    <i class="fas fa-th-large"></i>
                </button>
                <button 
                    class="view-toggle" 
                    :class="{ active: viewMode === 'list' }"
                    @click="viewMode = 'list'"
                >
                    <i class="fas fa-list"></i>
                </button>
            </div>

            <div class="sort-options">
                <select v-model="sortBy" class="sort-select">
                    <option value="time">Sort by Time</option>
                    <option value="title">Sort by Title</option>
                    <option value="type">Sort by Type</option>
                </select>
                <button class="sort-direction" @click="toggleSortDirection">
                    <i :class="sortDirection === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down'"></i>
                </button>
            </div>
        </div>

        <!-- View Toggle for Today/All -->
        <div class="view-toggle-tabs">
            <button 
                class="view-tab" 
                :class="{ active: showAll === false }"
                @click="showAll = false"
            >
                <i class="fas fa-sun"></i> Today Only
            </button>
            <button 
                class="view-tab" 
                :class="{ active: showAll === true }"
                @click="showAll = true"
            >
                <i class="fas fa-calendar-alt"></i> All Reminders
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading reminders...</p>
        </div>

        <!-- Reminder Content -->
        <div v-else>
            <!-- Today's Classes Section -->
            <div v-if="groupedReminders.today.classes.length > 0 && (currentFilter === 'all' || currentFilter === 'class')" class="time-section">
                <div class="section-header today">
                    <i class="fas fa-sun"></i>
                    <h3>Today's Classes</h3>
                    <span class="section-count">{{ groupedReminders.today.classes.length }}</span>
                </div>
                <div :class="['reminder-container', viewMode === 'grid' ? 'reminder-grid' : 'reminder-list']">
                    <div 
                        v-for="reminder in groupedReminders.today.classes" 
                        :key="reminder.id"
                        class="reminder-card"
                        :class="getReminderClasses(reminder)"
                    >
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="reminder-time">
                                <i :class="getTimeIcon(reminder)"></i>
                                <span>{{ reminder.time || formatTime(reminder.reminder_time) }}</span>
                            </div>
                            <div class="reminder-badge" :class="getBadgeClass(reminder)">
                                <i :class="getBadgeIcon(reminder)"></i>
                                {{ getBadgeLabel(reminder) }}
                            </div>
                        </div>

                        <!-- Card Body with Icon -->
                        <div class="card-body">
                            <div class="card-icon">
                                <i :class="getCardIcon(reminder)" :style="{ color: getIconColor(reminder) }"></i>
                            </div>
                            <div class="card-content">
                                <h3 class="reminder-title">{{ reminder.title }}</h3>
                                <div v-if="reminder.location" class="reminder-subtitle">
                                    <i class="fas fa-map-marker-alt"></i> {{ reminder.location }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="reminder-source">
                                <i :class="getSourceIcon(reminder)"></i>
                                <span>{{ getSourceLabel(reminder) }}</span>
                            </div>
                            
                            <div class="reminder-actions" @click.stop>
                                <a :href="`/schedule`" class="icon-btn view-btn" title="View Schedule">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Tasks Section -->
            <div v-if="groupedReminders.today.tasks.length > 0 && (currentFilter === 'all' || currentFilter === 'task')" class="time-section">
                <div class="section-header today-task">
                    <i class="fas fa-tasks"></i>
                    <h3>Today's Tasks</h3>
                    <span class="section-count">{{ groupedReminders.today.tasks.length }}</span>
                </div>
                <div :class="['reminder-container', viewMode === 'grid' ? 'reminder-grid' : 'reminder-list']">
                    <div 
                        v-for="reminder in groupedReminders.today.tasks" 
                        :key="reminder.id"
                        class="reminder-card task-reminder"
                        @click="openEditModal(reminder)"
                    >
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="reminder-time">
                                <i class="fas fa-clock"></i>
                                <span>{{ formatTime(reminder.reminder_time) }}</span>
                            </div>
                            
                            <div class="reminder-badge task-badge">
                                <i class="fas fa-tasks"></i>
                                Task
                            </div>
                        </div>

                        <!-- Card Body with Icon -->
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fas fa-check-circle" :style="{ color: getPriorityColor(reminder.priority) }"></i>
                            </div>
                            <div class="card-content">
                                <h3 class="reminder-title">{{ reminder.title }}</h3>
                                <div v-if="reminder.subtitle" class="reminder-subtitle">
                                    {{ reminder.subtitle }}
                                </div>
                                <div v-if="reminder.status" class="task-status-badge" :class="'status-' + reminder.status">
                                    {{ formatStatus(reminder.status) }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="reminder-source">
                                <i class="fas fa-check-circle"></i>
                                <span>Task</span>
                            </div>
                            
                            <div class="reminder-actions" @click.stop>
                                <a :href="`/tasks`" class="icon-btn view-btn" title="View Tasks">
                                    <i class="fas fa-tasks"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tomorrow's Sections (only shown when showAll is true) -->
            <template v-if="showAll">
                <!-- Tomorrow's Classes Section -->
                <div v-if="groupedReminders.tomorrow.classes.length > 0 && (currentFilter === 'all' || currentFilter === 'class')" class="time-section">
                    <div class="section-header tomorrow">
                        <i class="fas fa-cloud-sun"></i>
                        <h3>Tomorrow's Classes</h3>
                        <span class="section-count">{{ groupedReminders.tomorrow.classes.length }}</span>
                    </div>
                    <div :class="['reminder-container', viewMode === 'grid' ? 'reminder-grid' : 'reminder-list']">
                        <div 
                            v-for="reminder in groupedReminders.tomorrow.classes" 
                            :key="reminder.id"
                            class="reminder-card"
                            :class="getReminderClasses(reminder)"
                        >
                            <!-- Card Header -->
                            <div class="card-header">
                                <div class="reminder-time">
                                    <i :class="getTimeIcon(reminder)"></i>
                                    <span>{{ reminder.time || formatTime(reminder.reminder_time) }}</span>
                                </div>
                                <div class="reminder-badge" :class="getBadgeClass(reminder)">
                                    <i :class="getBadgeIcon(reminder)"></i>
                                    {{ getBadgeLabel(reminder) }}
                                </div>
                            </div>

                            <!-- Card Body with Icon -->
                            <div class="card-body">
                                <div class="card-icon">
                                    <i :class="getCardIcon(reminder)" :style="{ color: getIconColor(reminder) }"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="reminder-title">{{ reminder.title }}</h3>
                                    <div v-if="reminder.location" class="reminder-subtitle">
                                        <i class="fas fa-map-marker-alt"></i> {{ reminder.location }}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer">
                                <div class="reminder-source">
                                    <i :class="getSourceIcon(reminder)"></i>
                                    <span>{{ getSourceLabel(reminder) }}</span>
                                </div>
                                
                                <div class="reminder-actions" @click.stop>
                                    <a :href="`/schedule`" class="icon-btn view-btn" title="View Schedule">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tomorrow's Tasks Section -->
                <div v-if="groupedReminders.tomorrow.tasks.length > 0 && (currentFilter === 'all' || currentFilter === 'task')" class="time-section">
                    <div class="section-header tomorrow-task">
                        <i class="fas fa-tasks"></i>
                        <h3>Tomorrow's Tasks</h3>
                        <span class="section-count">{{ groupedReminders.tomorrow.tasks.length }}</span>
                    </div>
                    <div :class="['reminder-container', viewMode === 'grid' ? 'reminder-grid' : 'reminder-list']">
                        <div 
                            v-for="reminder in groupedReminders.tomorrow.tasks" 
                            :key="reminder.id"
                            class="reminder-card task-reminder"
                            @click="openEditModal(reminder)"
                        >
                            <!-- Card Header -->
                            <div class="card-header">
                                <div class="reminder-time">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ formatTime(reminder.reminder_time) }}</span>
                                </div>
                                
                                <div class="reminder-badge task-badge">
                                    <i class="fas fa-tasks"></i>
                                    Task
                                </div>
                            </div>

                            <!-- Card Body with Icon -->
                            <div class="card-body">
                                <div class="card-icon">
                                    <i class="fas fa-check-circle" :style="{ color: getPriorityColor(reminder.priority) }"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="reminder-title">{{ reminder.title }}</h3>
                                    <div v-if="reminder.subtitle" class="reminder-subtitle">
                                        {{ reminder.subtitle }}
                                    </div>
                                    <div v-if="reminder.status" class="task-status-badge" :class="'status-' + reminder.status">
                                        {{ formatStatus(reminder.status) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer">
                                <div class="reminder-source">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Task</span>
                                </div>
                                
                                <div class="reminder-actions" @click.stop>
                                    <a :href="`/tasks`" class="icon-btn view-btn" title="View Tasks">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Personal Reminders Section (Always shown) -->
            <div v-if="groupedReminders.personal.length > 0 && (currentFilter === 'all' || currentFilter === 'personal')" class="time-section">
                <div class="section-header personal">
                    <i class="fas fa-user"></i>
                    <h3>Personal Reminders</h3>
                    <span class="section-count">{{ groupedReminders.personal.length }}</span>
                </div>
                <div :class="['reminder-container', viewMode === 'grid' ? 'reminder-grid' : 'reminder-list']">
                    <div 
                        v-for="reminder in groupedReminders.personal" 
                        :key="reminder.id"
                        class="reminder-card user-reminder"
                        @click="openEditModal(reminder)"
                    >
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="reminder-time">
                                <i class="far fa-clock"></i>
                                <span>{{ formatDateTime(reminder.reminder_time) }}</span>
                            </div>
                            
                            <div class="reminder-badge user-badge">
                                <i class="fas fa-bell"></i>
                                Reminder
                            </div>
                        </div>

                        <!-- Card Body with Icon -->
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="fas fa-bell" style="color: #28a745"></i>
                            </div>
                            <div class="card-content">
                                <h3 class="reminder-title">{{ reminder.title }}</h3>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="reminder-source">
                                <i class="fas fa-user"></i>
                                <span>Personal</span>
                            </div>
                            
                            <div class="reminder-actions" @click.stop>
                                <button class="icon-btn edit-btn" @click="openEditModal(reminder)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="icon-btn delete-btn" @click="deleteReminder(reminder)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredAndSortedReminders.length === 0" class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>No reminders yet</h3>
                <p>Get started by creating your first reminder.</p>
                <button class="btn btn-primary" @click="openAddModal">
                    <i class="fas fa-plus"></i> Create Your First Reminder
                </button>
            </div>
        </div>

        <!-- Add/Edit Reminder Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i :class="modalMode === 'add' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
                        {{ modalMode === 'add' ? 'Create New Reminder' : 'Edit Reminder' }}
                    </h5>
                    <button 
                        type="button" 
                        class="btn-close" 
                        @click="closeModal" 
                        :disabled="saving"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form @submit.prevent="saveReminder">
                        <!-- Reminder Title -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-heading"></i>
                                Reminder Title <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="form.title"
                                placeholder="e.g., Doctor appointment"
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.title" class="error-message">{{ errors.title[0] }}</div>
                        </div>

                        <!-- Time -->
                        <div class="form-group">
                            <label>
                                <i class="far fa-clock"></i>
                                Time <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                class="form-control" 
                                v-model="form.reminder_time"
                                :min="minDateTime"
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.reminder_time" class="error-message">{{ errors.reminder_time[0] }}</div>
                        </div>

                        <!-- Quick Time Options -->
                        <div class="quick-time-options">
                            <button type="button" class="time-option" @click="setTimeLater(15)">+15 min</button>
                            <button type="button" class="time-option" @click="setTimeLater(30)">+30 min</button>
                            <button type="button" class="time-option" @click="setTimeLater(60)">+1 hour</button>
                            <button type="button" class="time-option" @click="setTimeLater(120)">+2 hours</button>
                            <button type="button" class="time-option" @click="setTimeLater(1440)">Tomorrow</button>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button 
                        v-if="modalMode === 'edit'" 
                        type="button" 
                        class="btn btn-danger" 
                        @click="deleteReminderFromModal"
                        :disabled="deleting"
                    >
                        <i class="fas fa-trash"></i>
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
                    
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        @click="closeModal"
                        :disabled="saving || deleting"
                    >
                        Cancel
                    </button>
                    
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        @click="saveReminder"
                        :disabled="saving || deleting"
                    >
                        <i :class="saving ? 'fas fa-spinner fa-spin' : (modalMode === 'add' ? 'fas fa-plus' : 'fas fa-save')"></i>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Create Reminder' : 'Update Reminder') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-container modal-sm" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Delete Reminder
                    </h5>
                    <button 
                        type="button" 
                        class="btn-close" 
                        @click="closeDeleteModal"
                        :disabled="deleting"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="delete-icon">
                        <i class="fas fa-bell-slash fa-4x text-danger"></i>
                    </div>
                    <p class="delete-message">
                        Are you sure you want to delete "<strong>{{ reminderToDelete?.title }}</strong>"?
                    </p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        @click="closeDeleteModal"
                        :disabled="deleting"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-danger" 
                        @click="confirmDelete"
                        :disabled="deleting"
                    >
                        <i v-if="deleting" class="fas fa-spinner fa-spin"></i>
                        {{ deleting ? 'Deleting...' : 'Yes, Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';

export default {
    name: 'ReminderManager',
    
    props: {
        initialReminders: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            reminders: this.initialReminders,
            searchQuery: '',
            currentFilter: 'all',
            viewMode: 'grid',
            showAll: false, // Default to false (Today only)
            sortBy: 'time',
            sortDirection: 'asc',
            loading: false,
            saving: false,
            deleting: false,
            modalMode: 'add',
            showModal: false,
            showDeleteModal: false,
            reminderToDelete: null,
            
            form: {
                id: null,
                title: '',
                reminder_time: ''
            },
            
            errors: {},
            
            searchTimeout: null
        };
    },

    computed: {
        filteredAndSortedReminders() {
            let filtered = [...this.reminders];

            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(reminder => 
                    reminder.title.toLowerCase().includes(query) ||
                    (reminder.subtitle && reminder.subtitle.toLowerCase().includes(query))
                );
            }

            // Apply type filter
            if (this.currentFilter !== 'all') {
                filtered = filtered.filter(reminder => {
                    if (this.currentFilter === 'class') {
                        return reminder.type.includes('class');
                    } else if (this.currentFilter === 'task') {
                        return reminder.type === 'task';
                    } else if (this.currentFilter === 'personal') {
                        return reminder.type === 'user';
                    }
                    return true;
                });
            }

            // Apply sorting
            return filtered.sort((a, b) => {
                let comparison = 0;
                
                if (this.sortBy === 'time') {
                    const timeA = this.getReminderTimeValue(a);
                    const timeB = this.getReminderTimeValue(b);
                    comparison = timeA - timeB;
                } else if (this.sortBy === 'title') {
                    comparison = a.title.localeCompare(b.title);
                } else if (this.sortBy === 'type') {
                    const typeA = a.type || '';
                    const typeB = b.type || '';
                    comparison = typeA.localeCompare(typeB);
                }
                
                return this.sortDirection === 'asc' ? comparison : -comparison;
            });
        },

        groupedReminders() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const dayAfterTomorrow = new Date(tomorrow);
            dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 1);

            // Get today's and tomorrow's day names
            const todayName = today.toLocaleDateString('en-US', { weekday: 'long' });
            const tomorrowName = tomorrow.toLocaleDateString('en-US', { weekday: 'long' });

            const filtered = this.filteredAndSortedReminders;

            return {
                // Today's items
                today: {
                    // Classes today - based on day matching today
                    classes: filtered.filter(r => {
                        if (!r.type.includes('class')) return false;
                        return r.day === todayName;
                    }),
                    
                    // Tasks due today
                    tasks: filtered.filter(r => {
                        if (r.type !== 'task') return false;
                        if (!r.reminder_time) return false;
                        const date = new Date(r.reminder_time);
                        return date >= today && date < tomorrow;
                    })
                },
                
                // Tomorrow's items
                tomorrow: {
                    // Classes tomorrow - based on day matching tomorrow
                    classes: filtered.filter(r => {
                        if (!r.type.includes('class')) return false;
                        return r.day === tomorrowName;
                    }),
                    
                    // Tasks due tomorrow
                    tasks: filtered.filter(r => {
                        if (r.type !== 'task') return false;
                        if (!r.reminder_time) return false;
                        const date = new Date(r.reminder_time);
                        return date >= tomorrow && date < dayAfterTomorrow;
                    })
                },
                
                // Personal reminders (always separate)
                personal: filtered.filter(r => r.type === 'user')
            };
        },

        getClassCount() {
            return this.reminders.filter(r => r.type.includes('class')).length;
        },

        getTaskCount() {
            return this.reminders.filter(r => r.type === 'task').length;
        },

        getPersonalCount() {
            return this.reminders.filter(r => r.type === 'user').length;
        },

        minDateTime() {
            const now = new Date();
            return now.toISOString().slice(0, 16);
        }
    },

    mounted() {
        console.log('ReminderManager mounted with reminders:', this.reminders);
        this.setDefaultTime();
        this.debouncedSearch = _.debounce(this.applySearch, 300);
        
        // Auto-refresh every minute
        setInterval(() => {
            this.$forceUpdate();
        }, 60000);
    },

    methods: {
        getReminderTimeValue(reminder) {
            if (reminder.reminder_time) {
                return new Date(reminder.reminder_time).getTime();
            }
            return Date.now();
        },

        getReminderClasses(reminder) {
            return {
                'class-now': reminder.type === 'class-now',
                'class-upcoming': reminder.type === 'class-upcoming',
                'class-later': reminder.type === 'class-later',
                'class-reminder': reminder.type.includes('class'),
                'task-reminder': reminder.type === 'task',
                'user-reminder': reminder.type === 'user'
            };
        },

        getCardIcon(reminder) {
            if (reminder.type.includes('class')) return 'fas fa-book';
            if (reminder.type === 'task') return 'fas fa-check-circle';
            return 'fas fa-bell';
        },

        getIconColor(reminder) {
            if (reminder.type === 'class-now') return '#e63946';
            if (reminder.type === 'class-upcoming') return '#f72585';
            if (reminder.type.includes('class')) return '#17a2b8';
            if (reminder.type === 'task') {
                return this.getPriorityColor(reminder.priority);
            }
            return '#28a745';
        },

        getPriorityColor(priority) {
            const colors = {
                'high': '#dc3545',
                'medium': '#ffc107',
                'low': '#28a745'
            };
            return colors[priority] || '#6c757d';
        },

        getTimeIcon(reminder) {
            if (reminder.type === 'class-now') return 'fas fa-play-circle';
            if (reminder.type.includes('class')) return 'fas fa-clock';
            if (reminder.type === 'task') return 'fas fa-tasks';
            return 'far fa-bell';
        },

        getBadgeClass(reminder) {
            if (reminder.type === 'class-now') return 'class-badge now';
            if (reminder.type === 'class-upcoming') return 'class-badge upcoming';
            if (reminder.type.includes('class')) return 'class-badge';
            if (reminder.type === 'task') return 'task-badge';
            return 'user-badge';
        },

        getBadgeIcon(reminder) {
            if (reminder.type.includes('class')) return 'fas fa-book';
            if (reminder.type === 'task') return 'fas fa-tasks';
            return 'fas fa-bell';
        },

        getBadgeLabel(reminder) {
            if (reminder.type === 'class-now') return 'Now';
            if (reminder.type === 'class-upcoming') return 'Soon';
            if (reminder.type.includes('class')) return 'Class';
            if (reminder.type === 'task') return 'Task';
            return 'Reminder';
        },

        getSourceIcon(reminder) {
            if (reminder.type.includes('class')) return 'fas fa-calendar-alt';
            if (reminder.type === 'task') return 'fas fa-check-circle';
            return 'fas fa-user';
        },

        getSourceLabel(reminder) {
            if (reminder.type.includes('class')) return 'Class';
            if (reminder.type === 'task') return 'Task';
            return 'Personal';
        },

        formatTime(time) {
            if (!time) return '';
            if (time.includes(' - ')) return time; // Already formatted
            try {
                const date = new Date(time);
                return date.toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            } catch (e) {
                return time;
            }
        },

        formatDateTime(dateTime) {
            if (!dateTime) return '';
            try {
                const date = new Date(dateTime);
                const now = new Date();
                const tomorrow = new Date(now);
                tomorrow.setDate(tomorrow.getDate() + 1);
                
                // Check if it's today
                if (date.toDateString() === now.toDateString()) {
                    return 'Today at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                // Check if it's tomorrow
                else if (date.toDateString() === tomorrow.toDateString()) {
                    return 'Tomorrow at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                // Otherwise show full date
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return dateTime;
            }
        },

        formatStatus(status) {
            const statusMap = {
                'todo': 'To Do',
                'inprogress': 'In Progress',
                'done': 'Done'
            };
            return statusMap[status] || status;
        },

        setDefaultTime() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            this.form.reminder_time = now.toISOString().slice(0, 16);
        },

        setTimeLater(minutes) {
            const later = new Date();
            later.setMinutes(later.getMinutes() + minutes);
            
            if (minutes === 1440) { // Tomorrow
                later.setDate(later.getDate() + 1);
                later.setHours(9, 0, 0, 0);
            }
            
            this.form.reminder_time = later.toISOString().slice(0, 16);
        },

        // Actions
        setFilter(filter) {
            this.currentFilter = filter;
        },

        clearSearch() {
            this.searchQuery = '';
            this.applySearch();
        },

        applySearch() {},

        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        openAddModal() {
            this.modalMode = 'add';
            this.form = {
                id: null,
                title: '',
                reminder_time: ''
            };
            this.setDefaultTime();
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        async openEditModal(reminder) {
            // Only allow editing personal reminders
            if (reminder.type !== 'user') {
                this.showNotification('Only personal reminders can be edited.', 'info');
                return;
            }
            
            this.modalMode = 'edit';
            this.showModal = true;
            document.body.style.overflow = 'hidden';
            
            try {
                const response = await axios.get(`/reminders/${reminder.id}/edit`);
                const reminderData = response.data;
                
                let reminderTime = reminderData.reminder_time;
                if (reminderTime && reminderTime.includes('T')) {
                    reminderTime = reminderTime.substring(0, 16);
                }
                
                this.form = {
                    id: reminderData.id,
                    title: reminderData.title,
                    reminder_time: reminderTime || ''
                };
                
                this.errors = {};
            } catch (error) {
                console.error('Error loading reminder data:', error);
                this.showNotification('Failed to load reminder data.', 'error');
                this.closeModal();
            }
        },

        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = '';
        },

        async saveReminder() {
            this.saving = true;
            this.errors = {};
            
            try {
                if (!this.form.title.trim()) {
                    this.errors.title = ['Reminder title is required.'];
                    this.saving = false;
                    return;
                }
                
                if (!this.form.reminder_time) {
                    this.errors.reminder_time = ['Time is required.'];
                    this.saving = false;
                    return;
                }
                
                const formData = {
                    title: this.form.title,
                    reminder_time: this.form.reminder_time
                };
                
                let response;
                if (this.modalMode === 'add') {
                    response = await axios.post('/reminders', formData);
                    this.reminders.push(response.data);
                    this.showNotification('Reminder created successfully!', 'success');
                } else {
                    response = await axios.put(`/reminders/${this.form.id}`, formData);
                    const index = this.reminders.findIndex(r => r.id === this.form.id);
                    if (index !== -1) {
                        this.reminders.splice(index, 1, response.data);
                    }
                    this.showNotification('Reminder updated successfully!', 'success');
                }
                
                this.closeModal();
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    const errorMessages = Object.values(this.errors).flat().join('\n');
                    this.showNotification(`Validation errors:\n${errorMessages}`, 'error');
                } else {
                    this.showNotification('Failed to save reminder. Please try again.', 'error');
                }
            } finally {
                this.saving = false;
            }
        },

        deleteReminder(reminder) {
            // Only allow deleting personal reminders
            if (reminder.type !== 'user') {
                this.showNotification('Only personal reminders can be deleted.', 'info');
                return;
            }
            
            this.reminderToDelete = {
                id: reminder.id,
                title: reminder.title
            };
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        deleteReminderFromModal() {
            if (!this.form || !this.form.id) return;
            
            this.reminderToDelete = {
                id: this.form.id,
                title: this.form.title
            };
            
            this.closeModal();
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.reminderToDelete = null;
            this.deleting = false;
            document.body.style.overflow = '';
        },

        async confirmDelete() {
            if (!this.reminderToDelete || !this.reminderToDelete.id) return;
            
            this.deleting = true;
            
            try {
                await axios.delete(`/reminders/${this.reminderToDelete.id}`);
                
                const index = this.reminders.findIndex(r => r.id === this.reminderToDelete.id);
                if (index !== -1) {
                    this.reminders.splice(index, 1);
                }
                
                this.showNotification('Reminder deleted successfully!', 'success');
                this.closeDeleteModal();
                
            } catch (error) {
                this.showNotification('Failed to delete reminder. Please try again.', 'error');
                this.deleting = false;
            }
        },

        showNotification(message, type = 'info') {
            alert(message);
        }
    }
};
</script>

<style scoped>
/* Add the new view toggle tabs styles */
.view-toggle-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    justify-content: center;
}

.view-tab {
    padding: 0.75rem 2rem;
    border: 2px solid #e9ecef;
    border-radius: 30px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.view-tab:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.view-tab.active {
    background: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(67, 97, 238, 0.3);
}

.view-tab i {
    font-size: 1.1rem;
}

@media (max-width: 480px) {
    .view-toggle-tabs {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .view-tab {
        width: 100%;
        justify-content: center;
    }
}

/* Keep all your existing styles below */
.reminder-manager {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Header Styles */
.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    flex: 1;
}

.module-title {
    font-size: 2rem;
    color: var(--primary, #4361ee);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.reminder-summary {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
}

.summary-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.summary-item.active {
    background: var(--primary, #4361ee);
}

.summary-item.active .summary-icon,
.summary-item.active .summary-label,
.summary-item.active .summary-value {
    color: white;
}

.summary-icon {
    font-size: 1rem;
}

.summary-icon.class { color: #17a2b8; }
.summary-icon.task { color: #ffb300; }
.summary-icon.personal { color: #28a745; }

.summary-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.summary-value {
    font-weight: 600;
    font-size: 1.1rem;
    color: #212529;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 30px;
    padding: 0.5rem 1rem;
    flex: 1;
    max-width: 400px;
}

.search-box i {
    color: #6c757d;
    margin-right: 0.5rem;
}

.search-box input {
    border: none;
    background: transparent;
    flex: 1;
    outline: none;
    font-size: 0.95rem;
}

.clear-search {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clear-search:hover {
    color: #dc3545;
}

.view-controls {
    display: flex;
    gap: 0.5rem;
}

.view-toggle {
    width: 36px;
    height: 36px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.2s;
}

.view-toggle:hover {
    background: #f8f9fa;
    color: var(--primary, #4361ee);
}

.view-toggle.active {
    background: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
}

.sort-options {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.sort-select {
    padding: 0.5rem 2rem 0.5rem 1rem;
    border: 1px solid #e9ecef;
    border-radius: 20px;
    background: white;
    font-size: 0.9rem;
    cursor: pointer;
    outline: none;
}

.sort-direction {
    width: 36px;
    height: 36px;
    border: 1px solid #e9ecef;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.sort-direction:hover {
    background: #f8f9fa;
}

/* Time Sections */
.time-section {
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid;
}

.section-header.today {
    color: #e63946;
    border-bottom-color: #e63946;
}

.section-header.today-task {
    color: #ffb300;
    border-bottom-color: #ffb300;
}

.section-header.tomorrow {
    color: #f72585;
    border-bottom-color: #f72585;
}

.section-header.tomorrow-task {
    color: #ffb300;
    border-bottom-color: #ffb300;
}

.section-header.personal {
    color: #28a745;
    border-bottom-color: #28a745;
}

.section-header i {
    font-size: 1.25rem;
}

.section-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.section-count {
    margin-left: auto;
    background: #f1f3f5;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
}

/* Reminder Container */
.reminder-container {
    min-height: 200px;
}

.reminder-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.reminder-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reminder-list .reminder-card {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 1rem;
}

.reminder-list .card-body {
    flex: 1;
    margin: 0 1rem;
}

.reminder-list .reminder-subtitle,
.reminder-list .task-status-badge {
    display: none;
}

.reminder-list .card-footer {
    width: auto;
    border-top: none;
    padding-top: 0;
}

/* Reminder Card */
.reminder-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
    border-left: 4px solid;
}

.reminder-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    border-color: #e9ecef;
}

/* Card Type Variations */
.reminder-card.class-now {
    border-left-color: #e63946;
    background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
}

.reminder-card.class-upcoming {
    border-left-color: #f72585;
    background: linear-gradient(135deg, #fff0f7 0%, #ffeaf5 100%);
}

.reminder-card.class-reminder {
    border-left-color: #17a2b8;
}

.reminder-card.task-reminder {
    border-left-color: #ffb300;
}

.reminder-card.user-reminder {
    border-left-color: #28a745;
}

/* Card Header */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.reminder-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.reminder-time i {
    font-size: 1rem;
}

.reminder-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.class-badge {
    background: #d1ecf1;
    color: #0c5460;
}

.class-badge.upcoming {
    background: #f72585;
    color: white;
}

.class-badge.now {
    background: #e63946;
    color: white;
}

.task-badge {
    background: #fff3cd;
    color: #856404;
}

.user-badge {
    background: #d4edda;
    color: #155724;
}

/* Card Body */
.card-body {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.card-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.card-content {
    flex: 1;
}

.reminder-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.reminder-subtitle {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.task-status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
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

/* Card Footer */
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.8rem;
    border-top: 1px solid #f1f3f5;
}

.reminder-source {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.reminder-source i {
    font-size: 0.85rem;
}

.reminder-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.reminder-card:hover .reminder-actions {
    opacity: 1;
}

.icon-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-decoration: none;
}

.icon-btn:hover {
    transform: scale(1.1);
}

.edit-btn:hover {
    background: var(--primary, #4361ee);
    color: white;
}

.delete-btn:hover {
    background: #dc3545;
    color: white;
}

.view-btn:hover {
    background: #28a745;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    grid-column: 1 / -1;
}

.empty-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #495057;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(4px);
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-container.modal-sm {
    max-width: 400px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.btn-close:hover:not(:disabled) {
    background: #f8f9fa;
    color: #dc3545;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    position: sticky;
    bottom: 0;
    background: white;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary, #4361ee);
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Quick Time Options */
.quick-time-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.time-option {
    padding: 0.4rem 1rem;
    border: 1px solid #e9ecef;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.time-option:hover {
    background: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
}

/* Button Styles */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary, #4361ee);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #3451d1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #5a6268;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 4rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f1f3f5;
    border-top-color: var(--primary, #4361ee);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
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

.reminder-card {
    animation: slideIn 0.3s ease-out;
}

/* Delete Modal Specific */
.delete-icon {
    margin-bottom: 1rem;
}

.delete-message {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #dc3545;
}

.text-center {
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .module-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reminder-summary {
        flex-wrap: wrap;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .view-controls {
        width: 100%;
        justify-content: center;
    }
    
    .sort-options {
        width: 100%;
    }
    
    .sort-select {
        flex: 1;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .reminder-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-wrap: wrap;
    }
    
    .section-count {
        margin-left: 0;
    }
}
</style>