<template>
  <nav class="sidebar">
    <div class="logo">
      <img src="@/assets/logo.png" alt="SkorSiswa Logo" />
      <h1>SkorSiswa</h1>
    </div>
    <div class="nav-links">
      <router-link to="/student/dashboard" class="nav-item" active-class="active">
        <i class="fas fa-home"></i>
        Dashboard
      </router-link>
      <router-link to="/student/marks" class="nav-item" active-class="active">
        <i class="fas fa-chart-bar"></i>
        Mark Breakdown
      </router-link>
      <router-link to="/student/compare" class="nav-item" active-class="active">
        <i class="fas fa-users"></i>
        Compare with Coursemates
      </router-link>
      <router-link to="/student/ranking" class="nav-item" active-class="active">
        <i class="fas fa-trophy"></i>
        Ranking
      </router-link>
      <router-link to="/student/simulator" class="nav-item" active-class="active">
        <i class="fas fa-calculator"></i>
        What-If Simulator
      </router-link>
      <router-link to="/student/remarks" class="nav-item" active-class="active">
        <i class="fas fa-comment-alt"></i>
        Remark Requests
      </router-link>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'StudentSidebar'
}
</script>

<style scoped>
.sidebar {
  width: 250px;
  background: #2c3e50;
  color: white;
  padding: 1rem;
  height: 100vh;
  position: sticky;
  top: 0;
}

.logo {
  display: flex;
  align-items: center;
  padding: 1rem;
  margin-bottom: 2rem;
}

.logo img {
  width: 40px;
  height: 40px;
  margin-right: 1rem;
}

.logo h1 {
  font-size: 1.5rem;
  margin: 0;
  font-weight: 600;
}

.nav-links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #ecf0f1;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.nav-item i {
  margin-right: 0.75rem;
  width: 20px;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.nav-item.active {
  background: #3498db;
  color: white;
}
</style>
