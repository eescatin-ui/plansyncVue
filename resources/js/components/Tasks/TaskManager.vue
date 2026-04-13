<template>
    <div class="task-manager">
        <!-- ========== MODULE HEADER ========== -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-tasks"></i> Homework & Tasks
                </h2>
                <div class="task-summary">
                    <div class="summary-item" @click="setFilter('all')" :class="{ active: currentFilter === 'all' }">
                        <i class="fas fa-tasks"></i>
                        <span class="summary-label">Total</span>
                        <span class="summary-value">{{ tasks.length }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('todo')" :class="{ active: currentFilter === 'todo' }">
                        <i class="far fa-circle"></i>
                        <span class="summary-label">To Do</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'todo').length }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('inprogress')" :class="{ active: currentFilter === 'inprogress' }">
                        <i class="fas fa-spinner"></i>
                        <span class="summary-label">In Progress</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'inprogress').length }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('done')" :class="{ active: currentFilter === 'done' }">
                        <i class="fas fa-check-circle"></i>
                        <span class="summary-label">Done</span>
                        <span class="summary-value">{{ tasks.filter(t => t.status === 'done').length }}</span>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>

        <!-- ========== QUICK ACTIONS ========== -->
        <div class="quick-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="debouncedSearch"
                    placeholder="Search tasks..."
                >
                <button v-if="searchQuery" class="clear-search" @click="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sort-options">
                <select v-model="sortBy" class="sort-select">
                    <option value="due_date">Sort by Due Date</option>
                    <option value="priority">Sort by Priority</option>
                    <option value="title">Sort by Title</option>
                </select>
                <button class="sort-direction" @click="toggleSortDirection">
                    <i :class="sortDirection === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down'"></i>
                </button>
            </div>
        </div>

        <!-- ========== VIEW TOGGLE ========== -->
        <div class="view-controls">
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

        <!-- ========== LOADING STATE ========== -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading tasks...</p>
        </div>

        <!-- ========== TASK LIST ========== -->
        <div v-else :class="['task-container', viewMode === 'grid' ? 'task-grid' : 'task-list']">
            <div 
                v-for="task in filteredAndSortedTasks" 
                :key="task.id"
                class="task-card"
                :class="[
                    `priority-${task.priority}`,
                    `status-${task.status}`,
                    { 
                        'is-overdue': isOverdue(task),
                        'is-due-today': isDueToday(task)
                    }
                ]"
                @click="openEditModal(task)"
            >
                <!-- Card Header -->
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

                <!-- Card Footer -->
                <div class="card-footer">
                    <div class="due-date">
                        <i class="far fa-calendar-alt"></i>
                        {{ formatDate(task.due_date) }}
                        <span v-if="isOverdue(task) && task.status !== 'done'" class="overdue-badge">Overdue</span>
                        <span v-else-if="isDueToday(task) && task.status !== 'done'" class="due-today-badge">Today</span>
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

                <!-- Progress Bar -->
                <div v-if="task.status === 'inprogress'" class="progress-indicator">
                    <div class="progress-bar" style="width: 50%"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredAndSortedTasks.length === 0" class="empty-state">
                <i class="fas fa-tasks"></i>
                <h3>{{ emptyStateTitle }}</h3>
                <p>{{ emptyStateMessage }}</p>
                <button class="btn btn-primary" @click="openAddModal">
                    <i class="fas fa-plus"></i> Create Your First Task
                </button>
            </div>
        </div>

        <!-- ========== ADD/EDIT MODAL ========== -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i :class="modalMode === 'add' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
                        {{ modalMode === 'add' ? 'Create New Task' : 'Edit Task' }}
                    </h5>
                    <button type="button" class="btn-close" @click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form @submit.prevent="saveTask">
                        <!-- Task Title -->
                        <div class="form-group">
                            <label>Task Title <span class="text-danger">*</span></label>
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
                            <label>Description</label>
                            <textarea 
                                class="form-control" 
                                v-model="form.description"
                                rows="4"
                                :disabled="saving"
                                placeholder="Add details about this task..."
                            ></textarea>
                            <div v-if="errors.description" class="error-message">{{ errors.description[0] }}</div>
                        </div>

                        <!-- Due Date & Priority -->
                        <div class="form-row">
                            <div class="form-group half">
                                <label>Due Date <span class="text-danger">*</span></label>
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
                                <label>Priority <span class="text-danger">*</span></label>
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
                            <label>Status <span class="text-danger">*</span></label>
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
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="closeModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" @click="saveTask" :disabled="saving">
                        <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Create Task' : 'Update Task') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== DELETE CONFIRMATION MODAL ========== -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-container modal-sm" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">Delete Task</h5>
                    <button type="button" class="btn-close" @click="closeDeleteModal">×</button>
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
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" @click="confirmDelete">
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
    
    data() {
        return {
            tasks: [],
            searchQuery: '',
            currentFilter: 'all',
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
            
            searchTimeout: null
        };
    },

    computed: {
        filteredAndSortedTasks() {
            let filtered = [...this.tasks];

            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(task => 
                    task.title.toLowerCase().includes(query) ||
                    (task.description && task.description.toLowerCase().includes(query))
                );
            }

            // Apply status filter from summary cards
            if (this.currentFilter !== 'all') {
                filtered = filtered.filter(task => task.status === this.currentFilter);
            }

            // Separate tasks into three categories
            // 1. Active tasks (not overdue, not done)
            // 2. Overdue tasks (past due date, not done)
            // 3. Completed tasks (done)
            const activeTasks = filtered.filter(task => {
                return task.status !== 'done' && !this.isOverdue(task);
            });
            
            const overdueTasks = filtered.filter(task => {
                return task.status !== 'done' && this.isOverdue(task);
            });
            
            const completedTasks = filtered.filter(task => task.status === 'done');

            // Sort active tasks by user preference
            const sortedActive = activeTasks.sort((a, b) => {
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

            // Sort overdue tasks by user preference (grouped together at bottom)
            const sortedOverdue = overdueTasks.sort((a, b) => {
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

            // Sort completed tasks by completion date
            const sortedCompleted = completedTasks.sort((a, b) => {
                const aDate = a.completed_at ? new Date(a.completed_at) : new Date(a.updated_at);
                const bDate = b.completed_at ? new Date(b.completed_at) : new Date(b.updated_at);
                return aDate - bDate;
            });

            // Return order: Active → Overdue → Completed
            return [...sortedActive, ...sortedOverdue, ...sortedCompleted];
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
        console.log('TaskManager mounted - fetching tasks...');
        this.fetchTasks();
        this.debouncedSearch = _.debounce(this.applySearch, 300);
        this.setDefaultDueDate();
    },

    methods: {
        // ========== DATA FETCHING ==========
        async fetchTasks() {
            this.loading = true;
            try {
                const response = await axios.get('/tasks');
                console.log('Tasks fetched:', response.data);
                this.tasks = response.data;
            } catch (error) {
                console.error('Failed to fetch tasks:', error);
                this.showNotification('Failed to load tasks.', 'error');
            } finally {
                this.loading = false;
            }
        },

        // ========== HELPER METHODS ==========
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
            dueDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            return dueDate.getTime() === today.getTime() && !this.isOverdue(task);
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
            const dueDate = new Date(dateObj);
            dueDate.setHours(0, 0, 0, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            
            if (dueDate.getTime() === today.getTime()) {
                return 'Today';
            } else if (dueDate.getTime() === tomorrow.getTime()) {
                return 'Tomorrow';
            } else if (dueDate.getTime() === yesterday.getTime()) {
                return 'Yesterday';
            }
            
            return dateObj.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: dateObj.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
            });
        },

        formatStatus(status) {
            const map = { 'todo': 'To Do', 'inprogress': 'In Progress', 'done': 'Done' };
            return map[status] || status;
        },

        formatPriority(priority) {
            const map = { 'low': 'Low', 'medium': 'Medium', 'high': 'High' };
            return map[priority] || priority;
        },

        // ========== FILTER & SORT ==========
        setFilter(filter) {
            this.currentFilter = filter;
        },

        applySearch() {},

        clearSearch() {
            this.searchQuery = '';
        },

        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        // ========== MODAL METHODS ==========
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
                title: task.title,
                description: task.description || '',
                due_date: task.due_date ? task.due_date.substring(0, 16) : '',
                status: task.status,
                priority: task.priority
            };
            this.errors = {};
        },

        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = '';
        },

        // ========== CRUD OPERATIONS ==========
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
                    console.log('Task created:', response.data);
                    this.tasks.push(response.data);
                    this.showNotification('Task created successfully!', 'success');
                } else {
                    response = await axios.put(`/tasks/${this.form.id}`, formData);
                    console.log('Task updated:', response.data);
                    const index = this.tasks.findIndex(t => t.id === this.form.id);
                    if (index !== -1) {
                        this.tasks.splice(index, 1, response.data);
                    }
                    this.showNotification('Task updated successfully!', 'success');
                }
                
                this.closeModal();
                
            } catch (error) {
                console.error('Save failed:', error);
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
            this.taskToDelete = { id: task.id, title: task.title };
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },

        deleteTaskFromModal() {
            if (!this.form || !this.form.id) return;
            this.taskToDelete = { id: this.form.id, title: this.form.title };
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
                console.error('Delete failed:', error);
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
    background: #f8fafc;
    min-height: 100vh;
}

