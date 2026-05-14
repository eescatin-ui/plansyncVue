<template>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="app-title">
                <i class="fas fa-calendar-alt"></i>
                <span>PlanSync</span>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <router-link to="/dashboard" class="nav-link" :class="{ active: $route.path === '/dashboard' }">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/schedule" class="nav-link" :class="{ active: $route.path === '/schedule' }">
                        <i class="fas fa-calendar-week"></i>
                        <span>Class Schedule</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/tasks" class="nav-link" :class="{ active: $route.path === '/tasks' }">
                        <i class="fas fa-tasks"></i>
                        <span>Homework & Tasks</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/notes" class="nav-link" :class="{ active: $route.path === '/notes' }">
                        <i class="fas fa-sticky-note"></i>
                        <span>Notes</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/reminders" class="nav-link" :class="{ active: $route.path === '/reminders' }">
                        <i class="fas fa-bell"></i>
                        <span>Reminders</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/settings" class="nav-link" :class="{ active: $route.path === '/settings' }">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </router-link>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <button @click="logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-profile">
                    <div class="user-avatar" :style="{ backgroundColor: userAvatarColor }">
                        {{ userInitial }}
                    </div>
                    <span class="user-name">{{ userName }}</span>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="content">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'MainLayout',
    
    data() {
        return {
            userName: '',
            userAvatarColor: '#4361ee',
            userInitial: ''
        };
    },
    
    mounted() {
        this.loadUserData();
    },
    
    methods: {
        loadUserData() {
            // Get user data from API
            axios.get('/api/user')
                .then(response => {
                    this.userName = response.data.name;
                    this.userInitial = this.userName.charAt(0).toUpperCase();
                    this.userAvatarColor = response.data.avatar_color || '#4361ee';
                })
                .catch(error => {
                    console.error('Failed to load user data:', error);
                });
        },
        
logout() {
    // Use a form POST instead of axios to let Laravel handle the redirect
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    document.body.appendChild(form);
    form.submit();
}
    }
};
</script>

<style scoped>
.app-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 260px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: white;
    padding: 1.5rem 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.app-title {
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 1.5rem;
    margin-bottom: 2rem;
    color: white;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1.5rem;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.nav-link:hover,
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: white;
}

.sidebar-footer {
    position: absolute;
    bottom: 1rem;
    left: 0;
    right: 0;
    padding: 0 1.5rem;
}

.logout-btn {
    width: 100%;
    padding: 0.8rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Main Content Styles */
.main-content {
    flex: 1;
    margin-left: 260px;
    width: calc(100% - 260px);
    background: #f8fafc;
    min-height: 100vh;
}

.header {
    height: 70px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 2rem;
    position: sticky;
    top: 0;
    z-index: 100;
}

.search-box {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 30px;
    padding: 0.5rem 1rem;
    width: 300px;
}

.search-box i {
    color: #6c757d;
    margin-right: 0.5rem;
}

.search-box input {
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    cursor: pointer;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.user-name {
    font-weight: 500;
    color: #1e293b;
}

.content {
    padding: 2rem;
    min-height: calc(100vh - 70px);
}

/* Route Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    
    .sidebar .app-title span,
    .sidebar-menu span,
    .sidebar-footer span {
        display: none;
    }
    
    .main-content {
        margin-left: 70px;
        width: calc(100% - 70px);
    }
    
    .nav-link {
        justify-content: center;
        padding: 0.8rem;
    }
}
</style>