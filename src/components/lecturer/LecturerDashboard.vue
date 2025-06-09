<template>
  <div>
    <h2>Lecturer Dashboard</h2>
    <div class="dashboard-cards">
      <!-- You can add summary cards or quick links here for aesthetics -->
    </div>
    <div class="dashboard-content">
      <!-- Optionally, show a welcome message or analytics summary -->
      <p>Welcome to your dashboard. Use the sidebar to manage your courses, students, marks, and more.</p>
    </div>
  </div>
</template>
<script>
export default {
  name: 'LecturerDashboard'
}
</script>
<style scoped>
.dashboard-cards {
  display: flex;
  gap: 24px;
  margin-bottom: 32px;
}
.dashboard-content {
  background: #fff;
  border-radius: 8px;
  padding: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
</style>
