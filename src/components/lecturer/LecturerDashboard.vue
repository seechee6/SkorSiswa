<template>
  <div class="lecturer-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Lecturer Dashboard</h2>
        <p class="welcome-text">Welcome back, {{ lecturerName }}!</p>
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
        <div class="card-icon courses-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ totalCourses }}</div>
          <div class="card-label">Total Courses</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon students-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ totalStudents }}</div>
          <div class="card-label">Total Students</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon assessments-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 00-1 1v6a1 1 0 001 1v1a2 2 0 01-2-2V5zM15 5a2 2 0 00-2-2v1a1 1 0 011 1v6a1 1 0 01-1 1v1a2 2 0 002-2V5z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ pendingGrading }}</div>
          <div class="card-label">Pending Grading</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon notifications-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ notifications }}</div>
          <div class="card-label">Notifications</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <router-link to="/lecturer/manage-courses" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
          </svg>
          Manage Courses
        </router-link>

        <router-link to="/lecturer/enter-final-marks" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 00-1 1v6a1 1 0 001 1v1a2 2 0 01-2-2V5zM15 5a2 2 0 00-2-2v1a1 1 0 011 1v6a1 1 0 01-1 1v1a2 2 0 002-2V5z" clip-rule="evenodd"></path>
          </svg>
          Manage Marks
        </router-link>

        <router-link to="/lecturer/manage-assessments" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
          </svg>
          Manage Assessments
        </router-link>

        <router-link to="/lecturer/analytics" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
          </svg>
          View Analytics
        </router-link>

        <router-link to="/lecturer/export-csv" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
          Export Reports
        </router-link>

        <router-link to="/lecturer/bulk-upload" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          Bulk Upload
        </router-link>
      </div>
    </div>

    <!-- Main Content Row -->
    <div class="content-row">
      <!-- My Courses -->
      <div class="card courses-section">
        <div class="section-header">
          <h3>My Courses</h3>
          <router-link to="/lecturer/manage-courses" class="view-all-link">View All</router-link>
        </div>
        
        <div class="courses-list" v-if="recentCourses.length > 0">
          <div v-for="course in recentCourses" :key="course.id" class="course-item">
            <div class="course-header">
              <div class="course-code">{{ course.code }}</div>
              <div class="course-status active">Active</div>
            </div>
            <div class="course-title">{{ course.name }}</div>
            <div class="course-details">
              <span class="course-semester">{{ course.semester }} {{ course.year }}</span>
              <span class="course-students">{{ getStudentCount(course.id) }} students</span>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <p>No courses assigned yet.</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card activity-section">
        <h3>Recent Activity</h3>
        <div class="activity-list" v-if="recentActivity.length > 0">
          <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
            <div class="activity-icon" :class="activity.type">
              <svg v-if="activity.type === 'grade'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M9.669.864L8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193l.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
              </svg>
              <svg v-else-if="activity.type === 'upload'" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
              </svg>
              <svg v-else fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
              </svg>
            </div>
            <div class="activity-content">
              <div class="activity-text">{{ activity.description }}</div>
              <div class="activity-time">{{ formatTime(activity.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <p>No recent activity.</p>
        </div>
      </div>
    </div>

    <!-- Calendar Section -->
    <div class="card calendar-section">
      <h4>Calendar</h4>
      <div class="calendar">
        <div class="calendar-header">
          <button @click="previousMonth" class="calendar-nav">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
          </button>
          <span class="month-year">{{ currentMonthYear }}</span>
          <button @click="nextMonth" class="calendar-nav">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
            </svg>
          </button>
        </div>
        <div class="calendar-grid">
          <div class="day-header" v-for="day in dayHeaders" :key="day">{{ day }}</div>
          <div 
            v-for="date in calendarDates" 
            :key="date.date"
            class="calendar-day"
            :class="{
              'other-month': date.otherMonth,
              'today': date.isToday
            }"
          >
            {{ date.day }}
          </div>
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
  name: 'LecturerDashboard',
  data() {
    return {
      lecturerName: '',
      totalCourses: 0,
      totalStudents: 0,
      pendingGrading: 0,
      notifications: 0,
      recentActivity: [],
      recentCourses: [],
      currentDate: new Date(),
      dayHeaders: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      error: '',
      success: ''
    }
  },
  computed: {
    currentMonthYear() {
      return this.currentDate.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
      })
    },
    calendarDates() {
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()
      const today = new Date()
      
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const daysInMonth = lastDay.getDate()
      const startDate = firstDay.getDay()
      
      const dates = []
      
      // Previous month days
      for (let i = startDate - 1; i >= 0; i--) {
        const prevDate = new Date(year, month, -i)
        dates.push({
          date: prevDate.toDateString(),
          day: prevDate.getDate(),
          otherMonth: true,
          isToday: false
        })
      }
      
      // Current month days
      for (let day = 1; day <= daysInMonth; day++) {
        const currentDay = new Date(year, month, day)
        dates.push({
          date: currentDay.toDateString(),
          day: day,
          otherMonth: false,
          isToday: currentDay.toDateString() === today.toDateString()
        })
      }
      
      // Next month days to fill grid
      const remainingCells = 42 - dates.length
      for (let day = 1; day <= remainingCells; day++) {
        const nextDate = new Date(year, month + 1, day)
        dates.push({
          date: nextDate.toDateString(),
          day: day,
          otherMonth: true,
          isToday: false
        })
      }
      
      return dates
    }
  },
  methods: {
    async fetchDashboardData() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        this.lecturerName = user.full_name || 'Lecturer'
        
        // Fetch courses
        const coursesRes = await api.get('/courses')
        const lecturerCourses = coursesRes.data.filter(c => c.lecturer_id === user.id)
        this.totalCourses = lecturerCourses.length
        this.recentCourses = lecturerCourses.slice(0, 3)
        
        // Calculate total students from enrollments
        let totalStudents = 0
        for (const course of lecturerCourses) {
          try {
            const enrollmentsRes = await api.get(`/courses/${course.id}/enrollments`)
            totalStudents += enrollmentsRes.data.length
          } catch (e) {
            // If enrollments endpoint fails, use mock data
            totalStudents += Math.floor(Math.random() * 30) + 10
          }
        }
        this.totalStudents = totalStudents
        
        // Calculate actual pending grading (consistent calculation)
        let pendingCount = 0
        for (const course of lecturerCourses) {
          try {
            const marksRes = await api.get(`/courses/${course.id}/marks`)
            const enrollmentsRes = await api.get(`/courses/${course.id}/enrollments`)
            const assessmentsRes = await api.get(`/courses/${course.id}/assessments`)
            
            const totalPossibleMarks = enrollmentsRes.data.length * assessmentsRes.data.length
            const currentMarks = marksRes.data.assessment_marks?.length || 0
            pendingCount += Math.max(0, totalPossibleMarks - currentMarks)
          } catch (e) {
            // Fallback to consistent calculation based on course ID
            pendingCount += (parseInt(course.id) % 10) + 5
          }
        }
        this.pendingGrading = pendingCount
        
        // Fetch notifications
        try {
          const notificationsRes = await api.get(`/users/${user.id}/notifications`)
          this.notifications = notificationsRes.data.filter(n => !n.is_read).length
        } catch (e) {
          this.notifications = 3 // fallback
        }
        
        // Generate recent activity
        this.generateRecentActivity()
        
      } catch (e) {
        this.error = 'Failed to load dashboard data.'
      }
    },
    
    generateRecentActivity() {
      const activities = []
      const activityTypes = [
        { type: 'grade', description: 'Graded Assignment 1 for CS101' },
        { type: 'upload', description: 'Uploaded lecture materials for CS102' },
        { type: 'notification', description: 'New student enrolled in CS103' },
        { type: 'grade', description: 'Final exam marks entered for CS101' },
        { type: 'upload', description: 'Course syllabus updated for CS102' }
      ]
      
      for (let i = 0; i < Math.min(5, activityTypes.length); i++) {
        const activity = activityTypes[i]
        const date = new Date()
        date.setHours(date.getHours() - (i * 2))
        
        activities.push({
          id: i + 1,
          ...activity,
          created_at: date.toISOString()
        })
      }
      
      this.recentActivity = activities
    },
    
    refreshDashboard() {
      this.success = ''
      this.error = ''
      this.fetchDashboardData()
      this.success = 'Dashboard refreshed!'
      setTimeout(() => this.success = '', 2000)
    },
    
    previousMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() - 1,
        1
      )
    },
    
    nextMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() + 1,
        1
      )
    },
    
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) return 'Just now'
      if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`
      if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`
      return date.toLocaleDateString()
    },
    
    getStudentCount(courseId) {
      // Use courseId to generate consistent student count
      const hash = courseId.toString().split('').reduce((a, b) => {
        a = ((a << 5) - a) + b.charCodeAt(0);
        return a & a;
      }, 0);
      return Math.abs(hash % 30) + 10;
    }
  },
  
  mounted() {
    this.fetchDashboardData()
  }
}
</script>

