<template>
  <div class="marks-notifications">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Mark Update Notifications</h1>
        <p class="page-subtitle">Track all mark updates and changes for your courses</p>
      </div>
      <div class="header-actions">
        <button @click="refreshNotifications" class="refresh-btn" :disabled="loading">
          <svg class="refresh-icon" :class="{ spinning: loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          {{ loading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon new">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ newUpdatesCount }}</div>
            <div class="stat-label">New Updates</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon today">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zM4 8h12v8H4V8z"></path>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ todayCount }}</div>
            <div class="stat-label">Today's Updates</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon week">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ weekCount }}</div>
            <div class="stat-label">This Week</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon total">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalCount }}</div>
            <div class="stat-label">Total Updates</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <div class="filter-row">
        <div class="filter-group">
          <label class="filter-label">Course:</label>
          <select v-model="selectedCourse" @change="applyFilters" class="filter-select">
            <option value="">All Courses</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.code }} - {{ course.name }}
            </option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Assessment Type:</label>
          <select v-model="selectedAssessment" @change="applyFilters" class="filter-select">
            <option value="">All Assessments</option>
            <option value="quiz">Quiz</option>
            <option value="assignment">Assignment</option>
            <option value="test">Test</option>
            <option value="final_exam">Final Exam</option>
            <option value="project">Project</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Time Period:</label>
          <select v-model="timePeriod" @change="applyFilters" class="filter-select">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Status:</label>
          <select v-model="statusFilter" @change="applyFilters" class="filter-select">
            <option value="all">All Updates</option>
            <option value="new">New Updates</option>
            <option value="acknowledged">Acknowledged</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="notifications-section">
      <div class="section-header">
        <h3>Mark Update History</h3>
        <div class="section-actions">
          <span class="results-count">{{ filteredNotifications.length }} updates found</span>
          <button 
            v-if="newUpdatesCount > 0" 
            @click="markAllAsAcknowledged" 
            class="acknowledge-all-btn"
          >
            Acknowledge All New ({{ newUpdatesCount }})
          </button>
        </div>
      </div>

      <div class="notifications-list" v-if="filteredNotifications.length > 0">
        <div 
          v-for="notification in paginatedNotifications" 
          :key="notification.id"
          class="notification-card"
          :class="{ 
            'new': !notification.acknowledged,
            'high-impact': notification.impact === 'high'
          }"
        >
          <div class="notification-indicator">
            <div class="status-dot" :class="{ 'new': !notification.acknowledged }"></div>
          </div>
          
          <div class="notification-content">
            <div class="notification-header">
              <div class="notification-title">
                <h4>{{ getNotificationTitle(notification) }}</h4>
                <span class="assessment-type">{{ formatAssessmentType(notification.assessment_type) }}</span>
              </div>
              <div class="notification-meta">
                <span class="course-code">{{ notification.course_code }}</span>
                <span class="timestamp">{{ formatTimestamp(notification.created_at) }}</span>
              </div>
            </div>
            
            <div class="notification-details">
              <div class="student-info">
                <strong>Student:</strong> {{ notification.student_name }} ({{ notification.student_id }})
              </div>
              <div class="mark-changes">
                <div class="mark-change-item">
                  <span class="label">Previous Mark:</span>
                  <span class="old-mark">{{ notification.old_mark || 'Not Set' }}</span>
                </div>
                <div class="change-arrow">→</div>
                <div class="mark-change-item">
                  <span class="label">New Mark:</span>
                  <span class="new-mark">{{ notification.new_mark }}</span>
                </div>
                <div class="mark-difference" v-if="notification.old_mark">
                  <span :class="getDifferenceClass(notification.old_mark, notification.new_mark)">
                    {{ getMarkDifference(notification.old_mark, notification.new_mark) }}
                  </span>
                </div>
              </div>
              <div class="change-reason" v-if="notification.reason">
                <strong>Reason:</strong> {{ notification.reason }}
              </div>
            </div>
          </div>
          
          <div class="notification-actions">
            <button 
              v-if="!notification.acknowledged"
              @click="acknowledgeNotification(notification.id)"
              class="acknowledge-btn"
              title="Mark as acknowledged"
            >
              <svg fill="currentColor" viewBox="0 0 20 20">
                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-content">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"></path>
          </svg>
          <h4>No Mark Updates Found</h4>
          <p>{{ getEmptyStateMessage() }}</p>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="totalPages > 1">
        <button 
          @click="currentPage--" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"></path>
          </svg>
          Previous
        </button>
        
        <div class="page-numbers">
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        </div>
        
        <button 
          @click="currentPage++" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Next
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Toast Messages -->
    <div v-if="message" class="toast-message" :class="messageType">
      {{ message }}
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'LecturerNotifications',
  data() {
    return {
      notifications: [],
      filteredNotifications: [],
      courses: [],
      selectedCourse: '',
      selectedAssessment: '',
      timePeriod: 'all',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      loading: false,
      message: '',
      messageType: 'success'
    }
  },
  computed: {
    newUpdatesCount() {
      return this.notifications.filter(n => !n.acknowledged).length
    },
    todayCount() {
      const today = new Date().toDateString()
      return this.notifications.filter(n => 
        new Date(n.created_at).toDateString() === today
      ).length
    },
    weekCount() {
      const weekAgo = new Date()
      weekAgo.setDate(weekAgo.getDate() - 7)
      return this.notifications.filter(n => 
        new Date(n.created_at) >= weekAgo
      ).length
    },
    totalCount() {
      return this.notifications.length
    },
    paginatedNotifications() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredNotifications.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredNotifications.length / this.itemsPerPage)
    }
  },
  methods: {
    async fetchNotifications() {
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        
        // Fetch mark update notifications
        const notificationsRes = await api.get(`/lecturers/${user.id}/mark-notifications`)
        
        // Fetch courses for filtering
        const coursesRes = await api.get(`/lecturers/${user.id}/courses`)
        
        this.notifications = notificationsRes.data || []
        this.courses = coursesRes.data || []
        this.applyFilters()
      } catch (error) {
        console.error('Error fetching notifications:', error)
        this.showMessage('Failed to load notifications. Please try again.', 'error')
        // Fallback to empty data
        this.notifications = []
        this.courses = []
        this.applyFilters()
      }
      this.loading = false
    },
    
    async refreshNotifications() {
      await this.fetchNotifications()
      this.showMessage('Notifications refreshed successfully!', 'success')
    },
    
    async acknowledgeNotification(id) {
      try {
        await api.put(`/notifications/${id}/acknowledge`)
        const notification = this.notifications.find(n => n.id === id)
        if (notification) {
          notification.acknowledged = true
        }
        this.applyFilters()
        this.showMessage('Notification acknowledged!', 'success')
      } catch (error) {
        console.error('Error acknowledging notification:', error)
        this.showMessage('Failed to acknowledge notification.', 'error')
      }
    },
    
    async markAllAsAcknowledged() {
      try {
        const newNotifications = this.notifications.filter(n => !n.acknowledged)
        await Promise.all(newNotifications.map(n => 
          api.put(`/notifications/${n.id}/acknowledge`)
        ))
        
        this.notifications.forEach(n => n.acknowledged = true)
        this.applyFilters()
        this.showMessage('All notifications acknowledged!', 'success')
      } catch (error) {
        console.error('Error acknowledging all notifications:', error)
        this.showMessage('Failed to acknowledge all notifications.', 'error')
      }
    },
    
    applyFilters() {
      let filtered = [...this.notifications]
      
      // Course filter
      if (this.selectedCourse) {
        filtered = filtered.filter(n => n.course_code === this.selectedCourse)
      }
      
      // Assessment type filter
      if (this.selectedAssessment) {
        filtered = filtered.filter(n => n.assessment_type === this.selectedAssessment)
      }
      
      // Time period filter
      if (this.timePeriod !== 'all') {
        const now = new Date()
        let cutoff
        
        switch (this.timePeriod) {
          case 'today':
            cutoff = new Date(now.toDateString())
            break
          case 'week':
            cutoff = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
            break
          case 'month':
            cutoff = new Date(now.getFullYear(), now.getMonth(), 1)
            break
        }
        
        filtered = filtered.filter(n => new Date(n.created_at) >= cutoff)
      }
      
      // Status filter
      if (this.statusFilter !== 'all') {
        if (this.statusFilter === 'new') {
          filtered = filtered.filter(n => !n.acknowledged)
        } else if (this.statusFilter === 'acknowledged') {
          filtered = filtered.filter(n => n.acknowledged)
        }
      }
      
      // Sort by creation date (newest first)
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      
      this.filteredNotifications = filtered
      this.currentPage = 1
    },
    
    getNotificationTitle(notification) {
      if (notification.is_final_exam) {
        return 'Final Exam - Mark Updated'
      }
      return `${notification.assessment_name || 'Assessment'} - Mark Updated`
    },
    
    formatAssessmentType(type) {
      const types = {
        quiz: 'Quiz',
        assignment: 'Assignment',
        test: 'Test',
        final_exam: 'Final Exam',
        project: 'Project',
        'lab_assignment': 'Lab Assignment',
        'midterm_exam': 'Midterm Exam',
        homework: 'Homework'
      }
      return types[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    },
    
    formatTimestamp(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) return 'Just now'
      if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`
      if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`
      if (diff < 604800000) return `${Math.floor(diff/86400000)}d ago`
      
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    },
    
    getMarkDifference(oldMark, newMark) {
      if (!oldMark && oldMark !== 0) return ''
      const diff = newMark - oldMark
      return diff > 0 ? `+${diff}` : `${diff}`
    },
    
    getDifferenceClass(oldMark, newMark) {
      if (!oldMark && oldMark !== 0) return ''
      const diff = newMark - oldMark
      return diff > 0 ? 'positive' : diff < 0 ? 'negative' : 'neutral'
    },
    
    viewStudentDetails(notification) {
      // Navigate to student details or show modal
      this.$router.push(`/lecturer/students/${notification.student_id}`)
    },
    
    getEmptyStateMessage() {
      if (this.selectedCourse) {
        const course = this.courses.find(c => c.code === this.selectedCourse)
        return `No mark updates found for ${course ? course.code + ' - ' + course.name : this.selectedCourse}`
      }
      if (this.selectedAssessment) return `No ${this.formatAssessmentType(this.selectedAssessment)} updates found`
      if (this.timePeriod !== 'all') return `No updates found for ${this.timePeriod}`
      return 'No mark updates have been recorded yet'
    },
    
    showMessage(text, type = 'success') {
      this.message = text
      this.messageType = type
      setTimeout(() => {
        this.message = ''
      }, 3000)
    }
  },
  
  mounted() {
    this.fetchNotifications()
  }
}
</script>

