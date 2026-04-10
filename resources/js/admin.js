import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';
import router from './router';

// Set up axios with CSRF token
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

// Import user components
import Dashboard from './components/Dashboard/Dashboard.vue';
import NotesManager from './components/Notes/NotesManager.vue';
import ClassSchedule from './components/Schedule/ClassSchedule.vue';
import TaskManager from './components/Tasks/TaskManager.vue';
import ReminderManager from './components/Reminders/ReminderManager.vue';
import SettingsManager from './components/Settings/SettingsManager.vue';
import Login from './components/Auth/Login.vue';
import Register from './components/Auth/Register.vue';
import AdminLogin from './components/Auth/AdminLogin.vue';

// Import admin components
import AdminClasses from './components/Admin/AdminClasses.vue';
import AdminDashboard from './components/Admin/AdminDashboard.vue';
import AdminUsers from './components/Admin/AdminUsers.vue';
import AdminTasks from './components/Admin/AdminTasks.vue';
import AdminNotes from './components/Admin/AdminNotes.vue';
import AdminReminders from './components/Admin/AdminReminders.vue';
import AdminAnalytics from './components/Admin/AdminAnalytics.vue';
import AdminSearch from './components/Admin/AdminSearch.vue';

// Create Vue app (SINGLE app for both user and admin)
const app = createApp({});

// Register user components
app.component('dashboard', Dashboard);
app.component('notes-manager', NotesManager);
app.component('class-schedule', ClassSchedule);
app.component('task-manager', TaskManager);
app.component('reminder-manager', ReminderManager);
app.component('settings-manager', SettingsManager);
app.component('login', Login);
app.component('register', Register);
app.component('admin-login', AdminLogin);

// Register admin components
app.component('admin-classes', AdminClasses);
app.component('admin-dashboard', AdminDashboard);
app.component('admin-users', AdminUsers);
app.component('admin-tasks', AdminTasks);
app.component('admin-notes', AdminNotes);
app.component('admin-reminders', AdminReminders);
app.component('admin-analytics', AdminAnalytics);
app.component('admin-search', AdminSearch);

// Use router
app.use(router);

// Mount the app to #app (NOT #admin-app)
app.mount('#app');

console.log('Vue app mounted successfully');