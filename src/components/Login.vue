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
        <h1 class="login-title">Login</h1>
        <p class="login-subtitle">Please enter your credentials</p>
        
        <form @submit.prevent="login" class="form-content">
          <div class="input-group">
            <input 
              v-model="matric_no" 
              placeholder="Matric No / Email" 
              class="login-input" 
              type="text"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="input-group">
            <input 
              v-model="password" 
              placeholder="Password" 
              class="login-input" 
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
              Logging in...
            </div>
            <div v-else class="login-content">
              Login
              <svg class="arrow-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
              </svg>
            </div>
          </button>
        </form>
        
        <div class="footer-text">
          <span class="footer-link">Click here</span> if you're a new Client
        </div>
        
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
      </div>
    </div>
    
    <div class="login-right">
      <div class="background-image"></div>
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
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.login-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #F1FAEE 0%, #E8F4F8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: 'Roboto', sans-serif;
  position: relative;
  overflow: hidden;
}

.login-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(29, 53, 87, 0.1);
  width: 100%;
  max-width: 420px;
  padding: 40px;
  position: relative;
  z-index: 10;
}

.login-header {
  text-align: center;
  margin-bottom: 40px;
}

.university-logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.logo-circle {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(29, 53, 87, 0.3);
}

.logo-text {
  color: white;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: 2px;
}

.brand-info {
  text-align: center;
}

.brand-title {
  font-size: 32px;
  font-weight: 700;
  color: #1D3557;
  margin-bottom: 4px;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  font-size: 14px;
  color: #457B9D;
  font-weight: 400;
}

.login-form {
  margin-bottom: 30px;
}

.form-title {
  font-size: 28px;
  font-weight: 700;
  color: #1D3557;
  text-align: center;
  margin-bottom: 8px;
}

.form-subtitle {
  font-size: 16px;
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
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  z-index: 2;
}

.icon {
  width: 20px;
  height: 20px;
}

.form-input {
  width: 100%;
  padding: 16px 16px 16px 50px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 16px;
  background: #f8f9fa;
  transition: all 0.3s ease;
  outline: none;
  font-family: 'Roboto', sans-serif;
}

.form-input:focus {
  border-color: #457B9D;
  background: white;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.form-input::placeholder {
  color: #6c757d;
  font-weight: 400;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  font-size: 14px;
}

.remember-check {
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #495057;
  font-weight: 400;
}

.remember-check input[type="checkbox"] {
  margin-right: 8px;
  transform: scale(1.1);
}

.forgot-link {
  color: #457B9D;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #1D3557;
  text-decoration: underline;
}

.login-btn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(29, 53, 87, 0.3);
  font-family: 'Roboto', sans-serif;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(29, 53, 87, 0.4);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: flex;
  align-items: center;
  gap: 8px;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.alert {
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
}

.alert-error {
  background: rgba(230, 57, 70, 0.1);
  color: #E63946;
  border: 1px solid rgba(230, 57, 70, 0.2);
}

.alert-success {
  background: rgba(40, 167, 69, 0.1);
  color: #28a745;
  border: 1px solid rgba(40, 167, 69, 0.2);
}

.alert-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.login-footer {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.footer-text {
  font-size: 14px;
  color: #6c757d;
}

.footer-link {
  color: #457B9D;
  text-decoration: none;
  font-weight: 500;
}

.footer-link:hover {
  text-decoration: underline;
}

.login-right {
  width: 380px;
  height: 600px;
  position: relative;
  overflow: hidden;
  border-radius: 0 12px 12px 0;
}

.background-image {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, 
    rgba(218, 165, 140, 0.9) 0%, 
    rgba(201, 137, 118, 0.95) 50%, 
    rgba(165, 102, 89, 1) 100%);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.background-image::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
}

.decoration-circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(29, 53, 87, 0.05) 0%, rgba(69, 123, 157, 0.05) 100%);
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -150px;
  right: -150px;
}

.circle-2 {
  width: 200px;
  height: 200px;
  bottom: -100px;
  left: -100px;
}

.circle-3 {
  width: 150px;
  height: 150px;
  top: 20%;
  left: -75px;
  background: linear-gradient(135deg, rgba(230, 57, 70, 0.05) 0%, rgba(69, 123, 157, 0.05) 100%);
}

@media (max-width: 768px) {
  .login-container {
    padding: 10px;
  }
  
  .login-left {
    width: 100%;
    max-width: 400px;
    height: auto;
    min-height: 500px;
    border-radius: 12px;
    padding: 40px 30px;
  }
  
  .brand-section {
    position: relative;
    top: 0;
    left: 0;
    margin-bottom: 40px;
    text-align: center;
  }
  
  .login-right {
    display: none;
  }
  
  .login-title {
    font-size: 36px;
    text-align: center;
  }
  
  .login-subtitle {
    text-align: center;
  }
  
  .form-options {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .login-left {
    padding: 30px 20px;
  }
  
  .login-title {
    font-size: 32px;
  }
  
  .login-form {
    max-width: 280px;
  }
}
</style>