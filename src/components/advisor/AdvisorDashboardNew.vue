<template>
  <div class="advisor-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Advisor Dashboard</h2>
        <p class="welcome-text">Welcome back, {{ advisorName }}!</p>
      </div>
      <div class="header-actions">
        <button @click="refreshDashboard" class="refresh-btn">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
            <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="dashboard-cards">
      <div class="card overview-card">
        <div class="card-icon advisees-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ totalAdvisees }}</div>
          <div class="card-label">Total Advisees</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon at-risk-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ atRiskStudents }}</div>
          <div class="card-label">At-Risk Students</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon meetings-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ meetingsThisWeek }}</div>
          <div class="card-label">Meetings This Week</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon performance-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ averageGPA }}</div>
          <div class="card-label">Average GPA</div>
        </div>
      </div>
    </div>

    <!-- At-Risk Students Alert -->
    <div v-if="atRiskStudents > 0" class="alert-section">
      <div class="alert alert-warning">
        <div class="alert-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="alert-content">
          <h4>Attention Required</h4>
          <p>You have {{ atRiskStudents }} students who may need immediate attention. Consider scheduling consultation meetings.</p>
          <router-link to="/advisor/at-risk-students" class="alert-link">View At-Risk Students</router-link>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-grid">
        <router-link to="/advisor/advisee-list" class="action-card">
          <div class="action-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
            </svg>
          </div>
          <div class="action-content">
            <h4>View Advisees</h4>
            <p>Manage your advisee list</p>
          </div>
        </router-link>

        <router-link to="/advisor/meeting-notes" class="action-card">
          <div class="action-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 00-1 1v6a1 1 0 001 1v1a2 2 0 01-2-2V5zM15 5a2 2 0 00-2-2v1a1 1 0 011 1v6a1 1 0 01-1 1v1a2 2 0 002-2V5z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="action-content">
            <h4>Meeting Notes</h4>
            <p>Record consultation notes</p>
          </div>
        </router-link>

        <router-link to="/advisor/performance-analytics" class="action-card">
          <div class="action-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg>
          </div>
          <div class="action-content">
            <h4>Analytics</h4>
            <p>View performance trends</p>
          </div>
        </router-link>

        <router-link to="/advisor/generate-reports" class="action-card">
          <div class="action-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="action-content">
            <h4>Generate Reports</h4>
            <p>Export advisee reports</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="recent-activities">
      <h3>Recent Activities</h3>
      <div class="activity-list">
        <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
          <div class="activity-icon">
            <svg v-if="activity.type === 'meeting'" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <svg v-else-if="activity.type === 'report'" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
            <svg v-else fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="activity-content">
            <p class="activity-title">{{ activity.title }}</p>
            <p class="activity-description">{{ activity.description }}</p>
            <span class="activity-time">{{ formatTime(activity.time) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'AdvisorDashboard',
  setup() {
    const advisorName = ref('Dr. John Smith')
    const totalAdvisees = ref(0)
    const atRiskStudents = ref(0)
    const meetingsThisWeek = ref(0)
    const averageGPA = ref('0.00')
    const recentActivities = ref([])

    const refreshDashboard = async () => {
      try {
        // TODO: Implement API calls to fetch dashboard data
        await fetchDashboardData()
      } catch (error) {
        console.error('Error refreshing dashboard:', error)
      }
    }

    const fetchDashboardData = async () => {
      try {
        // TODO: Replace with actual API calls
        // Placeholder data
        totalAdvisees.value = 25
        atRiskStudents.value = 3
        meetingsThisWeek.value = 8
        averageGPA.value = '3.15'
        
        recentActivities.value = [
          {
            id: 1,
            type: 'meeting',
            title: 'Meeting with Sarah Wilson',
            description: 'Discussed academic progress and course selection',
            time: new Date(Date.now() - 2 * 60 * 60 * 1000) // 2 hours ago
          },
          {
            id: 2,
            type: 'report',
            title: 'Generated semester report',
            description: 'Created performance report for 15 advisees',
            time: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000) // 1 day ago
          },
          {
            id: 3,
            type: 'alert',
            title: 'At-risk student identified',
            description: 'John Doe\'s GPA dropped below 2.0',
            time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000) // 2 days ago
          }
        ]
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
      }
    }

    const formatTime = (time) => {
      const now = new Date()
      const diff = now - time
      const hours = Math.floor(diff / (1000 * 60 * 60))
      const days = Math.floor(hours / 24)
      
      if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`
      } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`
      } else {
        return 'Just now'
      }
    }

    onMounted(() => {
      fetchDashboardData()
    })

    return {
      advisorName,
      totalAdvisees,
      atRiskStudents,
      meetingsThisWeek,
      averageGPA,
      recentActivities,
      refreshDashboard,
      formatTime
    }
  }
}
</script>

<style scoped>
.advisor-dashboard {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.header-content h2 {
  color: #1f2937;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.welcome-text {
  color: #6b7280;
  font-size: 1.1rem;
  margin: 0;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.refresh-btn:hover {
  background: #2563eb;
}

.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.overview-card {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.card-icon {
  padding: 1rem;
  border-radius: 0.75rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.advisees-icon {
  background: #dbeafe;
  color: #3b82f6;
}

.at-risk-icon {
  background: #fef3c7;
  color: #d97706;
}

.meetings-icon {
  background: #d1fae5;
  color: #059669;
}

.performance-icon {
  background: #e0e7ff;
  color: #7c3aed;
}

.card-icon svg {
  width: 24px;
  height: 24px;
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.card-label {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
}

.alert-section {
  margin-bottom: 2rem;
}

.alert {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 0.5rem;
  border: 1px solid;
}

.alert-warning {
  background: #fffbeb;
  border-color: #fbbf24;
  color: #92400e;
}

.alert-icon svg {
  width: 20px;
  height: 20px;
  margin-top: 0.125rem;
}

.alert-content h4 {
  margin: 0 0 0.5rem 0;
  font-weight: 600;
}

.alert-content p {
  margin: 0 0 0.5rem 0;
}

.alert-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.alert-link:hover {
  text-decoration: underline;
}

.quick-actions,
.recent-activities {
  margin-bottom: 2rem;
}

.quick-actions h3,
.recent-activities h3 {
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s;
}

.action-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.action-icon {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-icon svg {
  width: 20px;
  height: 20px;
}

.action-content h4 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
  color: #1f2937;
}

.action-content p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

.activity-list {
  space-y: 1rem;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.activity-icon {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.5rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
}

.activity-icon svg {
  width: 16px;
  height: 16px;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.activity-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0 0 0.5rem 0;
}

.activity-time {
  color: #9ca3af;
  font-size: 0.75rem;
}

@media (max-width: 768px) {
  .advisor-dashboard {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .action-grid {
    grid-template-columns: 1fr;
  }
}
</style>
