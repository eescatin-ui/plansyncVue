<template>
    <div class="task-manager">
        <!-- Module Header with Summary -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-tasks"></i> Homework & Tasks
                </h2>
                <div class="task-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total</span>
                        <span class="summary-value">{{ tasks.length }}</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label todo">To Do</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'todo').length }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label inprogress">In Progress</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'inprogress').length }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label done">Done</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'done').length }}</span>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Task
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
                    placeholder="Search tasks..."
                >
            </div>
            
            <div class="sort-options">
                <select v-model="sortBy" @change="applySort" class="sort-select">
                    <option value="due_date">Sort by Due Date</option>
                    <option value="priority">Sort by Priority</option>
                    <option value="title">Sort by Title</option>
                </select>
                <button class="sort-direction" @click="toggleSortDirection">
                    <i :class="sortDirection === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down'"></i>
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button 
                v-for="filterOption in filterOptions" 
                :key="filterOption.value"
                class="filter-tab" 
                :class="{ active: currentFilter === filterOption.value }"
                @click="setFilter(filterOption.value)"
            >
                <i :class="filterOption.icon"></i>
                {{ filterOption.label }}
                <span class="filter-count">{{ getFilterCount(filterOption.value) }}</span>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading tasks...</p>
        </div>

        <!-- Task Grid/List View Toggle -->
        <div v-else class="view-controls">
            <button 
                class="view-toggle" 
                :class="{ active: viewMode === 'grid' }"
                @click="viewMode = 'grid'"
            >
                <i class="fas fa-th-large"></i> Grid
            </button>
            <button 
                class="view-toggle" 
                :class="{ active: viewMode === 'list' }"
                @click="viewMode = 'list'"
            >
                <i class="fas fa-list"></i> List
            </button>
        </div>

        <!-- Task Grid/List -->
        <div :class="['task-container', viewMode === 'grid' ? 'task-grid' : 'task-list']">
            <div 
                v-for="task in filteredAndSortedTasks" 
                :key="task.id"
                class="task-card"
                :class="[
                    `priority-${task.priority}`,
                    `status-${task.status}`,
                    { 
                        'is-overdue': isOverdue(task) && task.status !== 'done',
                        'is-due-today': isDueToday(task) && task.status !== 'done' && !isOverdue(task)
                    }
                ]"
                @click="openEditModal(task)"
            >
                <!-- Card Header with Status and Priority -->
                <div class="card-header">
                    <div class="status-badge" :class="`status-${task.status}`">
                        {{ formatStatus(task.status) }}
                    </div>
                    <div class="priority-badge" :class="`priority-${task.priority}`">
                        <i class="fas fa-flag"></i>
                        {{ formatPriority(task.priority) }}
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    <h3 class="task-title">{{ task.title }}</h3>
                    <p class="task-description">{{ truncateDescription(task.description) }}</p>
                </div>

                <!-- Card Footer with Due Date and Actions -->
                <div class="card-footer">
                    <div class="due-date">
                        <i class="far fa-calendar-alt"></i>
                        {{ formatDate(task.due_date) }}
                        <span v-if="isOverdue(task) && task.status !== 'done'" class="overdue-badge">Overdue</span>
                        <span v-else-if="isDueToday(task) && task.status !== 'done' && !isOverdue(task)" class="due-today-badge">Today</span>
                    </div>
                    
                    <div class="task-actions" @click.stop>
                        <button class="icon-btn edit-btn" @click="openEditModal(task)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="icon-btn delete-btn" @click="deleteTask(task)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Progress Bar for In Progress Tasks -->
                <div v-if="task.status === 'inprogress'" class="progress-indicator">
                    <div class="progress-bar" style="width: 50%"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredAndSortedTasks.length === 0" class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3>{{ emptyStateTitle }}</h3>
                <p>{{ emptyStateMessage }}</p>
                <button class="btn btn-primary" @click="openAddModal">
                    <i class="fas fa-plus"></i> Create Your First Task
                </button>
            </div>
        </div>

       <!-- Add/Edit Task Modal -->