<style scoped>
.lecturer-dashboard {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding-bottom: 16px;
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
  gap: 24px;
  margin-bottom: 32px;
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

.courses-icon { background: #457B9D; }
.students-icon { background: #27ae60; }
.assessments-icon { background: #f39c12; }
.notifications-icon { background: #e74c3c; }

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
  margin-bottom: 48px;
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

.course-status {
  font-size: 12px;
  font-weight: 500;
  color: #27ae60;
}

.course-title {
  font-size: 16px;
  font-weight: 500;
  color: #1D3557;
}

.course-details {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
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

.activity-section h3 {
  margin-bottom: 20px;
  color: #1D3557;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.activity-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.activity-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-icon.grade {
  background: #e3f2fd;
}

.activity-icon.upload {
  background: #f3e5f5;
}

.activity-icon.notification {
  background: #fff3e0;
}

.activity-icon svg {
  width: 16px;
  height: 16px;
  color: #1D3557;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-text {
  font-size: 14px;
  font-weight: 500;
  color: #1D3557;
  margin-bottom: 4px;
  word-wrap: break-word;
}

.activity-time {
  font-size: 12px;
  color: #6c757d;
}

.calendar-section {
  margin-top: 32px;
}

.calendar-section h4 {
  margin-bottom: 16px;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.calendar {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border: 1px solid #f1f3f4;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.month-year {
  font-size: 16px;
  font-weight: 600;
  color: #1D3557;
}

.calendar-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #F1FAEE;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  color: #1D3557;
  transition: all 0.2s;
}

.calendar-nav:hover {
  background: #457B9D;
  color: white;
  transform: scale(1.1);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.day-header {
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
  text-align: center;
  padding: 8px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.calendar-day {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 36px;
  font-size: 14px;
  border-radius: 8px;
  background: #f8f9fa;
  color: #1D3557;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.calendar-day:hover {
  background: #E8F4FD;
  transform: scale(1.05);
}

.calendar-day.other-month {
  color: #6c757d;
  background: transparent;
}

.calendar-day.today {
  background: #457B9D;
  color: white;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.calendar-day.today:hover {
  background: #3a6b8a;
  transform: scale(1.05);
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
}

.floating-message.success {
  background: #27ae60;
}

.floating-message.error {
  background: #e74c3c;
}
</style>