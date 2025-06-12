<template>
  <div class="admin-sidebar" :class="{ 'collapsed': isCollapsed }">
    <div class="sidebar-header">
      <img src="@/assets/logo.png" alt="SkorSiswa Logo" class="logo">
      <button class="toggle-btn" @click="toggleSidebar">
        <i class="fas" :class="isCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <router-link to="/admin/dashboard" class="nav-item" title="Dashboard">
        <i class="fas fa-tachometer-alt"></i>
        <span v-if="!isCollapsed">Dashboard</span>
      </router-link>
      
      <router-link to="/admin/users" class="nav-item" title="Manage Users">
        <i class="fas fa-users"></i>
        <span v-if="!isCollapsed">Manage Users</span>
      </router-link>

      <router-link to="/admin/courses" class="nav-item" title="Manage Courses">
        <i class="fas fa-book"></i>
        <span v-if="!isCollapsed">Manage Courses</span>
      </router-link>

      <router-link to="/admin/enrollments" class="nav-item" title="Student Enrollments">
        <i class="fas fa-user-graduate"></i>
        <span v-if="!isCollapsed">Student Enrollments</span>
      </router-link>

      <router-link to="/admin/assignments" class="nav-item" title="Lecturer Assignments">
        <i class="fas fa-chalkboard-teacher"></i>
        <span v-if="!isCollapsed">Lecturer Assignments</span>
      </router-link>

      <router-link to="/admin/logs" class="nav-item" title="System Logs">
        <i class="fas fa-history"></i>
        <span v-if="!isCollapsed">System Logs</span>
      </router-link>

      <router-link to="/admin/password-reset" class="nav-item" title="Password Management">
        <i class="fas fa-key"></i>
        <span v-if="!isCollapsed">Password Management</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info" v-if="!isCollapsed">
        <img :src="userAvatar" alt="User Avatar" class="avatar">
        <div class="user-details">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">Administrator</span>
        </div>
      </div>
      <button class="logout-btn" @click="logout" :title="isCollapsed ? 'Logout' : ''">
        <i class="fas fa-sign-out-alt"></i>
        <span v-if="!isCollapsed">Logout</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminSidebar',
  data() {
    return {
      isCollapsed: false,
      userName: 'Admin User', // This should come from your auth system
      userAvatar: 'https://via.placeholder.com/40' // This should be the user's actual avatar
    }
  },
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
      this.$emit('sidebar-toggle', this.isCollapsed);
    },
    logout() {
      // Implement logout logic here
      this.$router.push('/login');
    }
  }
}
</script>

<style scoped>
.admin-sidebar {
  width: 250px;
  height: 100vh;
  background-color: #2c3e50;
  color: white;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
}

.admin-sidebar.collapsed {
  width: 60px;
}

.sidebar-header {
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  height: 40px;
  width: auto;
}

.toggle-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 5px;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.8rem 1rem;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
}

.nav-item:hover, .nav-item.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-item i {
  width: 20px;
  text-align: center;
  margin-right: 1rem;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 0.5rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: bold;
}

.user-role {
  font-size: 0.8rem;
  opacity: 0.8;
}

.logout-btn {
  width: 100%;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  cursor: pointer;
  border-radius: 4px;
}

.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.logout-btn i {
  margin-right: 0.5rem;
}

@media (max-width: 768px) {
  .admin-sidebar {
    width: 60px;
  }

  .admin-sidebar:not(.collapsed) {
    width: 250px;
  }
}
</style>
