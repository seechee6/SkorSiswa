<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">SkorSiswa Login</h2>
      <form @submit.prevent="login">
        <input v-model="matric_no" placeholder="Matric No" class="login-input" />
        <input v-model="password" type="password" placeholder="Password" class="login-input" />
        <button type="submit" class="login-btn">Login</button>
      </form>
      <div v-if="loginResult && loginResult.error" class="login-error">{{ loginResult.error }}</div>
      <div v-if="loginResult && loginResult.success" class="login-success">Login successful!</div>
    </div>
  </div>
</template>

<script>
import api from '../api';

export default {
  name: 'Login',
  data() {
    return {
      matric_no: '',
      password: '',
      loginResult: null
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
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(120deg, #3498db 0%, #8e44ad 100%);
}
.login-card {
  background: #fff;
  padding: 40px 32px 32px 32px;
  border-radius: 12px;
  box-shadow: 0 4px 32px rgba(44,62,80,0.12);
  min-width: 320px;
  max-width: 350px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.login-title {
  margin-bottom: 24px;
  color: #2c3e50;
  font-weight: 700;
  font-size: 1.7em;
  letter-spacing: 1px;
}
.login-input {
  width: 100%;
  padding: 12px 14px;
  margin-bottom: 18px;
  border: 1px solid #d0d0d0;
  border-radius: 6px;
  font-size: 1em;
  background: #f7f9fa;
  transition: border 0.2s;
}
.login-input:focus {
  border: 1.5px solid #8e44ad;
  outline: none;
}
.login-btn {
  width: 100%;
  padding: 12px 0;
  background: linear-gradient(90deg, #3498db 0%, #8e44ad 100%);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 1.1em;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  margin-bottom: 8px;
}
.login-btn:hover {
  background: linear-gradient(90deg, #2980b9 0%, #6c3483 100%);
}
.login-error {
  color: #e74c3c;
  margin-top: 8px;
  font-size: 0.98em;
  text-align: center;
}
.login-success {
  color: #27ae60;
  margin-top: 8px;
  font-size: 0.98em;
  text-align: center;
}
</style>