<template>
  <div class="mark-breakdown">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Class Ranking</h2>
        <p class="subtitle">View your ranking and position among coursemates</p>
      </div>
      <div class="header-actions">
        <div class="course-select">
          <select v-model="selectedCourseId" @change="fetchRanking">
            <option value="">Select a Course</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.code }} - {{ course.name }}
            </option>
          </select>
          <button @click="fetchCourses" class="reload-btn" title="Reload courses">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
              <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>    
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading ranking data...</p>
    </div>

    <div v-else-if="!selectedCourseId" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <h3>No Course Selected</h3>
      <p>Please select a course from the dropdown to view ranking data</p>
    </div>

    <div v-else-if="selectedCourseId && !rankData" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
      </div>
      <h3>No Ranking Data Available</h3>
      <p>There is no ranking data available for this course yet</p>
    </div>    <div v-else-if="selectedCourseId && rankData" class="mark-content">
      <!-- Summary Cards -->
      <div class="dashboard-cards">
        <div class="card overview-card">
          <div class="card-icon rank-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ rankData.yourRank || 'N/A' }}</div>
            <div class="card-label">Your Rank</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon class-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ rankData.totalStudents || 0 }}</div>
            <div class="card-label">Total Students</div>
          </div>
        </div>
      </div>

      <!-- Top Students Table -->
      <div class="card table-card">        <div class="card-header">
          <h3>Class Ranking</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="mark-table">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Student</th>
                  <th>Score</th>
                  <th>Grade</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(student, index) in (rankData.topStudents || [])" :key="index" 
                    :class="{'highlight-row': student.isYou}">
                  <td>{{ student.rank || 'N/A' }}</td>                  <td>
                    <span v-if="student.isYou" class="your-name">You</span>
                    <span v-else>{{ student.name || 'Unknown' }}</span>
                  </td>
                  <td class="score-cell">{{ student.score ? student.score.toFixed(1) : '0.0' }}%</td>
                  <td>
                    <span class="grade-badge" :class="getGradeClass(student.grade)">
                      {{ student.grade || 'N/A' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>    <div v-if="error" class="error-message">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <h3>Something went wrong</h3>
      <p>{{ error }}</p>
      <button class="retry-button" @click="fetchCourses">
        Try Again
      </button>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default /* eslint-disable vue/multi-word-component-names */ {
  name: 'Ranking',  data() {
    return {
      courses: [],
      selectedCourseId: '',
      rankData: null,
      error: '',
      loading: false
    }
  },
  methods: {    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/student/courses/${user.id}`)
        this.courses = response.data.courses
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },    async fetchRanking() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      this.error = ''
      
      try {        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/student/ranking/${user.id}/${this.selectedCourseId}`)
        this.rankData = response.data
      } catch (error) {
        this.error = 'Failed to load ranking data'
        console.error('Error fetching ranking:', error)
      } finally {
        this.loading = false
      }    },
    getGradeClass(grade) {
      if (!grade) return 'in-progress'
      
      const gradeClasses = {
        'A+': 'excellent',
        'A': 'excellent', 
        'A-': 'excellent',
        'B+': 'good',
        'B': 'good',
        'B-': 'good', 
        'C+': 'average',
        'C': 'average',
        'C-': 'average',
        'D+': 'poor',
        'D': 'poor',
        'D-': 'poor',
        'F': 'fail'
      }
      return gradeClasses[grade] || 'in-progress'
    }  },
  mounted() {
    this.fetchCourses()
  }
}
</script>

<style scoped>
.mark-breakdown {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Page Header Styles */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.header-content h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  text-align: left;
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
}

.course-select {
  display: flex;
  align-items: center;
}

.course-select select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background-color: #f8fafc;
  font-size: 0.9rem;
  min-width: 240px;
  outline: none;
  transition: all 0.2s;
  cursor: pointer;
}

.reload-btn {
  margin-left: 8px;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background-color: #f8fafc;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.reload-btn:hover {
  background-color: #edf2f7;
  color: #2d3748;
}

.course-select select:hover {
  border-color: #a0aec0;
}

.course-select select:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

/* Loading & Empty State Styles */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: #718096;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(66, 153, 225, 0.3);
  border-radius: 50%;
  border-top-color: #4c51bf;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #718096;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e0;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #a0aec0;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #4a5568;
}

.empty-state p {
  font-size: 0.95rem;
  text-align: center;
  max-width: 400px;
  margin: 0;
}

/* Card Styles */
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  margin-bottom: 20px;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.overview-card {
  display: flex;
  align-items: center;
  padding: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  margin-right: 16px;
}

.card-icon svg {
  width: 28px;
  height: 28px;
}

.rank-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.class-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 4px;
}

.card-label {
  font-size: 0.85rem;
  color: #718096;
}

.card-header {
  padding: 16px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.card-body {
  padding: 16px;
}

/* Table Styles */
.table-responsive {
  overflow-x: auto;
}

.mark-table {
  width: 100%;
  border-collapse: collapse;
}

.mark-table th, .mark-table td {
  padding: 12px 16px;
  text-align: left;
}

.mark-table th {
  background-color: #f7fafc;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.mark-table tbody tr {
  border-top: 1px solid #edf2f7;
}

.mark-table tbody tr:hover {
  background-color: #f7fafc;
}

.mark-table tbody td {
  font-size: 0.9rem;
  color: #4a5568;
  vertical-align: middle;
}

.highlight-row {
  background-color: rgba(76, 110, 245, 0.1);
}

.highlight-row td {
  font-weight: 600;
}

.your-name {
  color: #4c6ef5;
  font-weight: 600;
}

.score-cell {
  font-weight: 500;
  color: #2d3748;
}

.grade-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid transparent;
  transition: all 0.2s ease;
}

.grade-badge.excellent { 
  background: #e3fcef !important; 
  color: #27ae60 !important; 
  border-color: #27ae60;
}

.grade-badge.good { 
  background: #e3f2fc !important; 
  color: #3498db !important; 
  border-color: #3498db;
}

.grade-badge.average { 
  background: #fef9e7 !important; 
  color: #f39c12 !important; 
  border-color: #f39c12;
}

.grade-badge.poor { 
  background: #fde3dd !important; 
  color: #e67e22 !important; 
  border-color: #e67e22;
}

.grade-badge.fail { 
  background: #fdeded !important; 
  color: #e74c3c !important; 
  border-color: #e74c3c;
}

.grade-badge.in-progress {
  background: #f0f0f0 !important;
  color: #666 !important;
  border-color: #ccc;
}

/* Error Message */
.error-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  background-color: #fdeded;
  border-radius: 8px;
  border: 1px dashed #e53e3e;
}

.error-message .empty-icon {
  color: #e53e3e;
}

.error-message h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #e53e3e;
}

.error-message p {
  font-size: 0.95rem;
  text-align: center;
  max-width: 400px;
  margin: 0 0 16px 0;
  color: #c53030;
}

.retry-button {
  background-color: #e53e3e;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-button:hover {
  background-color: #c53030;
}

/* Responsive Design */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header-actions {
    margin-top: 16px;
    width: 100%;
  }
  
  .course-select select {
    width: 100%;
  }
  
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .mark-table th, .mark-table td {
    padding: 8px;
  }
  
  .card-header h3 {
    font-size: 1rem;
  }
    .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>