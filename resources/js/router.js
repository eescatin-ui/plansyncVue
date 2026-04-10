import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from './components/Layout/MainLayout.vue';
import AdminLayout from './components/Layout/AdminLayout.vue';
import Login from './components/Auth/Login.vue';
import Register from './components/Auth/Register.vue';
import AdminLogin from './components/Auth/AdminLogin.vue';
import NotFound from './components/NotFound.vue';

// Auth checks
const isAuthenticated = () => {
    return !!localStorage.getItem('auth_token') || !!window.user?.isAuthenticated;
};

const isAdminAuthenticated = () => {
    // Check both localStorage and window object
    const adminUser = localStorage.getItem('admin_user');
    return !!adminUser || !!window.admin?.isAuthenticated;
};

const routes = [
    // =============================================
    // PUBLIC AUTH ROUTES (No layout, full page)
    // =============================================
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { title: 'Login', requiresGuest: true, layout: 'auth' }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { title: 'Register', requiresGuest: true, layout: 'auth' }
    },
    {
        path: '/admin/login',
        name: 'admin.login',
        component: AdminLogin,
        meta: { title: 'Admin Login', requiresAdminGuest: true, layout: 'auth' }
    },
    
    // =============================================
    // USER AUTHENTICATED ROUTES (with MainLayout)
    // =============================================
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard.index',
                component: () => import('./components/Dashboard/Dashboard.vue'),
                meta: { title: 'Dashboard' }
            }
        ]
    },
    {
        path: '/notes',
        name: 'notes',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'notes.index',
                component: () => import('./components/Notes/NotesManager.vue'),
                meta: { title: 'Notes' }
            }
        ]
    },
    {
        path: '/schedule',
        name: 'schedule',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'schedule.index',
                component: () => import('./components/Schedule/ClassSchedule.vue'),
                meta: { title: 'Class Schedule' }
            }
        ]
    },
    {
        path: '/tasks',
        name: 'tasks',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'tasks.index',
                component: () => import('./components/Tasks/TaskManager.vue'),
                meta: { title: 'Homework & Tasks' }
            }
        ]
    },
    {
        path: '/reminders',
        name: 'reminders',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'reminders.index',
                component: () => import('./components/Reminders/ReminderManager.vue'),
                meta: { title: 'Reminders' }
            }
        ]
    },
    {
        path: '/settings',
        name: 'settings',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'settings.index',
                component: () => import('./components/Settings/SettingsManager.vue'),
                meta: { title: 'Settings' }
            }
        ]
    },
    
    // =============================================
    // ADMIN AUTHENTICATED ROUTES (with AdminLayout)
    // =============================================
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAdminAuth: true },
        children: [
            {
                path: '',
                redirect: '/admin/dashboard'
            },
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: () => import('./components/Admin/AdminDashboard.vue'),
                meta: { title: 'Admin Dashboard', requiresAdminAuth: true }
            },
            {
                path: 'users',
                name: 'admin.users',
                component: () => import('./components/Admin/AdminUsers.vue'),
                meta: { title: 'Admin - Users', requiresAdminAuth: true }
            },
            {
                path: 'users/:id',
                name: 'admin.users.show',
                component: () => import('./components/Admin/UserDetailView.vue'),
                meta: { title: 'User Details', requiresAdminAuth: true }
            },
            {
                path: 'classes',
                name: 'admin.classes',
                component: () => import('./components/Admin/AdminClasses.vue'),
                meta: { title: 'Admin - Classes', requiresAdminAuth: true }
            },
            {
                path: 'tasks',
                name: 'admin.tasks',
                component: () => import('./components/Admin/AdminTasks.vue'),
                meta: { title: 'Admin - Tasks', requiresAdminAuth: true }
            },
            {
                path: 'notes',
                name: 'admin.notes',
                component: () => import('./components/Admin/AdminNotes.vue'),
                meta: { title: 'Admin - Notes', requiresAdminAuth: true }
            },
            {
                path: 'reminders',
                name: 'admin.reminders',
                component: () => import('./components/Admin/AdminReminders.vue'),
                meta: { title: 'Admin - Reminders', requiresAdminAuth: true }
            },
            {
                path: 'analytics',
                name: 'admin.analytics',
                component: () => import('./components/Admin/AdminAnalytics.vue'),
                meta: { title: 'Admin - Analytics', requiresAdminAuth: true }
            },
            {
                path: 'search',
                name: 'admin.search',
                component: () => import('./components/Admin/AdminSearch.vue'),
                meta: { title: 'Admin - Search', requiresAdminAuth: true }
            }
        ]
    },
    
    // =============================================
    // 404 PAGE (MUST BE LAST)
    // =============================================
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta: { title: 'Page Not Found' }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }
        return { top: 0 };
    }
});

// Navigation Guard - This controls access to routes
router.beforeEach((to, from, next) => {
    const userAuthenticated = isAuthenticated();
    const adminAuthenticated = isAdminAuthenticated();
    
    // Set document title
    document.title = `PlanSync - ${to.meta.title || 'Academic Organizer'}`;
    
    console.log('Navigation Guard:', {
        path: to.path,
        name: to.name,
        requiresAuth: to.meta.requiresAuth,
        requiresAdminAuth: to.meta.requiresAdminAuth,
        requiresGuest: to.meta.requiresGuest,
        requiresAdminGuest: to.meta.requiresAdminGuest,
        userAuthenticated,
        adminAuthenticated
    });
    
    // =============================================
    // USER AUTHENTICATION RULES
    // =============================================
    
    // If route requires user authentication and user is not authenticated
    if (to.meta.requiresAuth && !userAuthenticated) {
        next({ name: 'login' });
        return;
    }
    
    // If route is for guests only (login/register) and user is authenticated
    if (to.meta.requiresGuest && userAuthenticated) {
        next({ name: 'dashboard' });
        return;
    }
    
    // =============================================
    // ADMIN AUTHENTICATION RULES
    // =============================================
    
    // If route requires admin authentication and admin is not authenticated
    if (to.meta.requiresAdminAuth && !adminAuthenticated) {
        next({ name: 'admin.login' });
        return;
    }
    
    // If route is for admin guests only (admin login) and admin is authenticated
    if (to.meta.requiresAdminGuest && adminAuthenticated) {
        next({ name: 'admin.dashboard' });
        return;
    }
    
    // Allow navigation
    next();
});

// Optional: Handle authentication errors globally
router.onError((error) => {
    console.error('Router error:', error);
});

export default router;