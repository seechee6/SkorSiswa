<template>
  <div class="student-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Student Dashboard</h2>
        <p class="welcome-text">Welcome back, {{ studentName }}!</p>
      </div>
      <div class="header-actions">
        <button @click="fetchStudentData" class="refresh-btn">
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
        <div class="card-icon cgpa-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-2.727 1.17 1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ cgpa }}</div>
          <div class="card-label">Current CGPA</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon courses-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ currentCourses.length }}</div>
          <div class="card-label">Current Courses</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon ranking-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ ranking }}</div>
          <div class="card-label">Current Ranking</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon progress-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ semesterProgress }}%</div>
          <div class="card-label">Semester Progress</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <router-link to="/student/marks" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
          </svg>
          View Marks
        </router-link>

        <router-link to="/student/compare" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
          </svg>
          Compare with Coursemates
        </router-link>

        <router-link to="/student/simulator" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          What-If Simulator
        </router-link>

        <router-link to="/student/performance" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
          </svg>
          Performance Trends
        </router-link>

        <router-link to="/student/remarks" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"></path>
          </svg>
          Remark Requests
        </router-link>
      </div>
    </div>

    <!-- Main Content Row -->
    <div class="content-row">
      <!-- Current Courses -->
      <div class="card courses-section">
        <div class="section-header">
          <h3>Current Courses</h3>
          <router-link to="/student/marks" class="view-all-link">View All</router-link>
        </div>
        
        <div class="courses-list" v-if="currentCourses.length > 0">
          <div v-for="course in currentCourses" :key="course.id" class="course-item">
            <div class="course-header">
              <div class="course-code">{{ course.code }}</div>
              <div class="course-grade" :class="getGradeClass(course.grade)">{{ course.grade }}</div>
            </div>
            <div class="course-title">{{ course.name }}</div>
            <div class="course-progress">
              <div class="progress-label">
                <span>Progress</span>
                <span>{{ course.progress }}%</span>
              </div>
              <div class="progress-container">
                <div class="progress-bar" :style="{ width: course.progress + '%' }"></div>
              </div>
            </div>
            <div class="course-details">
              <router-link :to="'/student/marks?course=' + course.id" class="view-details-link">
                View Details
              </router-link>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <p>No courses available.</p>
        </div>
      </div>

      <!-- Recent Notifications -->
      <div class="card notification-section">
        <h3>Recent Notifications</h3>
        <div class="notification-list" v-if="notifications.length > 0">
          <div v-for="notification in notifications" :key="notification.id" class="notification-item">
            <div class="notification-icon" :class="notification.type">
              <svg v-if="notification.type === 'grade'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M9.669.864L8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193l.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
              </svg>
              <svg v-else-if="notification.type === 'remark'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z"/>
              </svg>
              <svg v-else-if="notification.type === 'assessment'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5h3Z"/>
                <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5v-12Z"/>
                <path d="M10.854 7.854a.5.5 0 0 0-.708-.708L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3Z"/>
              </svg>
              <svg v-else-if="notification.type === 'deadline'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
              </svg>
              <svg v-else fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
              </svg>
            </div>
            <div class="notification-content">
              <div class="notification-text">{{ notification.message }}</div>
              <div class="notification-time">{{ notification.time }}</div>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <p>No recent notifications.</p>
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
import api from '../../api'

export default {
  name: 'StudentDashboard',
  data() {
    return {
      studentName: '',
      cgpa: '0.00',
      ranking: '0 / 0',
      semesterProgress: 0,
      currentCourses: [],
      notifications: [],
      loading: true,
      error: '',
      success: ''
    }
  },
  methods: {
    getGradeClass(grade) {
      if (grade.startsWith('A')) return 'grade-a'
      if (grade.startsWith('B')) return 'grade-b'
      if (grade.startsWith('C')) return 'grade-c'
      if (grade.startsWith('D')) return 'grade-d'
      return 'grade-f'
    },
    refreshDashboard() {
      this.success = 'Dashboard refreshed!'
      this.fetchStudentData()
      setTimeout(() => {
        this.success = ''
      }, 3000)
    },
    async fetchStudentData() {
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        if (!user) {
          this.$router.push('/login')
          return
        }
        
        // Set student name from local storage
        this.studentName = user.name || 'Student'
        
        const response = await api.student.getDashboard(user.id)
        const data = response.data
        
        this.cgpa = data.cgpa
        this.ranking = data.ranking
        this.semesterProgress = data.semesterProgress
          // Process courses data
        this.currentCourses = data.currentCourses.map(course => {
          return {
            id: course.id,
            code: course.code,
            name: course.name,
            grade: course.grade || this.calculateGrade(course.current_percentage || 0),
            progress: course.progress || this.calculateProgress(course)
          }
        })
          // Process notifications
        this.notifications = data.notifications.map(notification => {
          // Determine notification type based on message content
          let type = 'general'
          if (notification.message.includes('grade') || notification.message.includes('Grade')) type = 'grade'
          else if (notification.message.includes('remark') || notification.message.includes('Remark')) type = 'remark'
          else if (notification.message.includes('assignment') || notification.message.includes('exam') || 
                  notification.message.includes('Assignment') || notification.message.includes('Exam')) type = 'assessment'
          else if (notification.message.includes('deadline') || notification.message.includes('due') ||
                  notification.message.includes('Deadline')) type = 'deadline'
          
          return {
            id: notification.id,
            type: type,
            message: notification.message,
            time: this.formatTime(notification.created_at)
          }
        })
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
        this.error = 'Failed to load dashboard data. Please try again.'
        setTimeout(() => {
          this.error = ''
        }, 3000)
      } finally {
        this.loading = false
      }
    },
    calculateGrade(marks) {
      if (marks >= 90) return 'A+'
      if (marks >= 85) return 'A'
      if (marks >= 80) return 'A-'
      if (marks >= 75) return 'B+'
      if (marks >= 70) return 'B'
      if (marks >= 65) return 'B-'
      if (marks >= 60) return 'C+'
      if (marks >= 55) return 'C'
      if (marks >= 50) return 'C-'
      if (marks >= 45) return 'D'
      if (marks >= 40) return 'E'
      return 'F'
    },    calculateProgress(course) { // eslint-disable-line no-unused-vars
      // In a real app, this would be calculated based on dates and completed components
      // For now, we'll use a random value between 60-95%
      return Math.floor(Math.random() * 35) + 60
    },
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) return 'Just now'
      if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`
      if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`
      
      return date.toLocaleDateString()
    }
  },
  mounted() {
    this.fetchStudentData()
  }
}
</script>

