<template>
    <div class="auth-page">
        <div class="auth-card">
            <h1><i class="fas fa-calendar-alt"></i> PlanSync</h1>

            <div v-if="errorMessage" class="alert alert-error">
                <p>{{ errorMessage }}</p>
            </div>

            <form @submit.prevent="handleRegister">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="name" 
                        v-model="form.name" 
                        required 
                        placeholder="John Smith"
                        :disabled="loading"
                    >
                    <div v-if="errors.name" class="error-message">{{ errors.name[0] }}</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        v-model="form.email" 
                        required 
                        placeholder="you@example.com"
                        :disabled="loading"
                    >
                    <div v-if="errors.email" class="error-message">{{ errors.email[0] }}</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            class="form-control" 
                            id="password" 
                            v-model="form.password" 
                            required 
                            placeholder="min 6 chars"
                            :disabled="loading"
                        >
                        <span class="password-toggle" @click="showPassword = !showPassword">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </span>
                    </div>
                    <div v-if="errors.password" class="error-message">{{ errors.password[0] }}</div>
                    <small class="form-text">Password must be at least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-wrapper">
                        <input 
                            :type="showConfirmPassword ? 'text' : 'password'" 
                            class="form-control" 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            required 
                            placeholder="Confirm password"
                            :disabled="loading"
                        >
                        <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                            <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </span>
                    </div>
                    <div v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation[0] }}</div>
                </div>
                
                <button type="submit" class="btn" :disabled="loading">
                    <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                    {{ loading ? 'Creating Account...' : 'Create Account' }}
                </button>
                
                <div class="auth-links">
                    <a :href="loginUrl">Already have an account? <strong>Log In</strong></a>
                </div>
            </form>
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
                password_confirmation: ''
            },
            errors: {},
            errorMessage: '',
            loading: false,
            showPassword: false,
            showConfirmPassword: false,
            loginUrl: '/login'
        };
    },

    methods: {
        async handleRegister() {
            this.loading = true;
            this.errors = {};
            this.errorMessage = '';
            
            // Validate password match
            if (this.form.password !== this.form.password_confirmation) {
                this.errorMessage = 'Passwords do not match.';
                this.loading = false;
                return;
            }
            
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await axios.post('/register', this.form, {
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.location.href = '/dashboard';
                }
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.errorMessage = 'Please check your input.';
                } else if (error.response && error.response.status === 401) {
                    this.errorMessage = error.response.data.message || 'Registration failed.';
                } else {
                    this.errorMessage = 'An error occurred. Please try again.';
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
    background: linear-gradient(135deg, var(--primary, #4361ee) 0%, var(--secondary, #3a0ca3) 100%);
    padding: 1.5rem;
}

.auth-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.auth-card h1 {
    font-size: 2rem;
    color: var(--primary, #4361ee);
    text-align: center;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-error p {
    margin: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark, #212529);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary, #4361ee);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.form-control:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

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

.form-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: block;
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    background: var(--primary, #4361ee);
    color: white;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    width: 100%;
}

.btn:hover:not(:disabled) {
    background: #3451d1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
}

.auth-links a {
    color: var(--primary, #4361ee);
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 1.5rem;
    }
    
    .auth-card h1 {
        font-size: 1.5rem;
    }
}
</style>