<div v-if="showModal" class="modal-overlay" @click="closeModal">
    <div class="modal-container" @click.stop>
        <div class="modal-header">
            <h5 class="modal-title">
                <i :class="modalMode === 'add' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
                {{ modalMode === 'add' ? 'Create New Task' : 'Edit Task' }}
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
            <form @submit.prevent="saveTask">
                <!-- Task Title -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-heading"></i>
                        Task Title <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        v-model="form.title"
                        placeholder="e.g., Complete math assignment"
                        required
                        :disabled="saving"
                    >
                    <div v-if="errors.title" class="error-message">{{ errors.title[0] }}</div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i>
                        Description
                    </label>
                    <textarea 
                        class="form-control" 
                        v-model="form.description"
                        rows="4"
                        :disabled="saving"
                        placeholder="Add details about this task..."
                    ></textarea>
                    <div v-if="errors.description" class="error-message">{{ errors.description[0] }}</div>
                </div>

                <!-- Two Column Layout for Date and Priority -->
                <div class="form-row">
                    <div class="form-group half">
                        <label>
                            <i class="far fa-calendar-alt"></i>
                            Due Date <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            class="form-control" 
                            v-model="form.due_date"
                            :min="minDateTime"
                            required
                            :disabled="saving"
                        >
                        <div v-if="errors.due_date" class="error-message">{{ errors.due_date[0] }}</div>
                    </div>

                    <div class="form-group half">
                        <label>
                            <i class="fas fa-flag"></i>
                            Priority <span class="text-danger">*</span>
                        </label>
                        <div class="priority-selector">
                            <button 
                                type="button"
                                class="priority-option low"
                                :class="{ active: form.priority === 'low' }"
                                @click="form.priority = 'low'"
                            >
                                <i class="fas fa-flag"></i> Low
                            </button>
                            <button 
                                type="button"
                                class="priority-option medium"
                                :class="{ active: form.priority === 'medium' }"
                                @click="form.priority = 'medium'"
                            >
                                <i class="fas fa-flag"></i> Medium
                            </button>
                            <button 
                                type="button"
                                class="priority-option high"
                                :class="{ active: form.priority === 'high' }"
                                @click="form.priority = 'high'"
                            >
                                <i class="fas fa-flag"></i> High
                            </button>
                        </div>
                        <div v-if="errors.priority" class="error-message">{{ errors.priority[0] }}</div>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-tasks"></i>
                        Status <span class="text-danger">*</span>
                    </label>
                    <div class="status-selector">
                        <button 
                            type="button"
                            class="status-option todo"
                            :class="{ active: form.status === 'todo' }"
                            @click="form.status = 'todo'"
                        >
                            <i class="far fa-circle"></i> To Do
                        </button>
                        <button 
                            type="button"
                            class="status-option inprogress"
                            :class="{ active: form.status === 'inprogress' }"
                            @click="form.status = 'inprogress'"
                        >
                            <i class="fas fa-spinner"></i> In Progress
                        </button>
                        <button 
                            type="button"
                            class="status-option done"
                            :class="{ active: form.status === 'done' }"
                            @click="form.status = 'done'"
                        >
                            <i class="fas fa-check-circle"></i> Done
                        </button>
                    </div>
                    <div v-if="errors.status" class="error-message">{{ errors.status[0] }}</div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button 
                v-if="modalMode === 'edit'" 
                type="button" 
                class="btn btn-danger" 
                @click="deleteTaskFromModal"
                :disabled="deleting"
            >
                <i class="fas fa-trash"></i>
                {{ deleting ? 'Deleting...' : 'Delete Task' }}
            </button>
            
            <button 
                type="button" 
                class="btn btn-secondary" 
                @click="closeModal"
                :disabled="saving || deleting"
            >
                <i class="fas fa-times"></i>
                Cancel
            </button>
            
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="saveTask"
                :disabled="saving || deleting"
            >
                <i :class="saving ? 'fas fa-spinner fa-spin' : (modalMode === 'add' ? 'fas fa-plus' : 'fas fa-save')"></i>
                {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Create Task' : 'Update Task') }}
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
                        Delete Task
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
                        <i class="fas fa-trash-alt fa-4x text-danger"></i>
                    </div>
                    <p class="delete-message">
                        Are you sure you want to delete "<strong>{{ taskToDelete?.title }}</strong>"?
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
    name: 'TaskManager',
    
    props: {
        initialTasks: {
            type: Array,
            default: () => []
        },
        initialFilter: {
            type: String,
            default: 'all'
        }
    },

    data() {
        return {
            tasks: this.initialTasks,
            currentFilter: this.initialFilter,
            searchQuery: '',
            sortBy: 'due_date',
            sortDirection: 'asc',
            viewMode: 'grid',
            loading: false,
            saving: false,
            deleting: false,
            modalMode: 'add',
            showModal: false,
            showDeleteModal: false,
            taskToDelete: null,
            
            form: {
                id: null,
                title: '',
                description: '',
                due_date: '',
                status: 'todo',
                priority: 'medium'
            },
            
            errors: {},
            
            filterOptions: [
                { value: 'all', label: 'All Tasks', icon: 'fas fa-tasks' },
                { value: 'todo', label: 'To Do', icon: 'far fa-circle' },
                { value: 'inprogress', label: 'In Progress', icon: 'fas fa-spinner' },
                { value: 'done', label: 'Completed', icon: 'fas fa-check-circle' }
            ],
            
            searchTimeout: null
        };
    },

    computed: {
        filteredAndSortedTasks() {
        let filtered = this.tasks;

        // Apply search filter
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(task => 
                task.title.toLowerCase().includes(query) ||
                (task.description && task.description.toLowerCase().includes(query))
            );
        }

        // Apply status filter
        if (this.currentFilter !== 'all') {
            filtered = filtered.filter(task => task.status === this.currentFilter);
        }

        // Apply sorting with completed tasks at the bottom
        return filtered.sort((a, b) => {
            // First, always put completed tasks at the bottom
            if (a.status === 'done' && b.status !== 'done') return 1;
            if (a.status !== 'done' && b.status === 'done') return -1;
            
            // If both are completed or both are not completed, apply the selected sort
            if (a.status === 'done' && b.status === 'done') {
                // For completed tasks, sort by completion date (most recent first)
                return new Date(b.updated_at) - new Date(a.updated_at);
            }
            
            // For non-completed tasks, apply the user's selected sort
            let comparison = 0;
            
            if (this.sortBy === 'due_date') {
                comparison = new Date(a.due_date) - new Date(b.due_date);
            } else if (this.sortBy === 'priority') {
                const priorityWeight = { high: 3, medium: 2, low: 1 };
                comparison = priorityWeight[a.priority] - priorityWeight[b.priority];
            } else if (this.sortBy === 'title') {
                comparison = a.title.localeCompare(b.title);
            }
            
            return this.sortDirection === 'asc' ? comparison : -comparison;
        });
    },


        emptyStateTitle() {
            if (this.searchQuery) {
                return 'No results found';
            }
            
            switch(this.currentFilter) {
                case 'todo':
                    return 'No tasks to do';
                case 'inprogress':
                    return 'No tasks in progress';
                case 'done':
                    return 'No completed tasks';
                default:
                    return 'No tasks yet';
            }
        },

        emptyStateMessage() {
            if (this.searchQuery) {
                return `No tasks match "${this.searchQuery}". Try a different search term.`;
            }
            
            switch(this.currentFilter) {
                case 'todo':
                    return 'You have no pending tasks. Time to add some!';
                case 'inprogress':
                    return 'No tasks are currently in progress.';
                case 'done':
                    return 'You haven\'t completed any tasks yet.';
                default:
                    return 'Get started by creating your first task.';
            }
        },

        minDateTime() {
            const now = new Date();
            return now.toISOString().slice(0, 16);
        }
    },

    mounted() {
        console.log('TaskManager mounted');
        this.setDefaultDueDate();
        this.debouncedSearch = _.debounce(this.applySearch, 300);
    },

    methods: {
        getFilterCount(filterValue) {
            if (filterValue === 'all') {
                return this.tasks.length;
            }
            return this.tasks.filter(t => t.status === filterValue).length;
        },

isOverdue(task) {
    if (task.status === 'done') return false;
    const dueDate = new Date(task.due_date);
    const now = new Date();

    dueDate.setHours(0, 0, 0, 0);
    now.setHours(0, 0, 0, 0);
    return dueDate < now;
},

isDueToday(task) {
    if (task.status === 'done') return false;
    const dueDate = new Date(task.due_date);
    const today = new Date();
    // Reset time part to compare dates only
    dueDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    return dueDate.getTime() === today.getTime();
},

formatDate(date) {
    if (!date) return 'No date';
    
    const dateObj = new Date(date);
    const now = new Date();
    
    // Reset time part to compare dates only
    const dueDate = new Date(dateObj);
    dueDate.setHours(0, 0, 0, 0);
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    // Compare dates
    if (dueDate.getTime() === today.getTime()) {
        return 'Today';
    } else if (dueDate.getTime() === tomorrow.getTime()) {
        return 'Tomorrow';
    } else if (dueDate.getTime() === yesterday.getTime()) {
        return 'Yesterday';
    }
    
    // For other dates, show formatted date
    return dateObj.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: dateObj.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
},
        truncateDescription(description) {
            if (!description) return 'No description';
            return description.length > 100 ? description.substring(0, 100) + '...' : description;
        },
setDefaultDueDate() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(12, 0, 0, 0);
    this.form.due_date = tomorrow.toISOString().slice(0, 16);
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
                day: 'numeric',
                year: dateObj.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
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

        formatPriority(priority) {
            const priorityMap = {
                'low': 'Low',
                'medium': 'Medium',
                'high': 'High'
            };
            return priorityMap[priority] || priority;
        },

        setFilter(filter) {
            this.currentFilter = filter;
            const url = new URL(window.location);
            url.searchParams.set('filter', filter);
            window.history.pushState({}, '', url);
        },

        applySearch() {},

        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        applySort() {},

        async fetchTasks() {
            this.loading = true;
            try {
                const response = await axios.get('/tasks');
                if (Array.isArray(response.data)) {
                    this.tasks = response.data;
                } else if (response.data.data) {
                    this.tasks = response.data.data;
                }
            } catch (error) {
                console.error('Failed to fetch tasks:', error);
                this.showNotification('Failed to load tasks.', 'error');
            } finally {
                this.loading = false;
            }
        },

        openAddModal() {
            this.modalMode = 'add';
            this.form = {
                id: null,
                title: '',
                description: '',
                due_date: '',
                status: 'todo',
                priority: 'medium'
            };
            this.setDefaultDueDate();
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        async openEditModal(task) {
            this.modalMode = 'edit';
            this.showModal = true;
            document.body.style.overflow = 'hidden';
            
            this.form = {
                id: task.id,
                title: 'Loading...',
                description: 'Loading...',
                due_date: '',
                status: 'todo',
                priority: 'medium'
            };
            
            try {
                const response = await axios.get(`/tasks/${task.id}/edit`);
                const taskData = response.data;
                
                let dueDate = taskData.due_date;
                if (dueDate && dueDate.includes('T')) {
                    dueDate = dueDate.substring(0, 16);
                }
                
                this.form = {
                    id: taskData.id,
                    title: taskData.title,
                    description: taskData.description || '',
                    due_date: dueDate || '',
                    status: taskData.status,
                    priority: taskData.priority
                };
                
                this.errors = {};
            } catch (error) {
                console.error('Error loading task data:', error);
                this.showNotification('Failed to load task data.', 'error');
                this.closeModal();
            }
        },

        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = '';
        },

        async saveTask() {
            this.saving = true;
            this.errors = {};
            
            try {
                if (!this.form.title.trim()) {
                    this.errors.title = ['Task title is required.'];
                    this.saving = false;
                    return;
                }
                
                if (!this.form.due_date) {
                    this.errors.due_date = ['Due date is required.'];
                    this.saving = false;
                    return;
                }
                
                const formData = {
                    title: this.form.title,
                    description: this.form.description,
                    due_date: this.form.due_date,
                    status: this.form.status,
                    priority: this.form.priority
                };
                
                let response;
                if (this.modalMode === 'add') {
                    response = await axios.post('/tasks', formData);
                    this.tasks.push(response.data);
                    this.showNotification('Task created successfully!', 'success');
                } else {
                    response = await axios.put(`/tasks/${this.form.id}`, formData);
                    const index = this.tasks.findIndex(t => t.id === this.form.id);
                    if (index !== -1) {
                        this.tasks.splice(index, 1, response.data);
                    }
                    this.showNotification('Task updated successfully!', 'success');
                }
                
                this.closeModal();
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    const errorMessages = Object.values(this.errors).flat().join('\n');
                    this.showNotification(`Validation errors:\n${errorMessages}`, 'error');
                } else {
                    this.showNotification('Failed to save task. Please try again.', 'error');
                }
            } finally {
                this.saving = false;
            }
        },

        deleteTask(task) {
            this.taskToDelete = {
                id: task.id,
                title: task.title
            };
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        deleteTaskFromModal() {
            if (!this.form || !this.form.id) return;
            
            this.taskToDelete = {
                id: this.form.id,
                title: this.form.title
            };
            
            this.closeModal();
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.taskToDelete = null;
            this.deleting = false;
            document.body.style.overflow = '';
        },

        async confirmDelete() {
            if (!this.taskToDelete || !this.taskToDelete.id) return;
            
            this.deleting = true;
            
            try {
                await axios.delete(`/tasks/${this.taskToDelete.id}`);
                
                const index = this.tasks.findIndex(t => t.id === this.taskToDelete.id);
                if (index !== -1) {
                    this.tasks.splice(index, 1);
                }
                
                this.showNotification('Task deleted successfully!', 'success');
                this.closeDeleteModal();
                
            } catch (error) {
                this.showNotification('Failed to delete task. Please try again.', 'error');
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
.task-manager {
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

.task-summary {
    display: flex;
    gap: 1.5rem;
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
}

.summary-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.summary-label.todo { color: #856404; }
.summary-label.inprogress { color: #721c24; }
.summary-label.done { color: #155724; }

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
    transition: all 0.3s ease;
}

.search-box:focus-within {
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    transform: translateY(-1px);
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
    font-size: 0.95rem;
    cursor: pointer;
    outline: none;
    transition: all 0.3s ease;
}

.sort-select:hover {
    border-color: #4361ee;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.sort-select:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
    transition: all 0.3s ease;
}

.sort-direction:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-color: #4361ee;
    color: #4361ee;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.75rem 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 30px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #dee2e6;
}

.filter-tab.active {
    background: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
    transform: scale(1.02);
    box-shadow: 0 8px 16px rgba(67, 97, 238, 0.3);
}

.filter-tab.active .filter-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.filter-tab .filter-count {
    background: #f1f3f5;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-left: 0.5rem;
    transition: all 0.3s ease;
}

/* View Controls */
.view-controls {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    justify-content: flex-end;
}

.view-toggle {
    padding: 0.5rem 1rem;
    border: 1px solid #e9ecef;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.view-toggle:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.view-toggle.active {
    background: var(--primary, #4361ee);
    color: white;
    border-color: var(--primary, #4361ee);
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(67, 97, 238, 0.3);
}

/* Task Container */
.task-container {
    min-height: 400px;
}

.task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.task-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.task-list .task-card {
    display: flex;
    flex-direction: row;
    align-items: center;
    height: auto;
    padding: 1rem;
}

.task-list .card-body {
    flex: 1;
    margin: 0 1rem;
}

.task-list .task-description {
    display: none;
}

.task-list .card-footer {
    width: auto;
    border-top: none;
    padding-top: 0;
}

/* Task Card */
.task-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
    animation: slideIn 0.3s ease-out;
}

.task-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    border-color: #e9ecef;
}

.task-card.overdue {
    border-left: 4px solid #dc3545;
}

.task-card.priority-high {
    border-top: 4px solid #dc3545 !important;
}

.task-card.priority-medium {
    border-top: 4px solid #ffc107 !important;
}

.task-card.priority-low {
    border-top: 4px solid #28a745 !important;
}

/* Completed Tasks */
.task-card.status-done {
    opacity: 0.8;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #6c757d !important;
    filter: grayscale(0.7);
}

.task-card.status-done:hover {
    opacity: 1;
    filter: grayscale(0.5);
    background: linear-gradient(135deg, #ffffff 0%, #f1f3f5 100%);
}

.task-card.status-done .task-title {
    color: #6c757d;
    text-decoration: line-through;
    font-style: italic;
}

.task-card.status-done .status-badge.status-done {
    background: #6c757d !important;
    color: white !important;
}

/* Overdue Tasks */
.task-card.is-overdue {
    background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    border-left: 4px solid #9c27b0 !important;
    box-shadow: 0 4px 12px rgba(156, 39, 176, 0.15);
}

.task-card.is-overdue .due-date {
    color: #9c27b0 !important;
    font-weight: 600;
}

.task-card.is-overdue .overdue-badge {
    background: #9c27b0;
    color: white;
}

/* Due Today */
.task-card.is-due-today {
    background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%);
    border-left: 4px solid #ffb300 !important;
}

.task-card.is-due-today .due-date {
    color: #ff8f00 !important;
    font-weight: 600;
}

/* Card Header */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.status-todo {
    background: #fff3cd;
    color: #856404;
}

.status-badge.status-inprogress {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.status-done {
    background: #28a745;
    color: white;
}

.priority-badge {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.priority-badge.priority-high {
    color: #dc3545;
}

.priority-badge.priority-medium {
    color: #ffc107;
}

.priority-badge.priority-low {
    color: #28a745;
}

/* Card Body */
.card-body {
    margin-bottom: 1rem;
}

.task-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.task-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Card Footer */
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.8rem;
    border-top: 1px solid #f1f3f5;
}

.due-date {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.due-date.overdue {
    color: #dc3545;
    font-weight: 500;
}

.overdue-badge {
    background: #dc3545;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

.due-today-badge {
    background: #ffb300;
    color: #212529;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    margin-left: 0.5rem;
    font-weight: 600;
}

.task-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover .task-actions {
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
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.icon-btn:hover {
    transform: scale(1.15);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.edit-btn:hover {
    background: var(--primary, #4361ee);
    color: white;
}

.delete-btn:hover {
    background: #dc3545;
    color: white;
}

/* Progress Indicator */
.progress-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #f1f3f5;
}

.progress-bar {
    height: 100%;
    background: var(--primary, #4361ee);
    transition: width 0.3s ease;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    grid-column: 1 / -1;
    animation: slideIn 0.5s ease-out;
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
    animation: fadeIn 0.3s ease-out;
}

/* Modal Container - Wider with Vertical Scroll */
.modal-container {
    background: white;
    border-radius: 16px;
    width: 95%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.4s ease;
}

/* Custom Scrollbar Styling */
.modal-container::-webkit-scrollbar {
    width: 8px;
}

.modal-container::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.modal-container.modal-sm {
    max-width: 450px;
}

.modal-header {
    padding: 1.5rem 2rem;
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
    font-size: 1.35rem;
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
    transition: all 0.3s ease;
}

.btn-close:hover:not(:disabled) {
    background: #f8f9fa;
    color: #dc3545;
    transform: rotate(90deg);
}

.modal-body {
    padding: 2rem;
    overflow-y: visible;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    position: sticky;
    bottom: 0;
    background: white;
    z-index: 1;
}

/* Form Styles */
.form-row {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
    flex: 1;
}

.form-group.half {
    flex: 0 0 calc(50% - 0.75rem);
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
}

.form-control:hover:not(:disabled) {
    border-color: #4361ee;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    background: white;
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Priority Selector */
.priority-selector {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.25rem;
    width: 100%;
}

.priority-option {
    flex: 1;
    padding: 0.75rem 0.5rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.95rem;
    min-width: 90px;
}

/* Priority Option Colors */
.priority-option.low {
    color: #28a745;
    border-color: #28a745;
}

.priority-option.low:hover:not(.active) {
    background: linear-gradient(145deg, #e8f5e9, #c8e6c9);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.priority-option.low.active {
    background: linear-gradient(145deg, #28a745, #218838);
    color: white;
    border-color: #28a745;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
}

.priority-option.medium {
    color: #ffc107;
    border-color: #ffc107;
}

.priority-option.medium:hover:not(.active) {
    background: linear-gradient(145deg, #fff9e6, #fff3cd);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2);
}

.priority-option.medium.active {
    background: linear-gradient(145deg, #ffc107, #e0a800);
    color: #212529;
    border-color: #ffc107;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
}

.priority-option.high {
    color: #dc3545;
    border-color: #dc3545;
}

.priority-option.high:hover:not(.active) {
    background: linear-gradient(145deg, #ffebee, #ffcdd2);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
}

.priority-option.high.active {
    background: linear-gradient(145deg, #dc3545, #c82333);
    color: white;
    border-color: #dc3545;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(220, 53, 69, 0.3);
}

/* Status Selector */
.status-selector {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.25rem;
    width: 100%;
}

.status-option {
    flex: 1;
    padding: 0.75rem 0.5rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.95rem;
    min-width: 100px;
    white-space: nowrap;
}

/* Status Option Colors */
.status-option.todo {
    color: #856404;
    border-color: #ffc107;
}

.status-option.todo:hover:not(.active) {
    background: linear-gradient(145deg, #fff3cd, #ffe69c);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2);
}

.status-option.todo.active {
    background: linear-gradient(145deg, #ffc107, #e0a800);
    color: #212529;
    border-color: #ffc107;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
}

.status-option.inprogress {
    color: #004085;
    border-color: #17a2b8;
}

.status-option.inprogress:hover:not(.active) {
    background: linear-gradient(145deg, #d1ecf1, #bee5eb);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.2);
}

.status-option.inprogress.active {
    background: linear-gradient(145deg, #17a2b8, #138496);
    color: white;
    border-color: #17a2b8;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(23, 162, 184, 0.3);
}

.status-option.done {
    color: #155724;
    border-color: #28a745;
}

.status-option.done:hover:not(.active) {
    background: linear-gradient(145deg, #d4edda, #c3e6cb);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.status-option.done.active {
    background: linear-gradient(145deg, #28a745, #218838);
    color: white;
    border-color: #28a745;
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
}

/* Modal Footer Buttons */
.modal-footer .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.modal-footer .btn-primary {
    background: linear-gradient(145deg, #4361ee, #3451d1);
    color: white;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.modal-footer .btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(67, 97, 238, 0.4);
}

.modal-footer .btn-secondary {
    background: linear-gradient(145deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
}

.modal-footer .btn-secondary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(108, 117, 125, 0.3);
}

.modal-footer .btn-danger {
    background: linear-gradient(145deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.modal-footer .btn-danger:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(220, 53, 69, 0.4);
}

.modal-footer .btn:disabled {
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

/* Animation Keyframes */
@keyframes spin {
    to { transform: rotate(360deg); }
}

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

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
    
    .task-summary {
        flex-wrap: wrap;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .filter-tabs {
        justify-content: center;
    }
    
    .modal-container {
        max-width: 95%;
        width: 95%;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1.5rem;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .form-group.half {
        flex: 1;
        width: 100%;
    }
}

@media (max-width: 600px) {
    .priority-selector,
    .status-selector {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .priority-option,
    .status-option {
        width: 100%;
        min-width: 100%;
    }
    
    .status-option {
        white-space: normal;
        padding: 0.75rem;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .task-grid {
        grid-template-columns: 1fr;
    }
    
    .module-title {
        font-size: 1.5rem;
    }
    
    .summary-item {
        padding: 0.4rem 0.8rem;
    }
    
    .summary-value {
        font-size: 1rem;
    }
}
</style>