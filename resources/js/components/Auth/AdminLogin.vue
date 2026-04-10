<template>
  <div class="login-wrapper">
    <div class="login-container">
      <div class="logo">
        <i class="fas fa-shield-alt"></i>
        <h1>PlanSync Admin</h1>
        <p>Administrator Dashboard</p>
      </div>

      <!-- Error Message - Same as Blade -->
      <div v-if="errorMessage" class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        {{ errorMessage }}
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">
        <i class="fas fa-check-circle"></i>
        {{ successMessage }}
      </div>

      <!-- Login Form - Same structure as Blade -->
      <form method="POST" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input 
            type="email" 
            class="form-control" 
            id="email" 
            name="email"
            v-model="form.email"
            placeholder="admin@plansync.com"
            required 
            autofocus
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-input-wrapper">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              class="form-control" 
              id="password" 
              name="password"
              v-model="form.password"
              placeholder="Enter your password"
              required
            >
            <button type="button" class="password-toggle" @click="togglePasswordVisibility">
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="remember-forgot">
          <label class="remember-me">
            <input type="checkbox" name="remember" v-model="form.remember">
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-password" @click.prevent="showForgotPasswordModal">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          <i class="fas fa-sign-in-alt"></i> 
          {{ loading ? 'Logging in...' : 'Log In' }}
        </button>
      </form>

      <div class="back-link">
        <router-link to="/">
          <i class="fas fa-arrow-left"></i> Back to Main Site
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminLogin',
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
      errorMessage: '',
      successMessage: '',
      loading: false,
      showPassword: false
    };
  },
  mounted() {
    // Check if already logged in
    this.checkAuth();
    
    // Pre-fill email from localStorage (like Blade's old input)
    const savedEmail = localStorage.getItem('admin_email');
    if (savedEmail) {
      this.form.email = savedEmail;
      this.form.remember = true;
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.errorMessage = '';
      this.successMessage = '';
      
      try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Make POST request - same as Blade form submission
        const response = await axios.post('/admin/login', {
          email: this.form.email,
          password: this.form.password,
          remember: this.form.remember
        }, {
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          withCredentials: true
        });
        
        if (response.data.success === true) {
          // Store admin info (like session in Blade)
          if (response.data.admin) {
            localStorage.setItem('admin_user', JSON.stringify(response.data.admin));
          }
          
          if (this.form.remember) {
            localStorage.setItem('admin_email', this.form.email);
          } else {
            localStorage.removeItem('admin_email');
          }
          
          this.successMessage = response.data.message || 'Login successful! Redirecting...';
          
          // Redirect to dashboard (like Blade's redirect)
          setTimeout(() => {
            window.location.href = '/admin/dashboard';
          }, 500);
        } else {
          this.errorMessage = response.data.message || 'Invalid email or password';
        }
      } catch (error) {
        console.error('Login error:', error);
        
        if (error.response) {
          if (error.response.status === 422 && error.response.data.errors) {
            // Validation errors
            const errors = error.response.data.errors;
            this.errorMessage = Object.values(errors).flat()[0];
          } else if (error.response.status === 401) {
            this.errorMessage = error.response.data.message || 'Invalid email or password';
          } else if (error.response.status === 419) {
            this.errorMessage = 'CSRF token mismatch. Please refresh the page.';
          } else {
            this.errorMessage = error.response.data.message || 'Login failed. Please try again.';
          }
        } else if (error.request) {
          this.errorMessage = 'No response from server. Please check your connection.';
        } else {
          this.errorMessage = 'An error occurred. Please try again.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    async checkAuth() {
      try {
        const response = await axios.get('/admin/check', {
          withCredentials: true
        });
        
        if (response.data.authenticated === true) {
          // Already logged in, redirect to dashboard
          window.location.href = '/admin/dashboard';
        }
      } catch (error) {
        // Not authenticated, stay on login page
        console.log('Not authenticated');
      }
    },
    
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    
    showForgotPasswordModal() {
      alert('Please contact the system administrator to reset your password.');
    }
  }
};
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-container {
  background: white;
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
  animation: slideUp 0.5s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.logo {
  text-align: center;
  margin-bottom: 2rem;
}

.logo i {
  font-size: 3rem;
  color: #667eea;
  margin-bottom: 1rem;
}

.logo h1 {
  color: #2d3748;
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
}

.logo p {
  color: #718096;
  font-size: 0.9rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2d3748;
  font-weight: 600;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.password-input-wrapper {
  position: relative;
}

.password-input-wrapper .form-control {
  padding-right: 3rem;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #a0aec0;
  font-size: 1rem;
}

.remember-forgot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #4a5568;
  font-size: 0.9rem;
}

.remember-me input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.forgot-password {
  color: #667eea;
  text-decoration: none;
  font-size: 0.9rem;
}

.forgot-password:hover {
  text-decoration: underline;
}

.btn-login {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-login i {
  margin-right: 8px;
}

.back-link {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.back-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.back-link a:hover {
  text-decoration: underline;
}

.error-message {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: #fff5f5;
  color: #e53e3e;
  border: 1px solid #feb2b2;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.875rem;
}

.success-message {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: #f0fff4;
  color: #38a169;
  border: 1px solid #9ae6b4;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.875rem;
}
</style>