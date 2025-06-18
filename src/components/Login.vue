<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="university-logo">
          <div class="logo-circle">
            <span class="logo-text">SS</span>
          </div>
          <div class="brand-info">
            <h1 class="brand-title">SkorSiswa</h1>
            <p class="brand-subtitle">Academic Management System</p>
          </div>
        </div>
      </div>

      <div class="login-form">
        <h2 class="login-title">Welcome Back</h2>
        <p class="login-subtitle">Please sign in to your account</p>
        
        <form @submit.prevent="login" class="form-content">
          <div class="input-group">
            <div class="input-icon">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <input 
              v-model="matric_no" 
              placeholder="Matric No / Email" 
              class="form-input" 
              type="text"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <input 
              v-model="password" 
              placeholder="Password" 
              class="form-input" 
              type="password"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="form-options">
            <label class="remember-check">
              <input type="checkbox" v-model="rememberMe">
              <span class="checkmark"></span>
              Remember me
            </label>
            <a href="#" class="forgot-link">Forgot Password?</a>
          </div>
          
          <button type="submit" class="login-btn" :disabled="isLoading">
            <div v-if="isLoading" class="loading-spinner">
              <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/>
                <path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z"/>
              </svg>
              Signing in...
            </div>
            <div v-else class="login-content">
              Sign In
              <svg class="arrow-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
              </svg>
            </div>
          </button>
        </form>
        
        <!-- Error and Success Messages -->
        <div v-if="loginResult && loginResult.error" class="alert alert-error">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          {{ loginResult.error }}
        </div>
        <div v-if="loginResult && loginResult.success" class="alert alert-success">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Login successful! Redirecting...
        </div>
        
        <div class="login-footer">
          <div class="footer-text">
            New to SkorSiswa? <a href="#" class="footer-link">Contact Administrator</a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Background decoration -->
    <div class="decoration-elements">
      <div class="decoration-circle circle-1"></div>
      <div class="decoration-circle circle-2"></div>
      <div class="decoration-circle circle-3"></div>
    </div>
  </div>
</template>

<script>
import api from '../api';

export default {
  name: 'UserLogin',
  data() {
    return {
      matric_no: '',
      password: '',
      rememberMe: false,
      loginResult: null,
      isLoading: false
    };
  },
  methods: {
    async login() {
      // Reset previous results
      this.loginResult = null;
      this.isLoading = true;
      
      // Basic validation
      if (!this.matric_no.trim() || !this.password.trim()) {
        this.loginResult = { error: 'Please fill in both matric number and password.' };
        this.isLoading = false;
        return;
      }
      
      try {
        console.log('Attempting login with:', { matric_no: this.matric_no });
        
        const response = await api.post('/login', {
          matric_no: this.matric_no.trim(),
          password: this.password.trim()
        });
        
        console.log('Login response:', response.data);
        this.loginResult = response.data;
        
        if (response.data.success && response.data.user) {
          localStorage.setItem('user', JSON.stringify(response.data.user));
          
          // Show success message briefly before redirecting
          setTimeout(() => {
            const role = response.data.user.role_name.toLowerCase(); // Convert to lowercase for comparison
            if (role === 'lecturer') {
              this.$router.push('/lecturer');
            } else if (role === 'student') {
              this.$router.push('/student');
            } else if (role === 'advisor') {
              this.$router.push('/advisor');
            } else if (role === 'admin') {
              this.$router.push('/admin');
            } else {
              this.loginResult = { error: 'Unknown user role. Please contact administrator.' };
            }
          }, 1000);
        }
      } catch (error) {
        console.error('Login error:', error);
        
        if (error.response) {
          // Server responded with error status
          this.loginResult = error.response.data;
        } else if (error.request) {
          // Request was made but no response received
          this.loginResult = { 
            error: 'Cannot connect to server. Please check if the backend is running on http://localhost:8080' 
          };
        } else {
          // Something else happened
          this.loginResult = { 
            error: 'An unexpected error occurred. Please try again.' 
          };
        }
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
  overflow-x: hidden;
}

.login-container {
  min-height: 100vh;
  height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 50%, #F1FAEE 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 30px;
  margin: 0;
  font-family: 'Inter', sans-serif;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow-y: auto;
  overflow-x: hidden;
}

.login-card {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(29, 53, 87, 0.2);
  width: 100%;
  max-width: 380px;
  padding: 36px 32px;
  position: relative;
  z-index: 10;
  border: 1px solid rgba(255, 255, 255, 0.3);
  animation: slideUp 0.6s ease-out;
  margin: auto;
  flex-shrink: 0;
}

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

.login-header {
  text-align: center;
  margin-bottom: 36px;
}

.university-logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.logo-circle {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(29, 53, 87, 0.4);
  position: relative;
  overflow: hidden;
}

.logo-circle::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.logo-text {
  color: white;
  font-size: 26px;
  font-weight: 700;
  letter-spacing: 1.5px;
  position: relative;
  z-index: 1;
}

.brand-info {
  text-align: center;
}

.brand-title {
  font-size: 30px;
  font-weight: 700;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 6px;
  letter-spacing: -1px;
}

.brand-subtitle {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
  letter-spacing: 0.3px;
}

.login-form {
  width: 100%;
}

.login-title {
  font-size: 26px;
  font-weight: 700;
  color: #1D3557;
  text-align: center;
  margin-bottom: 6px;
  letter-spacing: -0.5px;
}

.login-subtitle {
  font-size: 14px;
  color: #6c757d;
  text-align: center;
  margin-bottom: 32px;
  font-weight: 400;
}

.input-group {
  position: relative;
  margin-bottom: 24px;
}

.input-icon {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  z-index: 2;
  transition: color 0.3s ease;
}

.icon {
  width: 22px;
  height: 22px;
}

.form-input {
  width: 100%;
  padding: 16px 16px 16px 52px;
  border: 2px solid #e2e8f0;
  border-radius: 14px;
  font-size: 15px;
  background: #f8fafc;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
}

.form-input:focus {
  border-color: #457B9D;
  background: white;
  box-shadow: 0 0 0 4px rgba(69, 123, 157, 0.1);
  transform: translateY(-1px);
}

.form-input:focus + .input-icon,
.input-group:focus-within .input-icon {
  color: #457B9D;
}

.form-input::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  font-size: 13px;
}

.remember-check {
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #495057;
  font-weight: 500;
  gap: 8px;
}

.remember-check input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #457B9D;
  cursor: pointer;
}

.forgot-link {
  color: #457B9D;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  position: relative;
}

.forgot-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -2px;
  left: 0;
  background: #457B9D;
  transition: width 0.3s ease;
}