/* ========== HEADER ========== */
.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left { flex: 1; }

.module-title {
    font-size: 2rem;
    color: #4361ee;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.task-summary {
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
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.summary-item.active {
    background: #4361ee;
    color: white;
}

.summary-item.active .summary-icon,
.summary-item.active .summary-label,
.summary-item.active .summary-value {
    color: white;
}

.summary-label { font-size: 0.9rem; color: #6c757d; }
.summary-value { font-weight: 600; font-size: 1.1rem; color: #212529; }

/* ========== QUICK ACTIONS ========== */
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

.search-box i { color: #6c757d; margin-right: 0.5rem; }
.search-box input { border: none; background: transparent; flex: 1; outline: none; }

.clear-search {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
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
    cursor: pointer;
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
}

/* ========== VIEW TOGGLE ========== */
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
}

.view-toggle.active {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

/* ========== TASK CONTAINER ========== */
.task-container { min-height: 400px; }
.task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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
    padding: 1rem;
}
.task-list .card-body { flex: 1; margin: 0 1rem; }
.task-list .task-description { display: none; }
.task-list .card-footer { width: auto; border-top: none; padding-top: 0; }

/* ========== TASK CARD ========== */
.task-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s;
    cursor: pointer;
    position: relative;
    border: 1px solid transparent;
}

.task-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: #e9ecef;
}

.task-card.priority-high { border-top: 4px solid #dc3545 !important; }
.task-card.priority-medium { border-top: 4px solid #ffc107 !important; }
.task-card.priority-low { border-top: 4px solid #28a745 !important; }

.task-card.status-done {
    opacity: 0.8;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    filter: grayscale(0.7);
}
.task-card.status-done .task-title {
    color: #6c757d;
    text-decoration: line-through;
    font-style: italic;
}
.task-card.is-overdue {
    background: linear-gradient(135deg, #f3e5f5, #e1bee7);
    border-left: 4px solid #9c27b0 !important;
}
.task-card.is-due-today {
    background: linear-gradient(135deg, #fff9c4, #fff59d);
    border-left: 4px solid #ffb300 !important;
}

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
.status-todo { background: #fff3cd; color: #856404; }
.status-inprogress { background: #f8d7da; color: #721c24; }
.status-done { background: #d4edda; color: #155724; }

.priority-badge {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    font-weight: 600;
}
.priority-high { color: #dc3545; }
.priority-medium { color: #ffc107; }
.priority-low { color: #28a745; }

.card-body { margin-bottom: 1rem; }
.task-title { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
.task-description { color: #6c757d; font-size: 0.9rem; line-height: 1.5; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; }

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
.overdue-badge { background: #dc3545; color: white; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.7rem; margin-left: 0.5rem; }
.due-today-badge { background: #ffb300; color: #212529; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.7rem; margin-left: 0.5rem; }

.task-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.2s;
}
.task-card:hover .task-actions { opacity: 1; }

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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.icon-btn:hover { transform: scale(1.1); }
.edit-btn:hover { background: #4361ee; color: white; }
.delete-btn:hover { background: #dc3545; color: white; }

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
    background: #4361ee;
    transition: width 0.3s;
}

/* ========== EMPTY STATE ========== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    grid-column: 1 / -1;
}
.empty-state i { font-size: 4rem; color: #dee2e6; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.5rem; color: #495057; margin-bottom: 0.5rem; }
.empty-state p { color: #6c757d; margin-bottom: 1.5rem; }

/* ========== LOADING ========== */
.loading-state { text-align: center; padding: 4rem; }
.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f1f3f5;
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ========== MODAL ========== */
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
    animation: fadeIn 0.2s ease;
    padding: 1rem;
    overflow-y: auto;
}

.modal-container {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
    position: relative;
    margin: auto;
}

/* Custom scrollbar for modal container */
.modal-container::-webkit-scrollbar {
    width: 8px;
}

.modal-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
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
    z-index: 10;
    border-radius: 20px 20px 0 0;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1e293b;
    margin: 0;
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
    color: #64748b;
}

.btn-close:hover {
    background: #f1f5f9;
    color: #dc3545;
    transform: rotate(90deg);
}

.modal-body {
    padding: 1.5rem;
    overflow-y: visible;
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
    z-index: 10;
    border-radius: 0 0 20px 20px;
}

/* Ensure footer buttons don't overlap content */
.modal-footer .btn {
    min-width: 100px;
}

/* Dark mode support */
.dark-mode .modal-container {
    background: #16213e;
}

.dark-mode .modal-header,
.dark-mode .modal-footer {
    background: #16213e;
    border-color: #0f3460;
}

.dark-mode .modal-title {
    color: #e4e6eb;
}

.dark-mode .btn-close {
    color: #94a3b8;
}

.dark-mode .btn-close:hover {
    background: #0f3460;
    color: #dc3545;
}

.dark-mode .modal-container::-webkit-scrollbar-track {
    background: #0f3460;
}

.dark-mode .modal-container::-webkit-scrollbar-thumb {
    background: #1a4a6f;
}

.dark-mode .modal-container::-webkit-scrollbar-thumb:hover {
    background: #2a5a8f;
}

/* ========== FORM STYLES (inside modal) ========== */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #1e293b;
}

.dark-mode .form-group label {
    color: #e4e6eb;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.dark-mode .form-control {
    background: #0f3460;
    border-color: #1a4a6f;
    color: #e4e6eb;
}

.dark-mode .form-control:focus {
    background: #1a4a6f;
    border-color: #4361ee;
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Form Row for two columns */
.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group.half {
    flex: 1;
}

/* Priority and Status Selectors */
.priority-selector,
.status-selector {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.priority-option,
.status-option {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    text-align: center;
    font-weight: 500;
    transition: all 0.2s;
}

.dark-mode .priority-option,
.dark-mode .status-option {
    background: #0f3460;
    border-color: #1a4a6f;
    color: #e4e6eb;
}

.priority-option:hover,
.status-option:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

.dark-mode .priority-option:hover,
.dark-mode .status-option:hover {
    background: #1a4a6f;
}

/* Priority option colors */
.priority-option.low.active {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.priority-option.medium.active {
    background: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

.priority-option.high.active {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.status-option.active {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

.dark-mode .priority-option.low.active {
    background: #28a745;
    color: white;
}

.dark-mode .priority-option.medium.active {
    background: #ffc107;
    color: #212529;
}

.dark-mode .priority-option.high.active {
    background: #dc3545;
    color: white;
}

.dark-mode .status-option.active {
    background: #4361ee;
    color: white;
}

/* Animations */
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

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        max-width: 95%;
        max-height: 85vh;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .priority-selector,
    .status-selector {
        flex-direction: column;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 1rem;
    }
}

/* ========== FORM ========== */
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
}
.form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.error-message { color: #dc3545; font-size: 0.85rem; margin-top: 0.25rem; }

.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}
.form-group.half { flex: 0 0 calc(50% - 0.5rem); }

.priority-selector, .status-selector {
    display: flex;
    gap: 0.5rem;
}
.priority-option, .status-option {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    text-align: center;
    font-weight: 500;
}
.priority-option.low.active { background: #28a745; color: white; border-color: #28a745; }
.priority-option.medium.active { background: #ffc107; color: #212529; border-color: #ffc107; }
.priority-option.high.active { background: #dc3545; color: white; border-color: #dc3545; }
.status-option.active { background: #4361ee; color: white; border-color: #4361ee; }

/* ========== BUTTONS ========== */
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
}
.btn-primary { background: #4361ee; color: white; }
.btn-primary:hover { background: #3451d1; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(67,97,238,0.3); }
.btn-secondary { background: #6c757d; color: white; }
.btn-danger { background: #dc3545; color: white; }

.delete-icon { margin-bottom: 1rem; }
.delete-message { font-size: 1.1rem; margin-bottom: 0.5rem; }
.text-danger { color: #dc3545; }
.text-center { text-align: center; }

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .task-manager { padding: 1rem; }
    .quick-actions { flex-direction: column; }
    .search-box { max-width: 100%; }
    .form-row { flex-direction: column; }
    .form-group.half { flex: 1; }
    .priority-selector, .status-selector { flex-direction: column; }
    .modal-footer { flex-direction: column; }
    .modal-footer .btn { width: 100%; justify-content: center; }
}
@media (max-width: 480px) {
    .task-grid { grid-template-columns: 1fr; }
    .module-title { font-size: 1.5rem; }
}
</style>