<style scoped>
.marks-notifications {
  max-width: 1200px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 2px solid #E9ECEF;
}

.header-content h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1D3557;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: #6C757D;
  margin: 0;
  font-size: 16px;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #457B9D;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.refresh-btn:hover:not(:disabled) {
  background: #1D3557;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.refresh-icon {
  width: 18px;
  height: 18px;
}

.refresh-icon.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Stats Section */
.stats-section {
  margin-bottom: 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
  color: white;
}

.stat-icon.new { background: linear-gradient(135deg, #E63946, #F77F00); }
.stat-icon.today { background: linear-gradient(135deg, #457B9D, #1D3557); }
.stat-icon.week { background: linear-gradient(135deg, #F77F00, #FCBF49); }
.stat-icon.total { background: linear-gradient(135deg, #6A994E, #A7C957); }

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1D3557;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6C757D;
  font-weight: 500;
}

/* Filters Section */
.filters-section {
  background: white;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.filter-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.filter-select {
  padding: 10px 12px;
  border: 2px solid #E9ECEF;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: border-color 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #457B9D;
}

/* Notifications Section */
.notifications-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  overflow: hidden;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 16px;
  border-bottom: 1px solid #E9ECEF;
}

.section-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1D3557;
  margin: 0;
}

.section-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.results-count {
  font-size: 14px;
  color: #6C757D;
}

.acknowledge-all-btn {
  background: #6A994E;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
}

.acknowledge-all-btn:hover {
  background: #5A8A42;
}

/* Notification Cards */
.notifications-list {
  padding: 0 24px 24px;
}

.notification-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px;
  border: 1px solid #E9ECEF;
  border-radius: 8px;
  margin-bottom: 16px;
  transition: all 0.2s ease;
}

.notification-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.notification-card.new {
  background: linear-gradient(135deg, #F8F9FF 0%, #FFFFFF 100%);
  border-left: 4px solid #457B9D;
}

.notification-card.high-impact {
  border-left-color: #E63946;
}

.notification-indicator {
  margin-top: 4px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #CED4DA;
}

.status-dot.new {
  background: #457B9D;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.notification-content {
  flex: 1;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.notification-title h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1D3557;
  margin: 0 0 4px 0;
}

.assessment-type {
  background: #F1FAEE;
  color: #457B9D;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.notification-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.course-code {
  background: #1D3557;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.timestamp {
  font-size: 12px;
  color: #6C757D;
}

.notification-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.student-info {
  color: #495057;
  font-size: 14px;
}

.mark-changes {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.mark-change-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.mark-change-item .label {
  font-size: 12px;
  color: #6C757D;
  font-weight: 500;
}

.old-mark, .new-mark {
  font-size: 18px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
}

.old-mark {
  background: #F8D7DA;
  color: #721C24;
}

.new-mark {
  background: #D4EDDA;
  color: #155724;
}

.change-arrow {
  font-size: 20px;
  color: #457B9D;
  font-weight: bold;
}

.mark-difference {
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 14px;
}

.mark-difference.positive {
  background: #D4EDDA;
  color: #155724;
}

.mark-difference.negative {
  background: #F8D7DA;
  color: #721C24;
}

.mark-difference.neutral {
  background: #E2E3E5;
  color: #6C757D;
}

.change-reason {
  font-size: 14px;
  color: #495057;
  font-style: italic;
}

.notification-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.acknowledge-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: #D4EDDA;
  color: #155724;
}

.acknowledge-btn:hover {
  background: #C3E6CB;
}

.acknowledge-btn svg {
  width: 16px;
  height: 16px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: #6C757D;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  opacity: 0.4;
}

.empty-content h4 {
  font-size: 20px;
  margin: 0 0 12px 0;
  color: #495057;
}

.empty-content p {
  font-size: 16px;
  margin: 0;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 24px;
  border-top: 1px solid #E9ECEF;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 2px solid #E9ECEF;
  border-radius: 8px;
  background: white;
  color: #495057;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #457B9D;
  color: #457B9D;
}

.pagination-btn:disabled {
  background: #F8F9FA;
  color: #ADB5BD;
  cursor: not-allowed;
}

.pagination-btn svg {
  width: 16px;
  height: 16px;
}

.page-info {
  font-weight: 600;
  color: #495057;
}

/* Toast Messages */
.toast-message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 24px;
  border-radius: 8px;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  animation: slideInRight 0.3s ease;
}

.toast-message.success {
  background: #D4EDDA;
  color: #155724;
  border: 1px solid #C3E6CB;
}

.toast-message.error {
  background: #F8D7DA;
  color: #721C24;
  border: 1px solid #F5C6CB;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filter-row {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .notification-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .mark-changes {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .change-arrow {
    transform: rotate(90deg);
  }
}
</style>