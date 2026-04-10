<template>
    <div class="settings-manager">
        <!-- ========== MODULE HEADER ========== -->
        <div class="module-header">
            <div class="header-left">
                <h2 class="module-title">
                    <i class="fas fa-cog"></i> Settings
                </h2>
                <p class="module-subtitle">Manage your account preferences and security</p>
            </div>
        </div>

        <!-- ========== SETTINGS GRID ========== -->
        <div class="settings-grid">
            <!-- Settings Menu -->
            <div class="settings-menu-card">
                <ul class="settings-menu">
                    <li>
                        <a href="#" class="settings-menu-link" :class="{ active: activeTab === 'profile' }" @click.prevent="setActiveTab('profile')">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a href="#" class="settings-menu-link" :class="{ active: activeTab === 'preferences' }" @click.prevent="setActiveTab('preferences')">
                            <i class="fas fa-palette"></i> Preferences
                        </a>
                    </li>
                    <li>
                        <a href="#" class="settings-menu-link" :class="{ active: activeTab === 'account' }" @click.prevent="setActiveTab('account')">
                            <i class="fas fa-shield-alt"></i> Account
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <!-- ========== PROFILE TAB ========== -->
                <div v-show="activeTab === 'profile'" class="setting-section">
                    <div class="section-header">
                        <h3><i class="fas fa-user-circle"></i> Profile Settings</h3>
                        <p>Update your personal information</p>
                    </div>

                    <!-- Success/Error Alerts -->
                    <div v-if="successMessage" class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ successMessage }}
                    </div>
                    <div v-if="errorMessage" class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
                    </div>

                    <form @submit.prevent="updateProfile">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="name" 
                                v-model="profileForm.name" 
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.name" class="error-message">{{ errors.name[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                v-model="profileForm.email" 
                                required
                                :disabled="saving"
                            >
                            <div v-if="errors.email" class="error-message">{{ errors.email[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-palette"></i> Avatar Color
                            </label>
                            <div class="color-picker">
                                <label 
                                    v-for="color in colors" 
                                    :key="color" 
                                    class="color-option" 
                                    :class="{ selected: profileForm.avatar_color === color }"
                                >
                                    <input type="radio" name="avatar_color" :value="color" v-model="profileForm.avatar_color">
                                    <span class="color-circle" :style="{ backgroundColor: color }"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                {{ saving ? 'Saving...' : 'Save Profile' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========== PREFERENCES TAB ========== -->
                <div v-show="activeTab === 'preferences'" class="setting-section">
                    <div class="section-header">
                        <h3><i class="fas fa-sliders-h"></i> Preferences</h3>
                        <p>Customize your PlanSync experience</p>
                    </div>

                    <form @submit.prevent="updatePreferences">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-moon"></i> Theme
                            </label>
                            <div class="theme-options">
                                <label class="theme-option" :class="{ selected: preferencesForm.theme === 'light' }">
                                    <input type="radio" name="theme" value="light" v-model="preferencesForm.theme">
                                    <div class="theme-preview light-theme">
                                        <div class="theme-header"></div>
                                        <div class="theme-content">
                                            <div class="theme-card"></div>
                                            <div class="theme-card"></div>
                                        </div>
                                    </div>
                                    <span class="theme-label"><i class="fas fa-sun"></i> Light</span>
                                </label>

                                <label class="theme-option" :class="{ selected: preferencesForm.theme === 'dark' }">
                                    <input type="radio" name="theme" value="dark" v-model="preferencesForm.theme">
                                    <div class="theme-preview dark-theme">
                                        <div class="theme-header"></div>
                                        <div class="theme-content">
                                            <div class="theme-card"></div>
                                            <div class="theme-card"></div>
                                        </div>
                                    </div>
                                    <span class="theme-label"><i class="fas fa-moon"></i> Dark</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="default_view">
                                <i class="fas fa-calendar-alt"></i> Default Calendar View
                            </label>
                            <select class="form-control" id="default_view" v-model="preferencesForm.default_view">
                                <option value="week">Week View</option>
                                <option value="month">Month View</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-bell"></i> Notifications
                            </label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="preferencesForm.email_notifications">
                                    <span><i class="fas fa-envelope"></i> Email notifications</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="preferencesForm.task_reminders">
                                    <span><i class="fas fa-tasks"></i> Task reminders</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="preferencesForm.class_notifications">
                                    <span><i class="fas fa-calendar-week"></i> Class notifications</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                                {{ saving ? 'Saving...' : 'Save Preferences' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========== ACCOUNT TAB ========== -->
                <div v-show="activeTab === 'account'" class="setting-section">
                    <div class="section-header">
                        <h3><i class="fas fa-shield-alt"></i> Account Security</h3>
                        <p>Manage your account security settings</p>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card password-card">
                        <div class="card-header">
                            <h4><i class="fas fa-lock"></i> Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="changePassword">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <div class="password-wrapper">
                                        <input 
                                            :type="showCurrentPassword ? 'text' : 'password'" 
                                            class="form-control" 
                                            id="current_password" 
                                            v-model="passwordForm.current_password" 
                                            required
                                            :disabled="changingPassword"
                                        >
                                        <span class="password-toggle" @click="showCurrentPassword = !showCurrentPassword">
                                            <i :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </span>
                                    </div>
                                    <div v-if="errors.current_password" class="error-message">{{ errors.current_password[0] }}</div>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <div class="password-wrapper">
                                        <input 
                                            :type="showNewPassword ? 'text' : 'password'" 
                                            class="form-control" 
                                            id="new_password" 
                                            v-model="passwordForm.new_password" 
                                            required
                                            :disabled="changingPassword"
                                        >
                                        <span class="password-toggle" @click="showNewPassword = !showNewPassword">
                                            <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </span>
                                    </div>
                                    <small class="form-text">Must be at least 8 characters long</small>
                                    <div v-if="errors.new_password" class="error-message">{{ errors.new_password[0] }}</div>
                                </div>

                                <div class="form-group">
                                    <label for="new_password_confirmation">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input 
                                            :type="showConfirmPassword ? 'text' : 'password'" 
                                            class="form-control" 
                                            id="new_password_confirmation" 
                                            v-model="passwordForm.new_password_confirmation" 
                                            required
                                            :disabled="changingPassword"
                                        >
                                        <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                                            <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </span>
                                    </div>
                                    <div v-if="errors.new_password_confirmation" class="error-message">{{ errors.new_password_confirmation[0] }}</div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary" :disabled="changingPassword">
                                        <i :class="changingPassword ? 'fas fa-spinner fa-spin' : 'fas fa-key'"></i>
                                        {{ changingPassword ? 'Changing...' : 'Change Password' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danger Zone Card -->
                    <div class="card danger-zone">
                        <div class="card-header">
                            <h4><i class="fas fa-exclamation-triangle"></i> Danger Zone</h4>
                        </div>
                        <div class="card-body">
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                            <button type="button" class="btn btn-danger" @click="confirmDeleteAccount" :disabled="deleting">
                                <i :class="deleting ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                                {{ deleting ? 'Deleting...' : 'Delete Account' }}
                            </button>
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
    name: 'SettingsManager',
    
    data() {
        return {
            // UI State
            activeTab: 'profile',
            saving: false,
            changingPassword: false,
            deleting: false,
            successMessage: '',
            errorMessage: '',
            
            // Form Data
            profileForm: {
                name: '',
                email: '',
                avatar_color: '#4361ee'
            },
            
            preferencesForm: {
                theme: 'light',
                default_view: 'week',
                email_notifications: false,
                task_reminders: true,
                class_notifications: true
            },
            
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            
            errors: {},
            
            // Color Options
            colors: ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#2d00f7', '#ff0054', '#00bbf9'],
            
            // Password visibility
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false
        };
    },

    mounted() {
        this.loadUserData();
        this.loadPreferences();
        this.applyTheme();
    },

    methods: {
        // ========== DATA LOADING ==========
        loadUserData() {
            // Get user data from window object (set in Blade)
            this.profileForm.name = window.userName || 'Student';
            this.profileForm.email = window.userEmail || '';
            this.profileForm.avatar_color = window.userAvatarColor || '#4361ee';
        },
        
        loadPreferences() {
            // Load saved preferences from localStorage
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                this.preferencesForm.theme = savedTheme;
            }
            
            const savedView = localStorage.getItem('default_view');
            if (savedView) {
                this.preferencesForm.default_view = savedView;
            }
            
            // Load notification preferences
            const emailNotif = localStorage.getItem('email_notifications');
            if (emailNotif !== null) this.preferencesForm.email_notifications = emailNotif === 'true';
            
            const taskReminders = localStorage.getItem('task_reminders');
            if (taskReminders !== null) this.preferencesForm.task_reminders = taskReminders === 'true';
            
            const classNotif = localStorage.getItem('class_notifications');
            if (classNotif !== null) this.preferencesForm.class_notifications = classNotif === 'true';
        },
        
        applyTheme() {
            if (this.preferencesForm.theme === 'dark') {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        },
        
        // ========== TAB MANAGEMENT ==========
        setActiveTab(tab) {
            this.activeTab = tab;
            this.errors = {};
            this.successMessage = '';
            this.errorMessage = '';
        },
        
        // ========== PROFILE METHODS ==========
        async updateProfile() {
            this.saving = true;
            this.errors = {};
            this.successMessage = '';
            this.errorMessage = '';
            
            try {
                const response = await axios.put('/settings/profile', this.profileForm);
                this.successMessage = response.data.message || 'Profile updated successfully!';
                
                // Update window user data
                window.userName = this.profileForm.name;
                window.userEmail = this.profileForm.email;
                window.userAvatarColor = this.profileForm.avatar_color;
                
                setTimeout(() => { this.successMessage = ''; }, 3000);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.errorMessage = 'Please fix the errors below.';
                } else {
                    this.errorMessage = error.response?.data?.message || 'Failed to update profile.';
                }
                setTimeout(() => { this.errorMessage = ''; }, 5000);
            } finally {
                this.saving = false;
            }
        },
        
        // ========== PREFERENCES METHODS ==========
        async updatePreferences() {
            this.saving = true;
            this.errors = {};
            this.successMessage = '';
            this.errorMessage = '';
            
            try {
                const response = await axios.put('/settings/preferences', this.preferencesForm);
                this.successMessage = response.data.message || 'Preferences saved successfully!';
                
                // Apply theme immediately
                this.applyTheme();
                
                // Save to localStorage
                localStorage.setItem('theme', this.preferencesForm.theme);
                localStorage.setItem('default_view', this.preferencesForm.default_view);
                localStorage.setItem('email_notifications', this.preferencesForm.email_notifications);
                localStorage.setItem('task_reminders', this.preferencesForm.task_reminders);
                localStorage.setItem('class_notifications', this.preferencesForm.class_notifications);
                
                setTimeout(() => { this.successMessage = ''; }, 3000);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.errorMessage = 'Please fix the errors below.';
                } else {
                    this.errorMessage = error.response?.data?.message || 'Failed to save preferences.';
                }
                setTimeout(() => { this.errorMessage = ''; }, 5000);
            } finally {
                this.saving = false;
            }
        },
        
        // ========== PASSWORD METHODS ==========
        async changePassword() {
            // Client-side validation
            if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
                this.errorMessage = 'Passwords do not match!';
                setTimeout(() => { this.errorMessage = ''; }, 3000);
                return;
            }
            
            if (this.passwordForm.new_password.length < 8) {
                this.errorMessage = 'Password must be at least 8 characters long!';
                setTimeout(() => { this.errorMessage = ''; }, 3000);
                return;
            }
            
            this.changingPassword = true;
            this.errors = {};
            this.successMessage = '';
            this.errorMessage = '';
            
            try {
                const response = await axios.put('/settings/password', this.passwordForm);
                this.successMessage = response.data.message || 'Password changed successfully!';
                
                // Clear password form
                this.passwordForm = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: ''
                };
                
                setTimeout(() => { this.successMessage = ''; }, 3000);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.errorMessage = 'Please fix the errors below.';
                } else {
                    this.errorMessage = error.response?.data?.message || 'Failed to change password.';
                }
                setTimeout(() => { this.errorMessage = ''; }, 5000);
            } finally {
                this.changingPassword = false;
            }
        },
        
        // ========== ACCOUNT DELETE METHODS ==========
        confirmDeleteAccount() {
            if (confirm('Are you absolutely sure? This action cannot be undone!\n\nAll your data will be permanently deleted.')) {
                this.deleteAccount();
            }
        },
        
        async deleteAccount() {
            this.deleting = true;
            
            try {
                await axios.delete('/settings/account');
                // Redirect to logout
                window.location.href = '/logout';
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Failed to delete account.';
                setTimeout(() => { this.errorMessage = ''; }, 5000);
                this.deleting = false;
            }
        }
    }
};
</script>

<style scoped>
/* ========== MAIN CONTAINER ========== */
.settings-manager {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
}

/* ========== HEADER ========== */
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
    color: #64748b;
    font-size: 0.95rem;
}

/* ========== SETTINGS GRID ========== */
.settings-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
}

/* ========== SETTINGS MENU ========== */
.settings-menu-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 1rem 0;
    height: fit-content;
    border: 1px solid #e9ecef;
}

