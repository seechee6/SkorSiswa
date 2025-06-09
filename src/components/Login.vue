<template>
  <div class="login-container">
    <div class="login-left">
      <div class="brand-section">
        <div class="brand-logo">
          <div class="logo-square"></div>
          <span class="brand-name">SkorSiswa</span>
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
            />
          </div>
          
          <div class="input-group">
            <input 
              v-model="password" 
              placeholder="Password" 
              class="login-input" 
              type="password"
            />
          </div>
          
          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" v-model="rememberMe">
              Remember me
            </label>
            <a href="#" class="forgot-password">Forgot Password?</a>
          </div>
          
          <button type="submit" class="next-btn">
            Login
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
            </svg>
          </button>
        </form>
        
        <div class="footer-text">
          <span class="footer-link">Click here</span> if you're a new Client
        </div>
        
        <div v-if="loginResult && loginResult.error" class="login-error">{{ loginResult.error }}</div>
        <div v-if="loginResult && loginResult.success" class="login-success">Login successful!</div>
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
  name: 'LoginForm',
  data() {
    return {
      matric_no: '',
      password: '',
      loginResult: null,
      rememberMe: false
    };
  },
  methods: {
    async login() {
      try {
        const response = await api.post('/login', {
          matric_no: this.matric_no,
          password: this.password
        });
        this.loginResult = response.data;
        if (response.data.success && response.data.user) {
          localStorage.setItem('user', JSON.stringify(response.data.user));
          if (response.data.user.role_name === 'Lecturer') {
            this.$router.push('/lecturer');
          } else if (response.data.user.role_name === 'Student') {
            this.$router.push('/student');
          } else if (response.data.user.role_name === 'Advisor') {
            this.$router.push('/advisor');
          } else if (response.data.user.role_name === 'Admin') {
            this.$router.push('/admin');
          }
        }
      } catch (error) {
        this.loginResult = error.response ? error.response.data : { error: 'Network error' };
      }
    }
  }
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  padding: 20px;
}

.login-left {
  width: 420px;
  height: 600px;
  padding: 50px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: white;
  border-radius: 12px 0 0 12px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  position: relative;
}

.brand-section {
  position: absolute;
  top: 30px;
  left: 40px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-square {
  width: 24px;
  height: 24px;
  background: #000;
  border-radius: 3px;
}

.brand-name {
  font-size: 16px;
  font-weight: 500;
  color: #000;
  letter-spacing: 0.5px;
}

.login-form {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 320px;
  margin: 0 auto;
}

.login-title {
  font-size: 42px;
  font-weight: 300;
  color: #2c3e50;
  margin: 0 0 16px 0;
  line-height: 1.1;
  letter-spacing: -1px;
}

.login-subtitle {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0 0 40px 0;
  font-weight: 300;
}

.form-content {
  margin-bottom: 0;
}

.input-group {
  margin-bottom: 30px;
  position: relative;
}

.login-input {
  width: 100%;
  border: none;
  border-bottom: 1px solid #ecf0f1;
  padding: 16px 0;
  font-size: 16px;
  background: transparent;
  outline: none;
  color: #2c3e50;
  transition: border-color 0.3s ease;
  font-weight: 400;
}

.login-input::placeholder {
  color: #bdc3c7;
  font-weight: 300;
}

.login-input:focus {
  border-bottom-color: #2c3e50;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  font-size: 14px;
}

.remember-me {
  display: flex;
  align-items: center;
  color: #7f8c8d;
  cursor: pointer;
  transition: color 0.3s ease;
}

.remember-me:hover {
  color: #2c3e50;
}

.remember-me input[type="checkbox"] {
  margin-right: 8px;
  transform: scale(0.9);
}

.forgot-password {
  color: #7f8c8d;
  text-decoration: none;
  transition: color 0.3s ease;
  font-weight: 400;
}

.forgot-password:hover {
  color: #2c3e50;
  text-decoration: underline;
}

.next-btn {
  background: #000;
  color: white;
  border: none;
  padding: 16px 32px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
  font-weight: 500;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.next-btn:hover {
  background: #333;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.arrow-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.3s ease;
}

.next-btn:hover .arrow-icon {
  transform: translateX(3px);
}

.footer-text {
  font-size: 13px;
  color: #95a5a6;
  text-align: center;
  margin-top: 30px;
  font-weight: 300;
}

.footer-link {
  color: #2c3e50;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #000;
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
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><circle cx="100" cy="100" r="60" fill="rgba(255,255,255,0.1)"/><circle cx="300" cy="150" r="80" fill="rgba(255,255,255,0.08)"/><circle cx="200" cy="300" r="70" fill="rgba(255,255,255,0.06)"/></svg>') no-repeat center;
  background-size: cover;
  opacity: 0.7;
}

.login-error {
  color: #e74c3c;
  margin-top: 16px;
  font-size: 13px;
  padding: 10px 12px;
  background: rgba(231, 76, 60, 0.1);
  border-radius: 4px;
  border-left: 3px solid #e74c3c;
}

.login-success {
  color: #27ae60;
  margin-top: 16px;
  font-size: 13px;
  padding: 10px 12px;
  background: rgba(39, 174, 96, 0.1);
  border-radius: 4px;
  border-left: 3px solid #27ae60;
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