<style scoped>
.student-dashboard {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e9ecef;
}

.header-content h2 {
  margin: 0;
  color: #1D3557;
  font-size: 28px;
}

.welcome-text {
  margin: 4px 0 0 0;
  color: #6c757d;
  font-size: 16px;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #F1FAEE;
  border: none;
  border-radius: 6px;
  color: #1D3557;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background: #457B9D;
  color: white;
}

.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  border: 1px solid #f1f3f4;
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
  flex-shrink: 0;
}

.card-icon svg {
  width: 24px;
  height: 24px;
  color: white;
}

.cgpa-icon { background: #457B9D; }
.courses-icon { background: #27ae60; }
.ranking-icon { background: #f39c12; }
.progress-icon { background: #e74c3c; }

.card-content {
  flex: 1;
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: #1D3557;
  margin-bottom: 4px;
}

.card-label {
  color: #6c757d;
  font-size: 14px;
  font-weight: 500;
}

.quick-actions {
  margin-bottom: 24px;
}

.quick-actions h3 {
  margin: 0 0 20px 0;
  color: #1D3557;
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
  background: #F1FAEE;
  color: #1D3557;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #457B9D;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.3);
}

.btn-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.content-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  margin-bottom: 32px;
}

@media (max-width: 768px) {
  .content-row {
    grid-template-columns: 1fr;
  }
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  margin: 0;
  color: #1D3557;
}

.view-all-link {
  color: #457B9D;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

.courses-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.course-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #457B9D;
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-code {
  font-weight: 700;
  color: #1D3557;
}

.course-title {
  font-size: 16px;
  font-weight: 500;
  color: #1D3557;
}

.course-grade {
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
}

.course-progress {
  margin: 8px 0;
}

.progress-label {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #7f8c8d;
  margin-bottom: 0.25rem;
}

.progress-container {
  background: #ecf0f1;
  height: 6px;
  border-radius: 3px;
  margin: 0.5rem 0;
  overflow: hidden;
}

.progress-bar {
  background: #3498db;
  height: 100%;
  border-radius: 3px;
}

.course-details {
  display: flex;
  justify-content: flex-end;
}

.view-details-link {
  color: #457B9D;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-details-link:hover {
  text-decoration: underline;
}

.grade-a {
  background: #e3f9e5;
  color: #27ae60;
}

.grade-b {
  background: #e3f1fa;
  color: #2980b9;
}

.grade-c {
  background: #fcf3e3;
  color: #f39c12;
}

.grade-d {
  background: #fae3e3;
  color: #e74c3c;
}

.grade-f {
  background: #e74c3c;
  color: white;
}

.notification-section h3 {
  margin-bottom: 20px;
  color: #1D3557;
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.notification-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.notification-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  flex-shrink: 0;
}

.notification-icon.grade {
  background: #e3f9e5;
}

.notification-icon.remark {
  background: #e3f1fa;
}

.notification-icon.assessment {
  background: #fcf3e3;
}

.notification-icon.deadline {
  background: #fae3e3;
}

.notification-icon.general {
  background: #f5f5f5;
}

.notification-icon svg {
  width: 16px;
  height: 16px;
  color: #1D3557;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-text {
  font-size: 14px;
  font-weight: 500;
  color: #1D3557;
  margin-bottom: 4px;
  word-wrap: break-word;
}

.notification-time {
  font-size: 12px;
  color: #6c757d;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 24px;
  background: #f8f9fa;
  border-radius: 8px;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: #6c757d;
}

.floating-message {
  position: fixed;
  bottom: 16px;
  right: 16px;
  padding: 12px 16px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  z-index: 1000;
}

.floating-message.success {
  background: #27ae60;
}

.floating-message.error {
  background: #e74c3c;
}
</style>
