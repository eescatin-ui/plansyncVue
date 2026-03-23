<template>
    <div class="schedule">
        <!-- Module Header -->
        <div class="module-header">
            <h2 class="module-title">
                <i class="fas fa-calendar-week"></i> Class Schedule
            </h2>
            <button class="btn btn-primary" @click="openAddModal">
                <i class="fas fa-plus"></i> Add Class
            </button>
        </div>

        <!-- Calendar View Toggle -->
        <div class="calendar-view">
            <button 
                class="calendar-view-btn" 
                :class="{ active: view === 'week' }"
                @click="switchView('week')"
            >
                Week
            </button>
            <button 
                class="calendar-view-btn" 
                :class="{ active: view === 'month' }"
                @click="switchView('month')"
            >
                Month
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Week View -->
        <div v-else-if="view === 'week'" class="week-days">
            <div v-for="day in days" :key="day" class="day">
                <div class="day-header">{{ day }}</div>
                
                <div v-if="getClassesByDay(day).length > 0">
                    <div 
                        v-for="classItem in getClassesByDay(day)" 
                        :key="classItem.id"
                        class="class-item"
                        :style="{ backgroundColor: classItem.color }"
                        @click="openEditModal(classItem)"
                    >
                        <div class="class-time">{{ classItem.time }}</div>
                        <div class="class-name">{{ classItem.name }}</div>
                        <div class="class-location">{{ classItem.location }}</div>
                    </div>
                </div>
                
                <div v-else class="empty-state">
                    <p>No classes</p>
                </div>
            </div>
        </div>

        <!-- Month View (Placeholder) -->
        <div v-else class="month-view text-center py-5">
            <p>Month view coming soon!</p>
            <button class="btn btn-primary" @click="view = 'week'">Back to Week View</button>
        </div>

        <!-- Add/Edit Class Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">{{ modalMode === 'add' ? 'Add Class' : 'Edit Class' }}</h5>
                    <button type="button" class="btn-close" @click="closeModal" :disabled="saving">×</button>
                </div>
                
                <div class="modal-body">
                    <form @submit.prevent="saveClass">
                        <!-- Class Name -->
                        <div class="form-group">
                            <label for="class-name">Class Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="class-name"
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
                            <div v-if="errors.name" class="text-danger mt-1">{{ errors.name[0] }}</div>
                        </div>

                        <!-- Time -->
                        <div class="form-group">
                            <label>Time <span class="text-danger">*</span></label>
                            <div class="time-input-group">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    v-model="form.time"
                                    placeholder="e.g., 7:00 AM - 8:00 AM"
                                    required
                                    :disabled="saving"
                                >
                                <button 
                                    type="button" 
                                    class="btn btn-small btn-success" 
                                    @click="setCurrentTime"
                                    :disabled="saving"
                                >
                                    Now
                                </button>
                            </div>
                            <div v-if="errors.time" class="text-danger mt-1">{{ errors.time[0] }}</div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label for="class-location">Location <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="class-location"
                                v-model="form.location"
                                placeholder="e.g., Room 302"
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.location" class="text-danger mt-1">{{ errors.location[0] }}</div>
                        </div>

                        <!-- Day -->
                        <div class="form-group">
                            <label for="class-day">Day <span class="text-danger">*</span></label>
                            <select 
                                class="form-control" 
                                id="class-day"
                                v-model="form.day"
                                required
                                :disabled="saving"
                            >
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                            <div v-if="errors.day" class="text-danger mt-1">{{ errors.day[0] }}</div>
                        </div>

                        <!-- Color Picker -->
                        <div class="form-group">
                            <label for="class-color">Color</label>
                            <div class="color-picker-container">
                                <input 
                                    type="color" 
                                    class="form-control color-picker" 
                                    id="class-color-picker"
                                    v-model="form.hexColor"
                                    @input="onHexColorChange"
                                    :disabled="saving"
                                >
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="class-color"
                                    v-model="form.color"
                                    placeholder="Color will auto-fill"
                                    required
                                    :disabled="saving"
                                >
                            </div>
                            
                            <!-- Color Palette -->
                            <div class="color-palette mt-2">
                                <div 
                                    v-for="(color, index) in fadedColors" 
                                    :key="index"
                                    class="color-swatch"
                                    :style="{ backgroundColor: color }"
                                    :class="{ active: form.color === color }"
                                    @click="selectColor(color, hexColors[index])"
                                    :title="color"
                                ></div>
                            </div>
                            
                            <small class="text-muted">
                                {{ modalMode === 'add' 
                                    ? 'Color will auto-fill when you select an existing class name' 
                                    : 'Changing the color will update all classes with this name' 
                                }}
                            </small>
                            <div v-if="errors.color" class="text-danger mt-1">{{ errors.color[0] }}</div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button 
                        v-if="modalMode === 'edit'" 
                        type="button" 
                        class="btn btn-danger" 
                        @click="deleteClass"
                        :disabled="deleting"
                    >
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
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
                        <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                        {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Save Class' : 'Update Class') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-container modal-sm" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">Delete Class</h5>
                    <button type="button" class="btn-close" @click="closeDeleteModal">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<strong>{{ classToDelete?.name }}</strong>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-danger" 
                        @click="confirmDelete"
                        :disabled="deleting"
                    >
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

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
            view: 'week',
            loading: false,
            saving: false,
            deleting: false,
            modalMode: 'add', // 'add' or 'edit'
            showModal: false,
            showDeleteModal: false,
            classToDelete: null,
            
            // Form data
            form: {
                id: null,
                name: '',
                time: '',
                location: '',
                day: 'Monday',
                color: '',
                hexColor: '#4361ee'
            },
            
            errors: {},
            
            // Days of the week
            days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            
            // Color palette
            colorPalette: [
                '#4361ee', '#3a0ca3', '#7209b7', '#4cc9f0', '#f72585', '#e63946',
                '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653'
            ]
        };
    },

    computed: {
        // Convert hex colors to rgba with opacity
        fadedColors() {
            return this.colorPalette.map(hex => this.getFadedColor(hex, 0.3));
        },
        
        hexColors() {
            return this.colorPalette;
        },
        
        // Map of class names to their colors (from existing classes)
        classColorMap() {
            const map = {};
            this.classes.forEach(cls => {
                if (!map[cls.name]) {
                    map[cls.name] = cls.color;
                }
            });
            return map;
        }
    },

    mounted() {
        console.log('ClassSchedule mounted');
        // Set default day to today
        const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
        if (this.days.includes(today)) {
            this.form.day = today;
        }
    },

    methods: {
        // Get classes by day
        getClassesByDay(day) {
            return this.classes.filter(cls => cls.day === day);
        },

        // Switch between week/month view
        switchView(view) {
            this.view = view;
            if (view === 'month') {
                // You can implement month view later
                console.log('Month view selected');
            }
        },

        // Convert hex to rgba
        getFadedColor(hexColor, opacity = 0.3) {
            hexColor = hexColor.replace('#', '');
            const r = parseInt(hexColor.substr(0, 2), 16);
            const g = parseInt(hexColor.substr(2, 2), 16);
            const b = parseInt(hexColor.substr(4, 2), 16);
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        },

        // Format current time for the "Now" button
        formatCurrentTime() {
            const now = new Date();
            const minutes = now.getMinutes();
            const roundedMinutes = minutes < 15 ? 0 : (minutes < 45 ? 30 : 0);
            const roundedHour = now.getHours() + (roundedMinutes === 0 && minutes >= 45 ? 1 : 0);
            const startHour = roundedHour % 24;
            const startMin = roundedMinutes;
            const endHour = startHour + 1;
            const endMin = startMin;
            
            const formatTime = (hour, min) => {
                const period = hour >= 12 ? 'PM' : 'AM';
                const displayHour = hour % 12 || 12;
                return `${displayHour}:${min.toString().padStart(2, '0')} ${period}`;
            };
            
            return `${formatTime(startHour, startMin)} - ${formatTime(endHour % 24, endMin)}`;
        },

        // Set current time in form
        setCurrentTime() {
            this.form.time = this.formatCurrentTime();
        },

        // Handle class name input (auto-fill color)
        onClassNameInput() {
            const className = this.form.name.trim();
            
            if (className && this.classColorMap[className]) {
                // Existing class - use its color
                const existingColor = this.classColorMap[className];
                this.form.color = existingColor;
                
                // Extract hex from rgba for color picker
                const rgba = existingColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/);
                if (rgba) {
                    const r = parseInt(rgba[1]);
                    const g = parseInt(rgba[2]);
                    const b = parseInt(rgba[3]);
                    this.form.hexColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
                }
            } else if (className) {
                // New class - generate color based on name
                let hash = 0;
                for (let i = 0; i < className.length; i++) {
                    hash = className.charCodeAt(i) + ((hash << 5) - hash);
                }
                const index = Math.abs(hash) % this.colorPalette.length;
                this.form.hexColor = this.colorPalette[index];
                this.form.color = this.getFadedColor(this.colorPalette[index], 0.3);
            }
        },

        // Handle class name change (when selected from dropdown)
        onClassNameChange() {
            const className = this.form.name.trim();
            
            if (className && this.classColorMap[className]) {
                const existingColor = this.classColorMap[className];
                this.form.color = existingColor;
                
                const rgba = existingColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/);
                if (rgba) {
                    const r = parseInt(rgba[1]);
                    const g = parseInt(rgba[2]);
                    const b = parseInt(rgba[3]);
                    this.form.hexColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
                }
            }
        },

        // Handle hex color picker change
        onHexColorChange() {
            this.form.color = this.getFadedColor(this.form.hexColor, 0.3);
        },

        // Select color from palette
        selectColor(rgbaColor, hexColor) {
            this.form.color = rgbaColor;
            this.form.hexColor = hexColor;
        },

        // Open modal for adding a new class
        openAddModal() {
            this.modalMode = 'add';
            this.form = {
                id: null,
                name: '',
                time: '',
                location: '',
                day: this.form.day || 'Monday',
                color: '',
                hexColor: '#4361ee'
            };
            this.errors = {};
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        // Delete class (show confirmation)
deleteClass() {
    console.log('Delete button clicked for class:', this.form);
    
    // Make sure we have the class data
    if (!this.form || !this.form.id) {
        console.error('No class data to delete');
        this.showNotification('Cannot delete: Class data is missing.', 'error');
        return;
    }
    
    // Store the class to delete
    this.classToDelete = { 
        id: this.form.id, 
        name: this.form.name 
    };
    
    console.log('Setting classToDelete:', this.classToDelete);
    
    // Close the edit modal first
    this.closeModal();
    
    // Then open delete confirmation modal
    this.showDeleteModal = true;
    document.body.style.overflow = 'hidden'; // Prevent scrolling
},

        // Open modal for editing a class
        async openEditModal(classItem) {
    this.modalMode = 'edit';
    this.showModal = true;
    document.body.style.overflow = 'hidden';
    
    // Set loading state
    this.form = {
        id: classItem.id,
        name: 'Loading...',
        time: 'Loading...',
        location: 'Loading...',
        day: 'Monday',
        color: '',
        hexColor: '#4361ee'
    };
    
    try {
        console.log('Fetching class data for ID:', classItem.id);
        
        // Use the show endpoint instead of edit
        const response = await axios.get(`/schedule/${classItem.id}`);
        console.log('Class data received:', response.data);
        
        const classData = response.data;
        
        this.form = {
            id: classData.id,
            name: classData.name,
            time: classData.time,
            location: classData.location,
            day: classData.day,
            color: classData.color,
            hexColor: '#4361ee'
        };
        
        // Extract hex from rgba
        if (classData.color) {
            const rgba = classData.color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/);
            if (rgba) {
                const r = parseInt(rgba[1]);
                const g = parseInt(rgba[2]);
                const b = parseInt(rgba[3]);
                this.form.hexColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
            } else if (classData.color.startsWith('#')) {
                this.form.hexColor = classData.color;
            }
        }
        
        this.errors = {};
    } catch (error) {
        console.error('Error loading class data:', error);
        
        let errorMessage = 'Failed to load class data.';
        if (error.response) {
            console.error('Error response:', error.response.data);
            console.error('Error status:', error.response.status);
            
            if (error.response.status === 403) {
                errorMessage = 'You are not authorized to view this class.';
            } else if (error.response.status === 404) {
                errorMessage = 'Class not found.';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        }
        
        this.showNotification(errorMessage, 'error');
        this.closeModal();
    }
},

        closeModal() {
    this.showModal = false;
    this.errors = {};
    this.form = {
        id: null,
        name: '',
        time: '',
        location: '',
        day: 'Monday',
        color: '',
        hexColor: '#4361ee'
    };
    document.body.style.overflow = ''; // Restore scrolling
},

// Close delete modal
closeDeleteModal() {
    this.showDeleteModal = false;
    this.classToDelete = null;
    this.deleting = false;
    document.body.style.overflow = ''; // Restore scrolling
},

        // Confirm delete
        async confirmDelete() {
            if (!this.classToDelete) return;
            
            this.deleting = true;
            
            try {
                await axios.delete(`/schedule/${this.classToDelete.id}`);
                
                // Remove from list
                const index = this.classes.findIndex(c => c.id === this.classToDelete.id);
                if (index !== -1) {
                    this.classes.splice(index, 1);
                }
                
                // Update unique class names
                this.updateUniqueNames();
                
                this.showNotification('Class deleted successfully!', 'success');
                this.closeDeleteModal();
                
            } catch (error) {
                console.error('Delete failed:', error);
                this.showNotification('Failed to delete class. Please try again.', 'error');
                this.deleting = false;
            }
        },

        // Save class (create or update)
async saveClass() {
    this.saving = true;
    this.errors = {};
    
    try {
        // Validate required fields
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
        
        console.log('Saving class:', this.modalMode, formData);
        console.log('Class ID:', this.form.id);
        
        let response;
        if (this.modalMode === 'add') {
            // Create new class
            console.log('Sending POST request to /schedule');
            response = await axios.post('/schedule', formData);
            console.log('POST response:', response.data);
            
            this.classes.push(response.data);
            this.showNotification('Class added successfully!', 'success');
        } else {
            // Update existing class
            const classId = this.form.id;
            if (!classId) {
                throw new Error('Class ID is missing for update');
            }
            
            console.log(`Sending PUT request to /schedule/${classId}`);
            response = await axios.put(`/schedule/${classId}`, formData);
            console.log('PUT response:', response.data);
            
            // Update in list
            const index = this.classes.findIndex(c => c.id === classId);
            if (index !== -1) {
                this.classes.splice(index, 1, response.data);
                console.log('Class updated in list at index:', index);
            } else {
                console.warn('Class not found in local array, refreshing...');
                // If class not found in array, refresh the whole list
                await this.fetchClasses();
            }
            
            this.showNotification('Class updated successfully!', 'success');
        }
        
        // Update unique class names
        this.updateUniqueNames();
        this.closeModal();
        
    } catch (error) {
        console.error('Save failed:', error);
        
        if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            console.error('Error response data:', error.response.data);
            console.error('Error response status:', error.response.status);
            console.error('Error response headers:', error.response.headers);
            
            if (error.response.status === 422) {
                // Validation errors
                this.errors = error.response.data.errors;
                
                // Show validation errors in a more user-friendly way
                const errorMessages = Object.values(this.errors).flat().join('\n');
                this.showNotification(`Validation errors:\n${errorMessages}`, 'error');
            } else if (error.response.status === 403) {
                this.showNotification('You are not authorized to modify this class.', 'error');
            } else if (error.response.status === 404) {
                this.showNotification('Class not found. It may have been deleted.', 'error');
                // Refresh the list to remove deleted class
                await this.fetchClasses();
                this.closeModal();
            } else if (error.response.status === 500) {
                this.showNotification('Server error. Please try again later.', 'error');
            } else {
                const message = error.response.data.message || 'Failed to save class.';
                this.showNotification(`Error: ${message}`, 'error');
            }
        } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
            this.showNotification('Network error. Please check your connection.', 'error');
        } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Error message:', error.message);
            this.showNotification('Failed to save class. Please try again.', 'error');
        }
    } finally {
        this.saving = false;
    }
},

        // Update unique class names from current classes
        updateUniqueNames() {
            const names = new Set();
            this.classes.forEach(cls => names.add(cls.name));
            this.uniqueClassNames = Array.from(names).sort();
        },

        // Show notification (temporary)
        showNotification(message, type = 'info') {
            alert(message);
        }
    }
};
</script>

