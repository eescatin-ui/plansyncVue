@extends('layouts.main')

@section('title', 'Settings')

@if(session('success'))
    <div class="alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-error">
        <i class="fas fa-exclamation-circle"></i> Please check the form for errors.
    </div>
@endif

@section('content')
<div class="settings">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-cog"></i> Settings</h2>
    </div>
    
    <div class="settings-grid">
        <div class="settings-menu-card">
            <ul class="settings-menu">
                <li>
                    <a href="#profile" class="settings-menu-link active" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="#preferences" class="settings-menu-link" data-tab="preferences">
                        <i class="fas fa-palette"></i> Preferences
                    </a>
                </li>
                <li>
                    <a href="#account" class="settings-menu-link" data-tab="account">
                        <i class="fas fa-shield-alt"></i> Account
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="settings-content">
            <!-- Profile Tab -->
            <div class="setting-section active" id="profile-section">
                <div class="section-header">
                    <h3><i class="fas fa-user"></i> Profile Settings</h3>
                    <p>Update your personal information</p>
                </div>
                
                <form method="POST" action="{{ route('settings.updateProfile') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Avatar Color</label>
                        <div class="color-picker">
                            @php
                                $colors = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#2d00f7', '#ff0054', '#00bbf9'];
                            @endphp
                            @foreach($colors as $color)
                                <label class="color-option {{ $user->avatar_color == $color ? 'selected' : '' }}">
                                    <input type="radio" name="avatar_color" value="{{ $color }}"
                                           {{ $user->avatar_color == $color ? 'checked' : '' }}>
                                    <span class="color-circle" style="background-color: {{ $color }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Save Profile
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Preferences Tab -->
            <div class="setting-section" id="preferences-section">
                <div class="section-header">
                    <h3><i class="fas fa-palette"></i> Preferences</h3>
                    <p>Customize your PlanSync experience</p>
                </div>
                
                <form method="POST" action="{{ route('settings.updatePreferences') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Theme</label>
                        <div class="theme-options">
                            <label class="theme-option {{ ($preferences['theme'] ?? 'light') == 'light' ? 'selected' : '' }}">
                                <input type="radio" name="theme" value="light"
                                       {{ ($preferences['theme'] ?? 'light') == 'light' ? 'checked' : '' }}>
                                <div class="theme-preview light-theme">
                                    <div class="theme-header"></div>
                                    <div class="theme-content">
                                        <div class="theme-card"></div>
                                        <div class="theme-card"></div>
                                    </div>
                                </div>
                                <span class="theme-label">Light</span>
                            </label>
                            
                            <label class="theme-option {{ ($preferences['theme'] ?? 'light') == 'dark' ? 'selected' : '' }}">
                                <input type="radio" name="theme" value="dark"
                                       {{ ($preferences['theme'] ?? 'light') == 'dark' ? 'checked' : '' }}>
                                <div class="theme-preview dark-theme">
                                    <div class="theme-header"></div>
                                    <div class="theme-content">
                                        <div class="theme-card"></div>
                                        <div class="theme-card"></div>
                                    </div>
                                </div>
                                <span class="theme-label">Dark</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="default_view">Default Calendar View</label>
                        <select class="form-control" id="default_view" name="default_view">
                            <option value="week" {{ ($preferences['default_view'] ?? 'week') == 'week' ? 'selected' : '' }}>Week View</option>
                            <option value="month" {{ ($preferences['default_view'] ?? 'week') == 'month' ? 'selected' : '' }}>Month View</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notifications">Notifications</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_notifications" 
                                       {{ ($preferences['email_notifications'] ?? false) ? 'checked' : '' }}>
                                <span>Email notifications</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="task_reminders" 
                                       {{ ($preferences['task_reminders'] ?? true) ? 'checked' : '' }}>
                                <span>Task reminders</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="class_notifications" 
                                       {{ ($preferences['class_notifications'] ?? true) ? 'checked' : '' }}>
                                <span>Class notifications</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Account Tab -->
            <div class="setting-section" id="account-section">
                <div class="section-header">
                    <h3><i class="fas fa-shield-alt"></i> Account Security</h3>
                    <p>Manage your account security settings</p>
                </div>

                    <!-- Add Password Change Form Here -->
    <div class="card mb-4">
        <div class="card-header">
            <h4><i class="fas fa-lock"></i> Change Password</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.changePassword') }}" id="changePasswordForm">
                @csrf
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small class="form-text text-muted">Must be at least 8 characters long</small>
                    @error('new_password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    @error('new_password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card danger-zone">
        <div class="card-header">
            <h4><i class="fas fa-exclamation-triangle"></i> Danger Zone</h4>
        </div>
        <div class="card-body">
            <p>Once you delete your account, there is no going back. Please be certain.</p>
            <button type="button" class="btn btn-danger" 
                    onclick="if(confirm('Are you absolutely sure? This cannot be undone!')) {
                        document.getElementById('deleteAccountForm').submit();
                    }">
                <i class="fas fa-trash"></i> Delete Account
            </button>
            
            <form id="deleteAccountForm" action="#" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<style>

    .mb-4 {
    margin-bottom: 1.5rem !important;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Success message styling */
.alert-success {
    background-color: #d1e7dd;
    border-color: #badbcc;
    color: #0f5132;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-error, .alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
    .settings-grid {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }
    .settings-menu-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        padding: 1rem 0;
    }
    .settings-menu {
        list-style: none;
    }
    .settings-menu li {
        margin-bottom: 0.5rem;
    }
    .settings-menu-link {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 1.5rem;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
    }
    .settings-menu-link:hover,
    .settings-menu-link.active {
        background-color: var(--light-gray);
        border-left-color: var(--primary);
        color: var(--primary);
    }
    .settings-content {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .setting-section {
        display: none;
    }
    .setting-section.active {
        display: block;
        animation: fadeIn 0.3s;
    }
    .section-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--light-gray);
    }
    .section-header h3 {
        font-size: 1.5rem;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .section-header p {
        color: var(--gray);
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
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
        transition: border-color 0.3s;
    }
    .color-option.selected .color-circle {
        border-color: var(--dark);
        transform: scale(1.1);
    }
    .theme-options {
        display: flex;
        gap: 1.5rem;
    }
    .theme-option {
        cursor: pointer;
        text-align: center;
    }
    .theme-option input {
        display: none;
    }
    .theme-preview {
        width: 120px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid transparent;
        transition: border-color 0.3s;
        margin-bottom: 0.5rem;
    }
    .theme-option.selected .theme-preview {
        border-color: var(--primary);
    }
    .light-theme {
        background-color: #f8f9fa;
    }
    .light-theme .theme-header {
        height: 20px;
        background-color: white;
        border-bottom: 1px solid #e9ecef;
    }
    .light-theme .theme-card {
        height: 15px;
        background-color: white;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        margin: 5px 10px;
    }
    .dark-theme {
        background-color: #2d3748;
    }
    .dark-theme .theme-header {
        height: 20px;
        background-color: #4a5568;
        border-bottom: 1px solid #718096;
    }
    .dark-theme .theme-card {
        height: 15px;
        background-color: #4a5568;
        border: 1px solid #718096;
        border-radius: 4px;
        margin: 5px 10px;
    }
    .theme-label {
        font-size: 0.9rem;
        color: var(--dark);
    }
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
    }
    .danger-zone {
        border-color: var(--danger);
    }
    .danger-zone .card-header {
        background-color: #f8d7da;
        color: #721c24;
        border-bottom: 1px solid #f5c6cb;
    }
    .danger-zone .card-body p {
        color: var(--gray);
        margin-bottom: 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabLinks = document.querySelectorAll('.settings-menu-link');
    const tabSections = document.querySelectorAll('.setting-section');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links and sections
            tabLinks.forEach(l => l.classList.remove('active'));
            tabSections.forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show corresponding section
            const tabId = this.dataset.tab + '-section';
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Color picker selection
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            colorOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    const passwordForm = document.getElementById('changePasswordForm');
if (passwordForm) {
    passwordForm.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('new_password_confirmation').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (newPassword.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
        
        return true;
    });
}

// Show/hide password toggle (optional enhancement)
const addPasswordToggle = (inputId) => {
    const input = document.getElementById(inputId);
    const parent = input.parentElement;
    const toggle = document.createElement('span');
    toggle.innerHTML = '<i class="fas fa-eye"></i>';
    toggle.style.cssText = 'position: absolute; right: 10px; top: 32px; cursor: pointer; color: #666;';
    parent.style.position = 'relative';
    parent.appendChild(toggle);
    
    toggle.addEventListener('click', function() {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
};

// Initialize toggle for password fields
document.addEventListener('DOMContentLoaded', function() {
    addPasswordToggle('current_password');
    addPasswordToggle('new_password');
    addPasswordToggle('new_password_confirmation');
});
    
    // Theme selection
    const themeOptions = document.querySelectorAll('.theme-option');
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            themeOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});
</script>
@endsection