.forgot-link:hover::after {
  width: 100%;
}

.forgot-link:hover {
  color: #1D3557;
}

.login-btn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #457B9D 0%, #1D3557 100%);
  color: white;
  border: none;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.3);
  font-family: 'Inter', sans-serif;
  position: relative;
  overflow: hidden;
}

.login-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(69, 123, 157, 0.4);
}

.login-btn:hover:not(:disabled)::before {
  left: 100%;
}

.login-btn:active {
  transform: translateY(0);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  display: flex;
  align-items: center;
  gap: 10px;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.login-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.arrow-icon {
  width: 18px;
  height: 18px;
  transition: transform 0.3s ease;
}

.login-btn:hover .arrow-icon {
  transform: translateX(2px);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.alert {
  padding: 14px 18px;
  border-radius: 10px;
  margin-top: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 500;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.alert-error {
  background: rgba(230, 57, 70, 0.1);
  color: #E63946;
  border: 1px solid rgba(230, 57, 70, 0.2);
}

.alert-success {
  background: rgba(39, 174, 96, 0.1);
  color: #27ae60;
  border: 1px solid rgba(39, 174, 96, 0.2);
}

.alert-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.login-footer {
  text-align: center;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

.footer-text {
  font-size: 13px;
  color: #6c757d;
  font-weight: 500;
}

.footer-link {
  color: #457B9D;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #1D3557;
  text-decoration: underline;
}

.decoration-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  overflow: hidden;
}

.decoration-circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(241, 250, 238, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  animation: float 6s ease-in-out infinite;
}

.circle-1 {
  width: 400px;
  height: 400px;
  top: -200px;
  right: -200px;
  animation-delay: 0s;
}

.circle-2 {
  width: 300px;
  height: 300px;
  bottom: -150px;
  left: -150px;
  animation-delay: 2s;
}

.circle-3 {
  width: 200px;
  height: 200px;
  top: 50%;
  left: -100px;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

@media (max-width: 768px) {
  .login-container {
    padding: 30px 20px;
  }
  
  .login-card {
    max-width: 380px;
    padding: 32px 28px;
    border-radius: 20px;
    margin: auto;
  }
  
  .brand-title {
    font-size: 28px;
  }
  
  .login-title {
    font-size: 24px;
  }
  
  .form-options {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .login-container {
    padding: 25px 15px;
  }
  
  .login-card {
    padding: 28px 22px;
    margin: auto;
  }
  
  .brand-title {
    font-size: 24px;
  }
  
  .login-title {
    font-size: 20px;
  }
  
  .form-input {
    padding: 14px 14px 14px 50px;
  }
  
  .login-btn {
    padding: 14px;
  }
}
</style>