import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';

// Set up axios with CSRF token
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import components
import Login from './components/Auth/Login.vue';
import Register from './components/Auth/Register.vue';
import Dashboard from './components/Dashboard/Dashboard.vue';
import NotesManager from './components/Notes/NotesManager.vue';
import ClassSchedule from './components/Schedule/ClassSchedule.vue';
import TaskManager from './components/Tasks/TaskManager.vue';
import ReminderManager from './components/Reminders/ReminderManager.vue';
import SettingsManager from './components/Settings/SettingsManager.vue';

// Create Vue app
const app = createApp({});

// Register components
app.component('login', Login);
app.component('register', Register);
app.component('dashboard', Dashboard);
app.component('notes-manager', NotesManager);
app.component('class-schedule', ClassSchedule);
app.component('task-manager', TaskManager);
app.component('reminder-manager', ReminderManager);
app.component('settings-manager', SettingsManager);

// Mount the app
const appElement = document.getElementById('app');
if (appElement) {
    app.mount('#app');
    console.log('Vue mounted successfully');
} else {
    console.error('App element not found');
}