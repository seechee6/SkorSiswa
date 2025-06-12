<template>
  <div class="dashboard-container">
    <h2 class="dashboard-title">Welcome, {{ studentName }}</h2>
    
    <div class="dashboard-stats">
      <div class="stat-card">
        <h3>Current CGPA</h3>
        <div class="stat-value">{{ cgpa || 'N/A' }}</div>
      </div>
      <div class="stat-card">
        <h3>Courses Enrolled</h3>
        <div class="stat-value">{{ coursesCount }}</div>
      </div>
      <div class="stat-card">
        <h3>Class Ranking</h3>
        <div class="stat-value">{{ ranking ? `${ranking}/${totalStudents}` : 'N/A' }}</div>
      </div>
    </div>

    <div class="dashboard-cards">
      <div v-for="course in courses" :key="course.id" class="course-card">
        <div class="course-header">
          <h3>{{ course.code }} - {{ course.name }}</h3>
          <span :class="['grade-badge', getGradeClass(course.currentGrade)]">
            {{ course.currentGrade || 'IP' }}
          </span>
        </div>
        <div class="progress-section">
          <div class="progress-header">
            <span>Overall Progress</span>
            <span>{{ course.overallProgress }}%</span>
          </div>
          <div class="progress-bar">
            <div :style="{ width: course.overallProgress + '%' }" class="progress-fill"></div>
          </div>
        </div>
        <div class="course-footer">
          <router-link :to="'/student/marks?course=' + course.id" class="view-details">
            View Details
          </router-link>
        </div>
      </div>
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
      cgpa: null,
      coursesCount: 0,
      ranking: null,
      totalStudents: 0,
      courses: []
    }
  },
  methods: {
    getGradeClass(grade) {
      const gradeClasses = {
        'A': 'excellent',
        'B': 'good',
        'C': 'average',
        'D': 'poor',
        'F': 'fail'
      }
      return gradeClasses[grade] || 'in-progress'
    },
    async fetchDashboardData() {
      try {
        // Get user data from localStorage
        const user = JSON.parse(localStorage.getItem('user'))
        this.studentName = user.name

        // Fetch enrolled courses
        const coursesRes = await api.get(`/users/${user.id}/courses`)
        this.courses = coursesRes.data
        this.coursesCount = this.courses.length

        // Fetch CGPA and ranking
        const statsRes = await api.get(`/users/${user.id}/stats`)
        this.cgpa = statsRes.data.cgpa
        this.ranking = statsRes.data.ranking
        this.totalStudents = statsRes.data.totalStudents
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
      }
    }
  },
  mounted() {
    this.fetchDashboardData()
  }
}
</script>

<style scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-title {
  font-size: 32px;
  font-weight: 300;
  margin-bottom: 32px;
  color: #2c3e50;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.stat-card h3 {
  font-size: 14px;
  color: #7f8c8d;
  margin: 0 0 12px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 28px;
  font-weight: 500;
  color: #2c3e50;
}

.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.course-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.course-header h3 {
  margin: 0;
  font-size: 16px;
  color: #2c3e50;
}

.grade-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

.grade-badge.excellent { background: #e3fcef; color: #27ae60; }
.grade-badge.good { background: #e3f2fc; color: #3498db; }
.grade-badge.average { background: #ffeeba; color: #f1c40f; }
.grade-badge.poor { background: #ffe3e3; color: #e74c3c; }
.grade-badge.fail { background: #fee; color: #c0392b; }
.grade-badge.in-progress { background: #f0f3f6; color: #7f8c8d; }

.progress-section {
  margin-bottom: 16px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 8px;
}

.progress-bar {
  height: 6px;
  background: #ecf0f1;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #3498db;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.course-footer {
  display: flex;
  justify-content: flex-end;
}

.view-details {
  color: #3498db;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-details:hover {
  text-decoration: underline;
}
</style>
