<template>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="logo">
                        <i class="fas fa-calendar-alt"></i>
                        <h1>PlanSync</h1>
                    </div>
                    <p class="tagline">Start your academic journey</p>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="handleRegister">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i>
                            Full Name
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="text" 
                                id="name" 
                                class="form-control" 
                                v-model="form.name" 
                                placeholder="John Smith"
                                required
                                :disabled="loading"
                            >
                        </div>
                        <div v-if="errors.name" class="error-message">{{ errors.name[0] }}</div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                id="email" 
                                class="form-control" 
                                v-model="form.email" 
                                placeholder="you@example.com"
                                required
                                :disabled="loading"
                            >
                        </div>
                        <div v-if="errors.email" class="error-message">{{ errors.email[0] }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                id="password" 
                                class="form-control" 
                                v-model="form.password" 
                                placeholder="Create a password"
                                required
                                :disabled="loading"
                            >
                            <span class="password-toggle" @click="showPassword = !showPassword">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </span>
                        </div>
                        <small class="form-text">Password must be at least 8 characters</small>
                        <div v-if="errors.password" class="error-message">{{ errors.password[0] }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">
                            <i class="fas fa-check-circle"></i>
                            Confirm Password
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-check-circle input-icon"></i>
                            <input 
                                :type="showConfirmPassword ? 'text' : 'password'" 
                                id="password_confirmation" 
                                class="form-control" 
                                v-model="form.password_confirmation" 
                                placeholder="Confirm your password"
                                required
                                :disabled="loading"
                            >
                            <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </span>
                        </div>
                        <div v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation[0] }}</div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" v-model="form.terms" required>
                            <span>I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
                        <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-user-plus"></i>
                        {{ loading ? 'Creating Account...' : 'Create Account' }}
                    </button>

                    <div class="auth-footer">
                        <p>Already have an account? 
                            <router-link to="/login" class="auth-link">Log In</router-link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Register',
    
    data() {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                terms: false
            },
            errors: {},
            errorMessage: '',
            loading: false,
            showPassword: false,
            showConfirmPassword: false
        };
    },

    methods: {
        async handleRegister() {
            this.loading = true;
            this.errors = {};
            this.errorMessage = '';
            
            // Client-side validation
            if (this.form.password !== this.form.password_confirmation) {
                this.errorMessage = 'Passwords do not match!';
                this.loading = false;
                return;
            }
            
            if (this.form.password.length < 8) {
                this.errorMessage = 'Password must be at least 8 characters long!';
                this.loading = false;
                return;
            }
            
            if (!this.form.terms) {
                this.errorMessage = 'You must agree to the Terms of Service';
                this.loading = false;
                return;
            }
            
            try {
                const response = await axios.post('/register', this.form);
                
                if (response.data.success) {
                    // Store user data correctly
                    const userData = response.data.user;
                    
                    localStorage.setItem('auth_token', 'authenticated');
                    localStorage.setItem('user_id', userData.id);
                    localStorage.setItem('user_name', userData.name);
                    localStorage.setItem('user_email', userData.email);
                    localStorage.setItem('user_avatar_color', userData.avatar_color || '#4361ee');
                    
                    // Also update window object for other components
                    window.userId = userData.id;
                    window.userName = userData.name;
                    window.userEmail = userData.email;
                    window.userAvatarColor = userData.avatar_color || '#4361ee';
                    
                    console.log('User registered:', userData);
                    
                    // Redirect to dashboard
                    this.$router.push('/dashboard');
                } else {
                    this.errorMessage = response.data.message || 'Registration failed';
                }
            } catch (error) {
                console.error('Registration error:', error);
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.errorMessage = 'Please fix the errors below.';
                } else {
                    this.errorMessage = error.response?.data?.message || 'An error occurred. Please try again.';
                }
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
}

.auth-container {
    width: 100%;
    max-width: 480px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    animation: slideUp 0.5s ease;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.logo i {
    font-size: 2rem;
    color: #4361ee;
}

.logo h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.tagline {
    color: #64748b;
    font-size: 0.9rem;
}

/* Alert */
.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

/* Form */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #1e293b;
    font-size: 0.9rem;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 0.85rem 1rem 0.85rem 2.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #94a3b8;
    transition: color 0.2s;
}

.password-toggle:hover {
    color: #4361ee;
}

.error-message {
    color: #dc2626;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.form-text {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.25rem;
    display: block;
}

/* Form Options */
.form-options {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.85rem;
    color: #475569;
}

.checkbox-label input {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.terms-link {
    color: #4361ee;
    text-decoration: none;
}

.terms-link:hover {
    text-decoration: underline;
}

/* Button */
.btn {
    padding: 0.85rem 1.5rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-block {
    width: 100%;
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

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Auth Footer */
.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.auth-footer p {
    color: #64748b;
    font-size: 0.9rem;
}

.auth-link {
    color: #4361ee;
    text-decoration: none;
    font-weight: 600;
}

.auth-link:hover {
    text-decoration: underline;
}

/* Animation */
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
@media (max-width: 480px) {
    .auth-card {
        padding: 1.5rem;
    }
    
    .logo h1 {
        font-size: 1.5rem;
    }
    
    .form-options {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
    }
}
</style>