<template>
  <div class="advisor-layout">
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
        <span class="role-badge">Advisor</span>
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
          <router-link to="/advisor/dashboard" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            <span class="nav-label">Dashboard</span>
          </router-link>

          <router-link to="/advisor/advisee-list" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="nav-label">Advisee List</span>
          </router-link>

          <router-link to="/advisor/mark-breakdown" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span class="nav-label">Mark Breakdown</span>
          </router-link>

          <router-link to="/advisor/at-risk-students" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span class="nav-label">At-Risk Students</span>
          </router-link>

          <router-link to="/advisor/meeting-notes" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="nav-label">Meeting Notes</span>
          </router-link>

          <router-link to="/advisor/advisee-reports" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="nav-label">Generate Reports</span>
          </router-link>

          <router-link to="/advisor/course-performance" class="nav-item" active-class="active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
            <span class="nav-label">Course Performance</span>
          </router-link>

          <div class="nav-section">
            <span class="section-title">Analytics</span>
            <router-link to="/advisor/performance-analytics" class="nav-item" active-class="active">
              <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <span class="nav-label">Analytics</span>
            </router-link>

            <router-link to="/advisor/performance-trends" class="nav-item" active-class="active">
              <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
              </svg>
              <span class="nav-label">Trend Graphs</span>
            </router-link>
          </div>
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
export default {
  name: 'AdvisorLayout',
  data() {
    return {
      user: null
    }
  },
  mounted() {
    this.loadUser()
  },
  methods: {
    loadUser() {
      const userData = localStorage.getItem('user')
      if (userData) {
        this.user = JSON.parse(userData)
      }
    },
    logout() {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
.advisor-layout {
  min-height: 100vh;
  background: #f8f9fa;
}

.top-nav {
  background: #1D3557;
  color: white;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 100;
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
  width: 36px;
  height: 36px;
  background: #457B9D;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  font-size: 14px;
  font-weight: 700;
  color: white;
}

.brand-name {
  font-size: 20px;
  font-weight: 700;
  color: #F1FAEE;
}

.nav-center {
  display: flex;
  align-items: center;
}

.role-badge {
  background: #457B9D;
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  border: 2px solid #F1FAEE;
}

.nav-right {
  display: flex;
  align-items: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: #457B9D;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-icon {
  width: 20px;
  height: 20px;
  color: white;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: white;
}

.user-id {
  font-size: 12px;
  color: #A8DADC;
}

.logout-btn {
  background: transparent;
  border: 1px solid #457B9D;
  color: #F1FAEE;
  padding: 8px 16px;
  border-radius: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  margin-left: 16px;
}

.logout-btn:hover {
  background: #457B9D;
  color: white;
  transform: translateY(-1px);
}

.logout-icon {
  width: 16px;
  height: 16px;
}

.main-content {
  display: flex;
  min-height: calc(100vh - 64px);
}

.sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #e9ecef;
  box-shadow: 2px 0 8px rgba(0,0,0,0.06);
}

.sidebar-nav {
  padding: 24px 0;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  color: #6c757d;
  text-decoration: none;
  transition: all 0.2s;
  gap: 12px;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: #f8f9fa;
  color: #1D3557;
  border-left-color: #A8DADC;
}

.nav-item.active {
  background: #E8F4FD;
  color: #1D3557;
  border-left-color: #457B9D;
  font-weight: 600;
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-label {
  font-size: 14px;
}

.nav-section {
  margin-top: 32px;
  padding-top: 16px;
  border-top: 1px solid #e9ecef;
}

.section-title {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0 24px 12px;
}

.page-content {
  flex: 1;
  padding: 32px;
  background: #f8f9fa;
  overflow-x: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
  .main-content {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    order: 2;
  }
  
  .page-content {
    order: 1;
    padding: 16px;
  }
  
  .nav-item {
    padding: 16px 24px;
  }
  
  .user-info {
    display: none;
  }
  
  .logout-btn {
    margin-left: 8px;
    padding: 8px 12px;
  }
  
  .logout-btn span {
    display: none;
  }
}
</style>
