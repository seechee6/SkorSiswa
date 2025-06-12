<template>
  <div class="student-layout">
    <StudentSidebar />
    <div class="main-content">
      <header class="main-header">
        <div class="user-info">
          <span class="welcome">Welcome, {{ studentName }}</span>
          <button class="logout-btn" @click="logout">Logout</button>
        </div>
      </header>
      <main class="content-area">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue'
export default {
  name: 'StudentLayout',
  components: {
    StudentSidebar
  },
  data() {
    return {
      studentName: ''
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      this.$router.push('/login')
    }
  },
  mounted() {
    const user = JSON.parse(localStorage.getItem('user'))
    this.studentName = user?.name || 'Student'
  }
}
</script>

<style scoped>
.student-layout {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  background-color: #f5f6fa;
}

.main-header {
  background: #fff;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome {
  font-size: 1.1rem;
  color: #2c3e50;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.logout-btn:hover {
  background: #c0392b;
}

.content-area {
  padding: 2rem;
  height: calc(100vh - 64px);
  overflow-y: auto;
}
</style>
