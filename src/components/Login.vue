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
        <h2 class="form-title">Welcome Back</h2>
        <p class="form-subtitle">Please sign in to your account</p>

        <form @submit.prevent="login" class="form-content">
          <div class="input-group">
            <div class="input-icon">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <input 
              v-model="identifier" 
              type="text"
              placeholder="Matric No / Staff ID / Email"
              class="form-input"
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
              type="password"
              placeholder="Password"
              class="form-input"
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
            <span v-if="!isLoading">Sign In</span>
            <span v-else class="loading-spinner">
              <svg class="animate-spin icon" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing In...
            </span>
          </button>
        </form>

        <!-- Error/Success Messages -->
        <div v-if="loginResult?.error" class="alert alert-error">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ loginResult.error }}
        </div>

        <div v-if="loginResult?.success" class="alert alert-success">
          <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Login successful! Redirecting...
        </div>
      </div>

      <div class="login-footer">
        <p class="footer-text">
          New to SkorSiswa? <a href="#" class="footer-link">Contact Administrator</a>
        </p>
      </div>
    </div>

    <!-- Background decoration -->
    <div class="bg-decoration">
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
      identifier: '',
      password: '',
      rememberMe: false,
      loginResult: null,
      isLoading: false
    };
  },
  methods: {
    async login() {
      this.isLoading = true;
      this.loginResult = null;
      
      try {
        const response = await api.post('/login', {
          identifier: this.identifier,
          password: this.password
        });
        
        this.loginResult = response.data;
        
        if (response.data.success && response.data.user) {
          localStorage.setItem('user', JSON.stringify(response.data.user));
          
          // Redirect based on role
          const role = response.data.user.role_name.toLowerCase();
          setTimeout(() => {
            if (role === 'lecturer') {
              this.$router.push('/lecturer');
            } else if (role === 'student') {
              this.$router.push('/student');
            } else if (role === 'advisor') {
              this.$router.push('/advisor');
            } else if (role === 'admin') {
              this.$router.push('/admin');
            }
          }, 1000);
        }
      } catch (error) {
        this.loginResult = error.response ? error.response.data : { error: 'Network error occurred' };
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

.bg-decoration {
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

@media (max-width: 480px) {
  .login-card {
    padding: 30px 24px;
    margin: 16px;
  }
  
  .brand-title {
    font-size: 28px;
  }
  
  .form-title {
    font-size: 24px;
  }
  
  .logo-circle {
    width: 60px;
    height: 60px;
  }
  
  .logo-text {
    font-size: 20px;
  }
}
</style>