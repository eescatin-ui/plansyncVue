import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';
import router from './router';

// Import components
import Dashboard from './components/Dashboard/Dashboard.vue';
import NotesManager from './components/Notes/NotesManager.vue';
import ClassSchedule from './components/Schedule/ClassSchedule.vue';
import TaskManager from './components/Tasks/TaskManager.vue';
import ReminderManager from './components/Reminders/ReminderManager.vue';
import SettingsManager from './components/Settings/SettingsManager.vue';
import Login from './components/Auth/Login.vue';
import Register from './components/Auth/Register.vue';
import AdminLogin from './components/Auth/AdminLogin.vue';

// Configure axios
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

window.axios = axios;

// Create Vue app
const app = createApp({});

// Register components
app.component('dashboard', Dashboard);
app.component('notes-manager', NotesManager);
app.component('class-schedule', ClassSchedule);
app.component('task-manager', TaskManager);
app.component('reminder-manager', ReminderManager);
app.component('settings-manager', SettingsManager);
app.component('login', Login);
app.component('register', Register);
app.component('admin-login', AdminLogin);

// Use router
app.use(router);

// Mount the app
app.mount('#app');

console.log('Vue SPA mounted');