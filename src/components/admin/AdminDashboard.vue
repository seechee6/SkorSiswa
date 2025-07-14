<template>
  <div class="admin-dashboard">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>
    </div>

    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="welcome-content">
        <h2>Welcome back, {{ adminName }}!</h2>
        <p class="welcome-text">Here's what's happening with your system today.</p>
      </div>
      <button @click="refreshDashboard" class="refresh-btn">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
          <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
        </svg>
        Refresh
      </button>
    </div>

    <!-- Overview Cards -->
    <div class="dashboard-cards">
      <div class="card overview-card">
        <div class="card-icon users-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
        </div>        <div class="card-content">
          <div class="card-value">{{ stats.totalUsers }}</div>
          <div class="card-label">Total Users</div>
          <!-- <div class="card-sublabel">{{ stats.totalLecturers }} Lecturers</div> -->
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon courses-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
          </svg>
        </div>        <div class="card-content">
          <div class="card-value">{{ stats.activeCourses }}</div>
          <div class="card-label">Active Courses</div>
          <!-- <div class="card-sublabel">Available for enrollment</div> -->
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon enrollments-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ stats.totalEnrollments }}</div>
          <div class="card-label">Total Enrollments</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon lecturers-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ stats.totalLecturers }}</div>
          <div class="card-label">Lecturers</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <router-link to="/admin/users" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
          Manage Users
        </router-link>

        <router-link to="/admin/courses" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
          </svg>
          Manage Courses
        </router-link>

        <router-link to="/admin/enrollments" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Student Enrollments
        </router-link>

        <router-link to="/admin/logs" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
          </svg>
          System Logs
        </router-link>
      </div>
    </div>

    <!-- Main Content Row -->
    <div class="content-row">
      <!-- Recent Users -->
      <div class="card users-section">
        <div class="section-header">
          <h3>Recent Users</h3>
          <router-link to="/admin/users" class="view-all-link">View All</router-link>
        </div>
        
        <div class="users-list" v-if="recentUsers.length > 0">
          <div v-for="user in recentUsers" :key="user.id" class="user-item">
            <div class="user-header">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-status" :class="user.status || 'active'">{{ user.status || 'active' }}</div>
            </div>
            <div class="user-details">
              <span class="user-role">{{ user.role }}</span>
              <span class="user-email">{{ user.email }}</span>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <p>No users found.</p>
        </div>
      </div>

      <!-- System Activity -->
      <div class="card activity-section">
        <h3>System Activity</h3>
        <div class="activity-list" v-if="recentActivity.length > 0">
          <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
            <div class="activity-icon" :class="activity.type">
              <svg v-if="activity.type === 'user'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
              </svg>
              <svg v-else-if="activity.type === 'course'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zm-2.5.5A.5.5 0 0 1 6 4h.5a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zM4.5 7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zM4 9.5A.5.5 0 0 1 4.5 9h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0 2A.5.5 0 0 1 4.5 11h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5z"/>
              </svg>
              <svg v-else fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917z"/>
              </svg>
            </div>            <div class="activity-content">
              <div class="activity-text">{{ activity.description }}</div>
              <div class="activity-user" v-if="activity.user_name">by {{ activity.user_name }}</div>
              <div class="activity-time">{{ formatTime(activity.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <p>No recent activity.</p>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="success" class="floating-message success">
      {{ success }}
    </div>
    <div v-if="error" class="floating-message error">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api';

export default {
  name: 'AdminDashboard',  data() {
    return {
      adminName: '',
      stats: {
        totalUsers: 0,
        activeCourses: 0,
        totalEnrollments: 0,
        totalLecturers: 0
      },
      recentUsers: [],
      recentActivity: [],
      error: '',
      success: '',
      loading: false,
      refreshInterval: null
    }
  },methods: {    async fetchDashboardData() {
      if (this.loading) return;
      
      this.loading = true;
      this.error = '';
      
      try {
        const userStr = localStorage.getItem('user');
        if (!userStr) {
          this.adminName = 'Administrator';
        } else {
          const user = JSON.parse(userStr);
          this.adminName = user.name || user.full_name || 'Administrator';
        }
          // Fetch users data for statistics
        const usersRes = await api.get('/api/admin/users');
        const users = usersRes.data || [];
        
        // Debug: Log users and their roles
        console.log('Users data:', users.map(u => ({ name: u.name, role: u.role, role_name: u.role_name })));
        
        // Fetch courses data
        const coursesRes = await api.get('/courses');
        const courses = coursesRes.data || [];
        
        // Fetch enrollments data
        const enrollmentsRes = await api.get('/enrollments');
        const enrollments = enrollmentsRes.data || [];
          // Calculate statistics
        this.stats = {
          totalUsers: users.length,
          activeCourses: courses.length,
          totalEnrollments: enrollments.length,
          totalLecturers: users.filter(u => {
            const role = (u.role_name || u.role || '').toLowerCase();
            return role === 'lecturer';
          }).length
        };
        
        // Get recent users (last 5)
        this.recentUsers = users
          .sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
          .slice(0, 5)
          .map(user => ({
            id: user.id,
            name: user.full_name || user.name,
            email: user.email,
            role: user.role_name || user.role,
            status: user.status || 'active',
            created_at: user.created_at
          }));
        
        // Get recent activity from system logs if available
        try {
          const activityRes = await api.get('/api/admin/system-logs?limit=8');
          this.recentActivity = (activityRes.data || []).map(activity => ({
            id: activity.id,
            type: this.getActivityType(activity.action),
            description: activity.action,
            user_name: activity.user || activity.user_name,
            created_at: activity.created_at || activity.timestamp
          }));
        } catch (logError) {          // If system logs endpoint not available, create some activity from recent users
          this.recentActivity = this.recentUsers.slice(0, 5).map((user) => ({
            id: `user-${user.id}`,
            type: 'user',
            description: `User ${user.name} registered`,
            user_name: 'System',
            created_at: user.created_at || new Date().toISOString()
          }));
        }
        
        this.error = '';
        
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        this.error = 'Failed to load dashboard data. Please check your connection.';
        // Reset stats to prevent showing stale data
        this.stats = {
          totalUsers: 0,
          activeCourses: 0,
          totalEnrollments: 0,
          totalLecturers: 0
        };
        this.recentUsers = [];
        this.recentActivity = [];
      } finally {
        this.loading = false;
      }
    },
    
    getActivityType(action) {
      if (!action) return 'system';
      const actionLower = action.toLowerCase();
      if (actionLower.includes('login') || actionLower.includes('user')) return 'user';
      if (actionLower.includes('course')) return 'course';
      if (actionLower.includes('enroll')) return 'enrollment';
      if (actionLower.includes('grade') || actionLower.includes('mark')) return 'grade';
      return 'system';
    },
      refreshDashboard() {
      if (this.loading) return;
      
      this.success = 'Refreshing dashboard...';
      this.error = '';
      
      this.fetchDashboardData().then(() => {
        this.success = 'Dashboard refreshed successfully!';
        setTimeout(() => {
          this.success = '';
        }, 3000);
      }).catch(() => {
        this.success = '';
        // Error is already set in fetchDashboardData
      });
    },
    
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diffInSeconds = Math.floor((now - date) / 1000)
      
      if (diffInSeconds < 60) return 'Just now'
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
      return `${Math.floor(diffInSeconds / 86400)}d ago`
    }  },
  
  mounted() {
    this.fetchDashboardData();
    
    // Set up auto-refresh every 5 minutes
    this.refreshInterval = setInterval(() => {
      if (document.visibilityState === 'visible') {
        this.fetchDashboardData();
      }
    }, 300000); // 5 minutes
  },
  
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  padding: 24px;
  background-color: #f8fafc;
  min-height: calc(100vh - 80px);
  position: relative;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(248, 250, 252, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.loading-spinner {
  text-align: center;
  color: #4299e1;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #4299e1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.welcome-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.welcome-content h2 {
  margin: 0;
  color: #1a202c;
  font-size: 1.875rem;
  font-weight: 700;
}

.welcome-text {
  margin: 4px 0 0;
  color: #718096;
  font-size: 1rem;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #4299e1;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.refresh-btn:hover {
  background: #3182ce;
}

.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
}

.overview-card {
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-icon svg {
  width: 24px;
  height: 24px;
  color: white;
}

.users-icon {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.courses-icon {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.enrollments-icon {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.lecturers-icon {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1;
}

.card-label {
  color: #718096;
  font-size: 0.875rem;
  margin-top: 4px;
}

.card-sublabel {
  color: #a0aec0;
  font-size: 0.75rem;
  margin-top: 2px;
  font-style: italic;
}

.quick-actions {
  margin-bottom: 32px;
}

.quick-actions h3 {
  margin: 0 0 16px 0;
  color: #1a202c;
  font-size: 1.25rem;
  font-weight: 600;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  text-decoration: none;
  color: #4a5568;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #edf2f7;
  border-color: #cbd5e0;
  transform: translateY(-1px);
}

.btn-icon {
  width: 20px;
  height: 20px;
}

.content-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-header h3 {
  margin: 0;
  color: #1a202c;
  font-size: 1.125rem;
  font-weight: 600;
}

.view-all-link {
  color: #4299e1;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
}

.view-all-link:hover {
  color: #3182ce;
}

.user-item, .activity-item {
  padding: 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f7fafc;
  margin-bottom: 12px;
}

.user-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.user-name {
  font-weight: 600;
  color: #1a202c;
}

.user-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.user-status.active {
  background: #c6f6d5;
  color: #276749;
}

.user-status.inactive {
  background: #fed7d7;
  color: #c53030;
}

.user-details {
  display: flex;
  justify-content: space-between;
  color: #718096;
  font-size: 0.875rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.activity-icon svg {
  width: 16px;
  height: 16px;
  color: white;
}

.activity-icon.user {
  background: #4299e1;
}

.activity-icon.course {
  background: #48bb78;
}

.activity-content {
  flex: 1;
}

.activity-text {
  color: #1a202c;
  font-weight: 500;
  margin-bottom: 2px;
}

.activity-user {
  color: #4299e1;
  font-size: 0.75rem;
  font-weight: 500;
  margin-bottom: 2px;
}

.activity-time {
  color: #718096;
  font-size: 0.75rem;
}

.empty-state {
  text-align: center;
  padding: 32px;
  color: #718096;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
}

.floating-message {
  position: fixed;
  top: 24px;
  right: 24px;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.floating-message.success {
  background: #c6f6d5;
  color: #276749;
  border: 1px solid #9ae6b4;
}

.floating-message.error {
  background: #fed7d7;
  color: #c53030;
  border: 1px solid #feb2b2;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .admin-dashboard {
    padding: 16px;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .content-row {
    grid-template-columns: 1fr;
  }
}
</style>
