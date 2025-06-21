<template>
  <div class="lecturer-layout">
    <!-- Top Navigation Bar -->
    <div class="top-nav">
      <div class="nav-left">
        <div class="logo">
          <div class="logo-circle">
            <span class="logo-text">SS</span>
          </div>
          <span class="brand-name">SkorSiswa</span>
        </div>
      </div>
      <div class="nav-center">
        <span class="role-badge">Lecturer</span>
      </div>
      <div class="nav-right">
        <div class="user-profile">
          <div class="user-avatar">
            <svg class="avatar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div class="user-info">
            <span class="user-name">{{ user?.full_name }}</span>
            <span class="user-id">{{ user?.staff_id }}</span>
          </div>
          <button @click="logout" class="logout-btn">
            <svg class="logout-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Sidebar -->
      <div class="sidebar">
        <nav class="sidebar-nav">
          <router-link to="/lecturer/dashboard" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            <span class="nav-label">Dashboard</span>
          </router-link>

          <router-link to="/lecturer/manage-courses" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <span class="nav-label">Manage Courses</span>
          </router-link>

          <router-link to="/lecturer/manage-assessments" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span class="nav-label">Assessments</span>
          </router-link>

          <router-link to="/lecturer/enter-final-marks" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="nav-label">Manage Marks</span>
          </router-link>          <router-link to="/lecturer/feedback" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <span class="nav-label">Feedback</span>
          </router-link>

          <router-link to="/lecturer/remark-reviews" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="nav-label">Remark Reviews</span>
            <span v-if="pendingRemarksCount > 0" class="notification-badge">{{ pendingRemarksCount }}</span>
          </router-link>

          <div class="nav-section">
            <span class="section-title">Analytics & Reports</span>
            
            <router-link to="/lecturer/analytics" class="nav-item" active-class="active">
              <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <span class="nav-label">Analytics</span>
            </router-link>
          </div>

          <router-link to="/lecturer/notifications" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5v5M6 17h5l-5-5v5m0 0V7a2 2 0 012-2h4a2 2 0 012 2v6"></path>
            </svg>
            <span class="nav-label">Notifications</span>
          </router-link>
        </nav>
      </div>

      <!-- Page Content -->
      <div class="page-content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'LecturerLayout',
  data() {
    return {
      user: null,
      unreadCount: 0,
      pendingRemarksCount: 0
    };
  },
  mounted() {
    this.user = JSON.parse(localStorage.getItem('user') || '{}');
    this.fetchNotificationCount();
    this.fetchPendingRemarksCount();
  },
  methods: {
    logout() {
      localStorage.removeItem('user');
      this.$router.push('/');
    },
    async fetchNotificationCount() {
      try {
        // You can implement this to fetch actual notification count
        this.unreadCount = 3; // Placeholder
      } catch (error) {
        console.error('Error fetching notifications:', error);
      }
    },
    async fetchPendingRemarksCount() {
      try {
        if (this.user?.id) {
          const response = await api.get(`/lecturers/${this.user.id}/remark-requests?status=pending`);
          this.pendingRemarksCount = response.data.requests ? response.data.requests.length : 0;
        }
      } catch (error) {
        console.error('Error fetching pending remarks count:', error);
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

.lecturer-layout {
  min-height: 100vh;
  background: #F1FAEE;
  font-family: 'Roboto', sans-serif;
  margin: 0;
  padding: 0;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.top-nav {
  background: white;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 100;
  margin: 0;
}

.nav-left {
  display: flex;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-circle {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  color: white;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 1px;
}

.brand-name {
  font-size: 24px;
  font-weight: 700;
  color: #1D3557;
}

.nav-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.role-badge {
  background: linear-gradient(135deg, #457B9D 0%, #1D3557 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.nav-right {
  display: flex;
  align-items: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: #F1FAEE;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #457B9D;
}

.avatar-icon {
  width: 20px;
  height: 20px;
  color: #457B9D;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #1D3557;
}

.user-id {
  font-size: 12px;
  color: #6c757d;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #E63946;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: #c53030;
  transform: translateY(-1px);
}

.logout-icon {
  width: 16px;
  height: 16px;
}

.main-content {
  display: flex;
  min-height: calc(100vh - 70px);
}

.sidebar {
  width: 280px;
  background: white;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
  padding: 24px 0;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 16px;
}

.nav-section {
  margin: 20px 0 8px 0;
}

.section-title {
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-left: 16px;
  margin-bottom: 8px;
  display: block;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #495057;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(69, 123, 157, 0.1);
  color: #1D3557;
  transform: translateX(4px);
}

.nav-item.active {
  background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(29, 53, 87, 0.3);
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-label {
  font-size: 14px;
}

.notification-badge {
  background: #E63946;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: auto;
}

.page-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .sidebar {
    width: 240px;
  }
  
  .page-content {
    padding: 20px;
  }
  
  .top-nav {
    padding: 0 16px;
  }
  
  .user-info {
    display: none;
  }
}
</style>
