<template>
    <div class="schedule-manager">
        <!-- Module Header -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-calendar-week"></i> Class Schedule
                </h2>
                <div class="schedule-summary">
                    <div class="summary-item" @click="setFilter('all')" :class="{ active: currentFilter === 'all' }">
                        <i class="fas fa-book summary-icon"></i>
                        <span class="summary-label">Total Classes</span>
                        <span class="summary-value">{{ classes.length }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('today')" :class="{ active: currentFilter === 'today' }">
                        <i class="fas fa-sun summary-icon today-icon"></i>
                        <span class="summary-label">Today</span>
                        <span class="summary-value">{{ getTodayClassesCount }}</span>
                    </div>
                    <div class="summary-item" @click="setFilter('upcoming')" :class="{ active: currentFilter === 'upcoming' }">
                        <i class="fas fa-clock summary-icon upcoming-icon"></i>
                        <span class="summary-label">Upcoming</span>
                        <span class="summary-value">{{ getUpcomingClassesCount }}</span>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Class
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
                    placeholder="Search classes..."
                >
                <button v-if="searchQuery" class="clear-search" @click="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sort-options">
                <select v-model="sortBy" class="sort-select">
                    <option value="day">Sort by Day</option>
                    <option value="time">Sort by Time</option>
                    <option value="name">Sort by Name</option>
                </select>
                <button class="sort-direction" @click="toggleSortDirection">
                    <i :class="sortDirection === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down'"></i>
                </button>
            </div>
        </div>

        <!-- Week Navigation -->
        <div class="week-navigation">
            <button class="nav-btn" @click="previousWeek">
                <i class="fas fa-chevron-left"></i> Previous Week
            </button>
            <div class="current-week">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ currentWeekRange }}</span>
            </div>
            <button class="nav-btn" @click="nextWeek">
                Next Week <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading schedule...</p>
        </div>

        <!-- Week Grid -->
        <div v-else class="week-grid">
            <div 
                v-for="day in weekDays" 
                :key="day.name"
                class="day-column"
                :class="{
                    'today': day.isToday,
                    'weekend': day.isWeekend
                }"
            >
                <div class="day-header">
                    <div class="day-name">{{ day.name }}</div>
                    <div class="day-date">{{ day.date }}</div>
                    <div class="day-count" v-if="getClassesForDay(day.name).length > 0">
                        {{ getClassesForDay(day.name).length }}
                    </div>
                </div>
                
                <div class="day-classes">
                    <div 
                        v-for="classItem in getClassesForDay(day.name)" 
                        :key="classItem.id"
                        class="class-card"
                        :style="{ borderLeftColor: getSolidColor(classItem.color) }"
                        @click="openEditModal(classItem)"
                    >
                        <div class="class-time">
                            <i class="fas fa-clock"></i>
                            <span>{{ classItem.time }}</span>
                        </div>
                        <div class="class-name">{{ classItem.name }}</div>
                        <div class="class-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ classItem.location }}</span>
                        </div>
                        <div class="class-actions" @click.stop>
                            <button class="icon-btn edit-btn" @click="openEditModal(classItem)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="icon-btn delete-btn" @click="deleteClass(classItem)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="getClassesForDay(day.name).length === 0" class="empty-day">
                        <i class="fas fa-calendar-day"></i>
                        <p>No classes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Class Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i :class="modalMode === 'add' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
                        {{ modalMode === 'add' ? 'Add New Class' : 'Edit Class' }}
                    </h5>
                    <button type="button" class="btn-close" @click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form @submit.prevent="saveClass">
                        <!-- Class Name -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-heading"></i>
                                Class Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="form.name"
                                @input="onClassNameInput"
                                @change="onClassNameChange"
                                list="class-name-suggestions"
                                placeholder="e.g., Mathematics"
                                required
                                :disabled="saving"
                            >
                            <datalist id="class-name-suggestions">
                                <option v-for="name in uniqueClassNames" :key="name" :value="name">
                                    {{ name }}
                                </option>
                            </datalist>
                            <div v-if="errors.name" class="error-message">{{ errors.name[0] }}</div>
                        </div>

                        <!-- Time -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock"></i>
                                Time <span class="text-danger">*</span>
                            </label>
                            <div class="time-input-group">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    v-model="form.time"
                                    placeholder="e.g., 9:00 AM - 10:30 AM"
                                    required
                                    :disabled="saving"
                                >
                                <button 
                                    type="button" 
                                    class="btn-time" 
                                    @click="setCurrentTime"
                                    :disabled="saving"
                                >
                                    <i class="fas fa-play"></i> Now
                                </button>
                            </div>
                            <div v-if="errors.time" class="error-message">{{ errors.time[0] }}</div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Location <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="form.location"
                                placeholder="e.g., Room 302"
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.location" class="error-message">{{ errors.location[0] }}</div>
                        </div>

                        <!-- Day -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-day"></i>
                                Day <span class="text-danger">*</span>
                            </label>
                            <div class="day-selector">
                                <button 
                                    v-for="day in daysOfWeek" 
                                    :key="day.value"
                                    type="button"
                                    class="day-option"
                                    :class="{ active: form.day === day.value }"
                                    @click="form.day = day.value"
                                >
                                    {{ day.label }}
                                </button>
                            </div>
                            <div v-if="errors.day" class="error-message">{{ errors.day[0] }}</div>
                        </div>

                        <!-- Color Picker - Vibrant Solid Colors -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-palette"></i>
                                Color
                            </label>
                            <div class="color-picker-container">
                                <input 
                                    type="color" 
                                    class="color-picker" 
                                    v-model="form.hexColor"
                                    @input="onHexColorChange"
                                    :disabled="saving"
                                >
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    v-model="form.color"
                                    placeholder="Color will auto-fill"
                                    required
                                    :disabled="saving"
                                >
                            </div>
                            
                            <!-- Vibrant Color Palette -->
                            <div class="color-palette">
                                <div 
                                    v-for="color in vibrantColors" 
                                    :key="color"
                                    class="color-swatch"
                                    :style="{ backgroundColor: color }"
                                    :class="{ active: form.hexColor === color }"
                                    @click="selectColor(color)"
                                    :title="color"
                                ></div>
                            </div>
                            
                            <small class="text-muted">
                                {{ modalMode === 'add' 
                                    ? 'Color will auto-fill when you select an existing class name' 
                                    : 'Changing the color will update all classes with this name' 
                                }}
                            </small>
                            <div v-if="errors.color" class="error-message">{{ errors.color[0] }}</div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button 
                        v-if="modalMode === 'edit'" 
                        type="button" 
                        class="btn btn-danger" 
                        @click="deleteClassFromModal"
                        :disabled="deleting"
                    >
                        <i class="fas fa-trash"></i>
                        {{ deleting ? 'Deleting...' : 'Delete Class' }}
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
                        @click="saveClass"
                        :disabled="saving || deleting"
                    >
                        <i :class="saving ? 'fas fa-spinner fa-spin' : (modalMode === 'add' ? 'fas fa-plus' : 'fas fa-save')"></i>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Add Class' : 'Update Class') }}
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
                        Delete Class
                    </h5>
                    <button type="button" class="btn-close" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="delete-icon">
                        <i class="fas fa-trash-alt fa-4x text-danger"></i>
                    </div>
                    <p class="delete-message">
                        Are you sure you want to delete "<strong>{{ classToDelete?.name }}</strong>"?
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
    name: 'ClassSchedule',
    
    props: {
        initialClasses: {
            type: Array,
            default: () => []
        },
        initialUniqueNames: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            classes: this.initialClasses,
            uniqueClassNames: this.initialUniqueNames,
            searchQuery: '',
            currentFilter: 'all',
            sortBy: 'day',
            sortDirection: 'asc',
            loading: false,
            saving: false,
            deleting: false,
            modalMode: 'add',
            showModal: false,
            showDeleteModal: false,
            classToDelete: null,
            currentWeekOffset: 0,
            
            form: {
                id: null,
                name: '',
                time: '',
                location: '',
                day: 'Monday',
                color: '#4361ee',
                hexColor: '#4361ee'
            },
            
            errors: {},
            
            daysOfWeek: [
                { value: 'Monday', label: 'Mon' },
                { value: 'Tuesday', label: 'Tue' },
                { value: 'Wednesday', label: 'Wed' },
                { value: 'Thursday', label: 'Thu' },
                { value: 'Friday', label: 'Fri' },
                { value: 'Saturday', label: 'Sat' }
            ],
            
            // Vibrant solid colors - all hex values
            vibrantColors: [
                '#4361ee', // Royal Blue
                '#3a0ca3', // Deep Purple
                '#7209b7', // Violet
                '#f72585', // Hot Pink
                '#e63946', // Red
                '#2a9d8f', // Teal
                '#e9c46a', // Gold
                '#f4a261', // Orange
                '#e76f51', // Coral
                '#4cc9f0', // Light Blue
                '#06d6a0', // Emerald
                '#118ab2', // Ocean Blue
                '#ef476f', // Pink
                '#ffd166', // Yellow
                '#8338ec', // Purple
                '#3b82f6', // Bright Blue
                '#10b981', // Green
                '#f59e0b', // Amber
                '#ef4444', // Bright Red
                '#8b5cf6'  // Violet
            ],
            
            searchTimeout: null
        };
    },

    computed: {
        classColorMap() {
            const map = {};
            this.classes.forEach(cls => {
                if (!map[cls.name]) {
                    // Store the solid color, not faded
                    map[cls.name] = this.getSolidColor(cls.color);
                }
            });
            return map;
        },
        
        getTodayClassesCount() {
            const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
            return this.classes.filter(c => c.day === today).length;
        },
        
        getUpcomingClassesCount() {
            const today = new Date();
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const todayIndex = days.indexOf(today.toLocaleDateString('en-US', { weekday: 'long' }));
            
            return this.classes.filter(c => {
                const classIndex = days.indexOf(c.day);
                return classIndex > todayIndex && classIndex <= todayIndex + 3;
            }).length;
        },
        
        weekDays() {
            const today = new Date();
            const currentDay = today.getDay();
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            let startOffset = currentDay === 0 ? -6 : 1 - currentDay;
            startOffset += this.currentWeekOffset * 7;
            
            const weekDays = [];
            for (let i = 0; i < 6; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() + startOffset + i);
                const dayName = days[date.getDay()];
                const isToday = date.toDateString() === today.toDateString();
                const isWeekend = dayName === 'Saturday';
                
                weekDays.push({
                    name: dayName,
                    date: date.getDate(),
                    month: date.toLocaleDateString('en-US', { month: 'short' }),
                    isToday,
                    isWeekend,
                    fullDate: date
                });
            }
            
            return weekDays;
        },
        
        currentWeekRange() {
            if (this.weekDays.length === 0) return '';
            const firstDay = this.weekDays[0];
            const lastDay = this.weekDays[this.weekDays.length - 1];
            return `${firstDay.month} ${firstDay.date} - ${lastDay.month} ${lastDay.date}, ${new Date().getFullYear()}`;
        },
        
        filteredAndSortedClasses() {
            let filtered = [...this.classes];
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(cls => 
                    cls.name.toLowerCase().includes(query) ||
                    cls.location.toLowerCase().includes(query) ||
                    cls.time.toLowerCase().includes(query)
                );
            }
            
            if (this.currentFilter === 'today') {
                const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
                filtered = filtered.filter(cls => cls.day === today);
            } else if (this.currentFilter === 'upcoming') {
                const today = new Date();
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const todayIndex = days.indexOf(today.toLocaleDateString('en-US', { weekday: 'long' }));
                filtered = filtered.filter(cls => {
                    const classIndex = days.indexOf(cls.day);
                    return classIndex > todayIndex && classIndex <= todayIndex + 3;
                });
            }
            
            const daysOrder = { Monday: 0, Tuesday: 1, Wednesday: 2, Thursday: 3, Friday: 4, Saturday: 5 };
            
            return filtered.sort((a, b) => {
                let comparison = 0;
                
                if (this.sortBy === 'day') {
                    comparison = daysOrder[a.day] - daysOrder[b.day];
                } else if (this.sortBy === 'time') {
                    comparison = a.time.localeCompare(b.time);
                } else if (this.sortBy === 'name') {
                    comparison = a.name.localeCompare(b.name);
                }
                
                return this.sortDirection === 'asc' ? comparison : -comparison;
            });
        }
    },

    mounted() {
        console.log('ClassSchedule mounted');
        this.debouncedSearch = _.debounce(this.applySearch, 300);
        this.setDefaultDay();
        
        // Convert any existing faded colors to solid on load
        this.classes.forEach(classItem => {
            if (classItem.color && classItem.color.includes('rgba')) {
                classItem.color = this.getSolidColor(classItem.color);
            }
        });
    },

    methods: {
        // Convert rgba to hex
        getSolidColor(color) {
            if (!color) return '#4361ee';
            
            // If it's already a hex color, return it
            if (color.startsWith('#')) {
                return color;
            }
            
            // If it's rgba, extract RGB and convert to hex
            if (color.startsWith('rgba')) {
                const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
                if (match) {
                    const r = parseInt(match[1]);
                    const g = parseInt(match[2]);
                    const b = parseInt(match[3]);
                    return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
                }
            }
            
            return '#4361ee';
        },
        
        getClassesForDay(day) {
            return this.filteredAndSortedClasses.filter(cls => cls.day === day);
        },
        
        setFilter(filter) {
            this.currentFilter = filter;
        },
        
        setDefaultDay() {
            const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
            if (this.daysOfWeek.map(d => d.value).includes(today)) {
                this.form.day = today;
            }
        },
        
        previousWeek() {
            this.currentWeekOffset--;
        },
        
        nextWeek() {
            this.currentWeekOffset++;
        },
        
        formatCurrentTime() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            const endHour = (now.getHours() + 1) % 24 || 12;
            const endAmpm = endHour >= 12 ? 'PM' : 'AM';
            
            return `${hours}:${minutes.toString().padStart(2, '0')} ${ampm} - ${endHour}:${minutes.toString().padStart(2, '0')} ${endAmpm}`;
        },
        
        setCurrentTime() {
            this.form.time = this.formatCurrentTime();
        },
        
        onClassNameInput() {
            const className = this.form.name.trim();
            
            if (className && this.classColorMap[className]) {
                const existingColor = this.classColorMap[className];
                this.form.color = existingColor;
                this.form.hexColor = existingColor;
            } else if (className) {
                let hash = 0;
                for (let i = 0; i < className.length; i++) {
                    hash = className.charCodeAt(i) + ((hash << 5) - hash);
                }
                const index = Math.abs(hash) % this.vibrantColors.length;
                this.form.hexColor = this.vibrantColors[index];
                this.form.color = this.vibrantColors[index];
            }
        },
        
        onClassNameChange() {
            const className = this.form.name.trim();
            
            if (className && this.classColorMap[className]) {
                const existingColor = this.classColorMap[className];
                this.form.color = existingColor;
                this.form.hexColor = existingColor;
            }
        },
        
        onHexColorChange() {
            this.form.color = this.form.hexColor;
        },
        
        selectColor(hexColor) {
            this.form.hexColor = hexColor;
            this.form.color = hexColor;
        },
        
        applySearch() {},
        
        clearSearch() {
            this.searchQuery = '';
            this.applySearch();
        },
        
        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },
        
        openAddModal() {
            this.modalMode = 'add';
            this.form = {
                id: null,
                name: '',
                time: '',
                location: '',
                day: this.form.day || 'Monday',
                color: '#4361ee',
                hexColor: '#4361ee'
            };
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        async openEditModal(classItem) {
            this.modalMode = 'edit';
            this.showModal = true;
            document.body.style.overflow = 'hidden';
            
            // Convert color to solid if needed
            const solidColor = this.getSolidColor(classItem.color);
            
            this.form = {
                id: classItem.id,
                name: classItem.name,
                time: classItem.time,
                location: classItem.location,
                day: classItem.day,
                color: solidColor,
                hexColor: solidColor
            };
            
            this.errors = {};
        },
        
        closeModal() {
            this.showModal = false;
            this.errors = {};
            document.body.style.overflow = '';
        },
        
        async saveClass() {
            this.saving = true;
            this.errors = {};
            
            try {
                if (!this.form.name.trim()) {
                    this.errors.name = ['Class name is required.'];
                    this.saving = false;
                    return;
                }
                
                if (!this.form.time.trim()) {
                    this.errors.time = ['Time is required.'];
                    this.saving = false;
                    return;
                }
                
                if (!this.form.location.trim()) {
                    this.errors.location = ['Location is required.'];
                    this.saving = false;
                    return;
                }
                
                if (!this.form.color.trim()) {
                    this.errors.color = ['Please select a color for the class.'];
                    this.saving = false;
                    return;
                }
                
                const formData = {
                    name: this.form.name,
                    time: this.form.time,
                    location: this.form.location,
                    day: this.form.day,
                    color: this.form.color
                };
                
                let response;
                if (this.modalMode === 'add') {
                    response = await axios.post('/schedule', formData);
                    // Ensure color is stored as hex
                    response.data.color = this.getSolidColor(response.data.color);
                    this.classes.push(response.data);
                    this.showNotification('Class added successfully!', 'success');
                } else {
                    response = await axios.put(`/schedule/${this.form.id}`, formData);
                    response.data.color = this.getSolidColor(response.data.color);
                    const index = this.classes.findIndex(c => c.id === this.form.id);
                    if (index !== -1) {
                        this.classes.splice(index, 1, response.data);
                    }
                    this.showNotification('Class updated successfully!', 'success');
                }
                
                this.updateUniqueNames();
                this.closeModal();
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.showNotification('Failed to save class. Please try again.', 'error');
                }
            } finally {
                this.saving = false;
            }
        },
        
        deleteClass(classItem) {
            this.classToDelete = {
                id: classItem.id,
                name: classItem.name
            };
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        deleteClassFromModal() {
            if (!this.form || !this.form.id) return;
            
            this.classToDelete = {
                id: this.form.id,
                name: this.form.name
            };
            
            this.closeModal();
            this.showDeleteModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.classToDelete = null;
            this.deleting = false;
            document.body.style.overflow = '';
        },
        
        async confirmDelete() {
            if (!this.classToDelete || !this.classToDelete.id) return;
            
            this.deleting = true;
            
            try {
                await axios.delete(`/schedule/${this.classToDelete.id}`);
                
                const index = this.classes.findIndex(c => c.id === this.classToDelete.id);
                if (index !== -1) {
                    this.classes.splice(index, 1);
                }
                
                this.updateUniqueNames();
                this.showNotification('Class deleted successfully!', 'success');
                this.closeDeleteModal();
                
            } catch (error) {
                this.showNotification('Failed to delete class. Please try again.', 'error');
                this.deleting = false;
            }
        },
        
        updateUniqueNames() {
            const names = new Set();
            this.classes.forEach(cls => names.add(cls.name));
            this.uniqueClassNames = Array.from(names).sort();
        },
        
        showNotification(message, type = 'info') {
            alert(message);
        }
    }
};
</script>