<style scoped>
.calendar-view {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.calendar-view-btn {
    padding: 0.5rem 1rem;
    background-color: var(--light-gray, #e9ecef);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: var(--dark, #212529);
    transition: all 0.2s;
}

.calendar-view-btn:hover {
    background-color: var(--primary, #4361ee);
    color: white;
}

.calendar-view-btn.active {
    background-color: var(--primary, #4361ee);
    color: white;
}

.week-days {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 1.5rem;
}

.day {
    background-color: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    min-height: 200px;
}

.day-header {
    font-weight: 600;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--light-gray, #e9ecef);
    text-align: center;
}

.class-item {
    padding: 0.8rem;
    margin-bottom: 0.8rem;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s;
    color: var(--dark, #212529);
    position: relative;
}

.class-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.class-time {
    font-weight: 600;
    color: var(--primary, #4361ee);
    font-size: 0.9rem;
}

.class-name {
    margin: 0.2rem 0;
    font-weight: 600;
}

.class-location {
    font-size: 0.9rem;
    color: var(--gray, #6c757d);
}

.empty-state {
    text-align: center;
    padding: 1rem;
    color: var(--gray, #6c757d);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modal-container {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-container.modal-sm {
    max-width: 400px;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close:hover {
    color: #dc3545;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
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
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary, #4361ee);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

select.form-control {
    cursor: pointer;
}

.time-input-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.time-input-group .form-control {
    flex: 1;
}

/* Color Picker Styles */
.color-picker-container {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-picker {
    width: 50px !important;
    height: 50px;
    padding: 0 !important;
    border: none !important;
    cursor: pointer;
}

.color-palette {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.color-swatch {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.color-swatch:hover {
    transform: scale(1.1);
}

.color-swatch.active {
    border-color: #333;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
}

/* Button Styles */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background-color: var(--primary, #4361ee);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: var(--secondary, #3a0ca3);
}

.btn-secondary {
    background-color: var(--gray, #6c757d);
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #5a6268;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background-color: #c82333;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background-color: #218838;
}

.btn-small {
    padding: 0.25rem 0.75rem;
    font-size: 0.85rem;
}

/* Text Styles */
.text-danger {
    color: #dc3545;
    font-size: 0.875rem;
}

.text-muted {
    color: var(--gray, #6c757d);
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.mt-1 {
    margin-top: 0.25rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.py-5 {
    padding-top: 3rem;
    padding-bottom: 3rem;
}

.text-center {
    text-align: center;
}

/* Spinner */
.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    vertical-align: text-bottom;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1100px) {
    .week-days {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .week-days {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .week-days {
        grid-template-columns: 1fr;
    }
    
    .color-picker-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .color-picker {
        width: 100% !important;
        height: 40px !important;
    }
    
    .time-input-group {
        flex-direction: column;
    }
    
    .time-input-group .btn {
        width: 100%;
    }
}
</style>