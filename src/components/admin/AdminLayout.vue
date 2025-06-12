<template>
  <div class="admin-layout">
    <AdminSidebar @sidebar-toggle="handleSidebarToggle" />
    <div class="admin-main" :class="{ 'sidebar-collapsed': isSidebarCollapsed }">
      <div class="admin-header">
        <h1>{{ pageTitle }}</h1>
        <div class="header-actions">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
          </div>
          <button class="notifications-btn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" v-if="notificationCount">{{ notificationCount }}</span>
          </button>
        </div>
      </div>
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';

export default {
  name: 'AdminLayout',
  components: {
    AdminSidebar
  },
  data() {
    return {
      isSidebarCollapsed: false,
      notificationCount: 3,
      pageTitle: 'Admin Dashboard'
    }
  },
  methods: {
    handleSidebarToggle(collapsed) {
      this.isSidebarCollapsed = collapsed;
    }
  },
  watch: {
    '$route'(to) {
      // Update page title based on route
      this.pageTitle = to.meta.title || 'Admin Dashboard';
    }
  }
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}

.admin-main {
  flex: 1;
  margin-left: 250px;
  transition: margin-left 0.3s ease;
  padding: 20px;
  background-color: #f5f6fa;
}

.admin-main.sidebar-collapsed {
  margin-left: 60px;
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-bar {
  display: flex;
  align-items: center;
  background-color: #f5f6fa;
  padding: 8px 15px;
  border-radius: 20px;
}

.search-bar input {
  border: none;
  background: none;
  margin-left: 10px;
  outline: none;
  width: 200px;
}

.notifications-btn {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
}

.notification-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #e74c3c;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .admin-main {
    margin-left: 60px;
    padding: 10px;
  }

  .search-bar input {
    width: 150px;
  }
}
</style>
