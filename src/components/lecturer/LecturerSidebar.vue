<template>
  <div class="lecturer-sidebar">
    <div class="sidebar-header">Lecturer Panel</div>
    <ul class="sidebar-list">
      <li><router-link to="/lecturer/dashboard">Dashboard</router-link></li>
      <li><router-link to="/lecturer/courses">Manage Courses</router-link></li>
      <li><router-link to="/lecturer/enrollment">Manage Enrollment</router-link></li>
      <li><router-link to="/lecturer/assessments">Manage Assessments</router-link></li>
      <li><router-link to="/lecturer/final-exam">Enter Final Exam</router-link></li>
      <li><router-link to="/lecturer/bulk-upload">Bulk Upload CSV</router-link></li>
      <li><router-link to="/lecturer/export-csv">Export CSV</router-link></li>
      <li><router-link to="/lecturer/feedback">Feedback/Remarks</router-link></li>
      <li><router-link to="/lecturer/mark-breakdown">Full Mark Breakdown</router-link></li>
      <li><router-link to="/lecturer/analytics">Analytics</router-link></li>
      <li><router-link to="/lecturer/trend-graphs">Trend Graphs</router-link></li>
      <li><router-link to="/lecturer/notifications">Notifications</router-link></li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'LecturerSidebar'
}
</script>

<style scoped>
.lecturer-sidebar {
  width: 220px;
  background: #2c3e50;
  color: #fff;
  min-height: 100vh;
  padding-top: 20px;
  box-shadow: 2px 0 8px rgba(0,0,0,0.04);
}
.sidebar-header {
  font-size: 1.3em;
  font-weight: bold;
  padding: 16px 24px;
  border-bottom: 1px solid #34495e;
  margin-bottom: 10px;
}
.sidebar-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar-list li {
  margin: 0;
}
.sidebar-list a {
  display: block;
  color: #ecf0f1;
  text-decoration: none;
  padding: 12px 24px;
  transition: background 0.2s;
}
.sidebar-list a.router-link-exact-active,
.sidebar-list a:hover {
  background: #34495e;
}
</style>
