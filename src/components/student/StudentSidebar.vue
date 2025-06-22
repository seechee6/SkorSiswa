<template>  <nav class="sidebar-nav">
    <router-link to="/student/dashboard" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
      </svg>
      <span class="nav-label">Dashboard</span>
    </router-link>

    <router-link to="/student/marks" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
      </svg>
      <span class="nav-label">Mark Breakdown</span>
    </router-link>

    <router-link to="/student/compare" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
      </svg>
      <span class="nav-label">Compare with Coursemates</span>
    </router-link>

    <router-link to="/student/ranking" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
      </svg>
      <span class="nav-label">Ranking</span>
    </router-link>    
    
    <router-link to="/student/performance" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
      </svg>
      <span class="nav-label">Performance & Simulator</span>
    </router-link>
      <router-link to="/student/remarks" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
      </svg>
      <span class="nav-label">Remark Requests</span>
    </router-link>      <router-link to="/student/notifications" class="nav-item" active-class="active">
      <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 17h5l-5 5v-5zM21 12H3M21 6H3"></path>
      </svg>
      <span class="nav-label">Notifications</span>
    </router-link>
  </nav>
</template>

<script>
import api from '../../api'

export default {
  name: 'StudentSidebar'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 16px;
  font-family: 'Roboto', sans-serif;
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
</style>