.settings-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.settings-menu li {
    margin-bottom: 0.5rem;
}

.settings-menu-link {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem 1.5rem;
    color: #1e293b;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
    font-weight: 500;
}

.settings-menu-link:hover,
.settings-menu-link.active {
    background: #f8fafc;
    border-left-color: var(--primary, #4361ee);
    color: var(--primary, #4361ee);
}

.settings-menu-link i {
    width: 20px;
}

/* ========== SETTINGS CONTENT ========== */
.settings-content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
}

.setting-section {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ========== SECTION HEADER ========== */
.section-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.section-header h3 {
    font-size: 1.5rem;
    color: var(--primary, #4361ee);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.section-header p {
    color: #64748b;
    font-size: 0.9rem;
}

/* ========== ALERTS ========== */
.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* ========== FORM ========== */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #1e293b;
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
    border-color: var(--primary, #4361ee);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: block;
}

/* ========== COLOR PICKER ========== */
.color-picker {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.color-option {
    cursor: pointer;
    position: relative;
}

.color-option input {
    display: none;
}

.color-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: block;
    border: 3px solid transparent;
    transition: all 0.2s;
    cursor: pointer;
}

.color-option.selected .color-circle {
    border-color: #1e293b;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* ========== THEME OPTIONS ========== */
.theme-options {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.theme-option {
    cursor: pointer;
    text-align: center;
}

.theme-option input {
    display: none;
}

.theme-preview {
    width: 140px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid transparent;
    transition: border-color 0.3s;
    margin-bottom: 0.5rem;
}

.theme-option.selected .theme-preview {
    border-color: var(--primary, #4361ee);
}

.light-theme {
    background: #f8f9fa;
}

.light-theme .theme-header {
    height: 25px;
    background: white;
    border-bottom: 1px solid #e9ecef;
}

.light-theme .theme-card {
    height: 20px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin: 8px 12px;
}

.dark-theme {
    background: #1a1a2e;
}

.dark-theme .theme-header {
    height: 25px;
    background: #16213e;
    border-bottom: 1px solid #0f3460;
}

.dark-theme .theme-card {
    height: 20px;
    background: #16213e;
    border: 1px solid #0f3460;
    border-radius: 6px;
    margin: 8px 12px;
}

.theme-label {
    font-size: 0.9rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

/* ========== CHECKBOX GROUP ========== */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* ========== PASSWORD WRAPPER ========== */
.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    transition: color 0.2s;
}

.password-toggle:hover {
    color: var(--primary, #4361ee);
}

/* ========== CARDS ========== */
.card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.card-header h4 {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* ========== DANGER ZONE ========== */
.danger-zone {
    border: 1px solid #dc3545;
}

.danger-zone .card-header {
    background: #f8d7da;
    color: #721c24;
    border-bottom-color: #f5c6cb;
}

.danger-zone .card-body p {
    color: #6c757d;
    margin-bottom: 1rem;
}

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

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
    transform: translateY(-2px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .settings-manager {
        padding: 1rem;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .settings-menu-card {
        position: sticky;
        top: 70px;
        z-index: 10;
    }
    
    .settings-menu-link {
        padding: 0.6rem 1rem;
    }
    
    .settings-content {
        padding: 1.5rem;
    }
    
    .theme-options {
        flex-direction: column;
        align-items: center;
    }
    
    .theme-preview {
        width: 100%;
        max-width: 200px;
    }
    
    .color-picker {
        justify-content: center;
    }
    
    .form-actions {
        justify-content: center;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .section-header h3 {
        font-size: 1.25rem;
    }
    
    .card-header {
        padding: 0.75rem 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>