<style scoped>
.schedule-manager {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
    background: #f8fafc;
    min-height: 100vh;
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

/* Summary Cards */
.schedule-summary {
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
    background: var(--primary, #5d5d5e);
}

.summary-item.active .summary-icon,
.summary-item.active .summary-label,
.summary-item.active .summary-value {
    color: white;
}

.summary-icon {
    font-size: 1rem;
    color: #1380e0;
}

.summary-icon.today-icon {
    color: #e63946;
}

.summary-icon.upcoming-icon {
    color: #f72585;
}

.summary-item.active .summary-icon {
    color: white;
}

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

.search-box:focus-within {
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
    cursor: pointer;
    padding: 0.25rem;
    color: #6c757d;
}

.clear-search:hover {
    color: #dc3545;
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
    color: #4361ee;
}

/* Week Navigation */
.week-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background: white;
    padding: 0.75rem 1.5rem;
    border-radius: 40px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.nav-btn {
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    color: #4361ee;
}

.nav-btn:hover {
    background: #636364;
    color: white;
    border-color: #4361ee;
}

.current-week {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #1e293b;
}

/* Week Grid */
.week-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.day-column {
    background: white;
    border-right: 1px solid #f1f5f9;
    min-height: 500px;
}

.day-column:last-child {
    border-right: none;
}

.day-column.today {
    background: linear-gradient(135deg, #fff9e6, #fff5e6);
    position: relative;
}

.day-column.today::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #ffb300;
}

.day-column.today .day-name {
    color: #e63946;
    font-weight: 700;
}

.day-column.weekend {
    background: #faf9fe;
}

.day-column.weekend.today {
    background: linear-gradient(135deg, #fff9e6, #fff5e6);
}

.day-header {
    padding: 1rem;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.day-name {
    font-weight: 600;
    font-size: 1rem;
    color: #1e293b;
}

.day-date {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.2rem;
}

.day-count {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #4361ee;
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 20px;
    min-width: 20px;
    text-align: center;
}

.day-classes {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Class Card - Solid Color Border */
.class-card {
    background: white;
    border-radius: 12px;
    padding: 0.85rem;
    border-left: 4px solid;
    transition: all 0.2s;
    cursor: pointer;
    position: relative;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.class-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.class-time {
    font-size: 0.75rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-bottom: 0.3rem;
}

.class-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
}

.class-location {
    font-size: 0.7rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.class-actions {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.class-card:hover .class-actions {
    opacity: 1;
}

.icon-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.icon-btn:hover {
    transform: scale(1.1);
}

.edit-btn:hover {
    background: #4361ee;
    color: white;
}

.delete-btn:hover {
    background: #dc3545;
    color: white;
}

.empty-day {
    text-align: center;
    padding: 1rem;
    color: #94a3b8;
}

.empty-day i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #cbd5e1;
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
    border-top-color: #4361ee;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
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
    border-radius: 20px;
    width: 90%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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

.btn-close:hover {
    background: #f1f5f9;
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
    color: #1e293b;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Time Input */
.time-input-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-time {
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-time:hover {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

/* Day Selector */
.day-selector {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.day-option {
    flex: 1;
    padding: 0.6rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    text-align: center;
    font-weight: 500;
    transition: all 0.2s;
}

.day-option:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

.day-option.active {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

/* Color Picker */
.color-picker-container {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 1rem;
}

.color-picker {
    width: 50px;
    height: 50px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    padding: 0;
}

.color-palette {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 0.5rem;
}

.color-swatch {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.color-swatch:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.color-swatch.active {
    border-color: #1e293b;
    box-shadow: 0 0 0 2px white, 0 0 0 4px #1e293b;
}

.text-muted {
    color: #64748b;
    font-size: 0.8rem;
    display: block;
    margin-top: 0.25rem;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 40px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #3451d1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover:not(:disabled) {
    background: #e2e8f0;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Delete Modal */
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
@media (max-width: 1024px) {
    .week-grid {
        overflow-x: auto;
    }
    .day-column {
        min-width: 200px;
    }
}

@media (max-width: 768px) {
    .schedule-manager {
        padding: 1rem;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .week-navigation {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>