<template>
  <div class="ranking-container">
    <h2 class="page-title">Class Ranking</h2>
    
    <div class="course-select">
      <select v-model="selectedCourseId" @change="fetchRanking">
        <option value="">Select a Course</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading ranking data...</p>
    </div>

    <div v-else-if="selectedCourseId && rankData" class="ranking-content">
      <div class="ranking-header">
        <div class="your-ranking">
          <div class="rank-number">{{ rankData.yourRank }}</div>
          <div class="rank-label">Your Rank</div>
        </div>
        <div class="rank-stats">
          <div class="rank-stat">
            <div class="stat-value">{{ rankData.yourScore.toFixed(1) }}%</div>
            <div class="stat-label">Your Score</div>
          </div>
          <div class="rank-stat">
            <div class="stat-value">{{ rankData.percentile }}th</div>
            <div class="stat-label">Percentile</div>
          </div>
          <div class="rank-stat">
            <div class="stat-value">{{ rankData.totalStudents }}</div>
            <div class="stat-label">Total Students</div>
          </div>
        </div>
      </div>

      <div class="chart-container">
        <h3>Score Distribution</h3>
        <canvas ref="distributionChart"></canvas>
      </div>

      <div class="table-container">
        <div class="table-header">
          <h3>Top 10 Students</h3>
          <div class="anonymous-toggle">
            <span>Anonymous Mode</span>
            <label class="switch">
              <input type="checkbox" v-model="anonymousMode">
              <span class="slider"></span>
            </label>
          </div>
        </div>
        <table class="ranking-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student</th>
              <th>Score</th>
              <th>Grade</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, index) in rankData.topStudents" :key="index" 
                :class="{'your-row': student.isYou}">
              <td>{{ student.rank }}</td>
              <td>
                <span v-if="student.isYou">You</span>
                <span v-else-if="anonymousMode">Student {{ student.anonymousId }}</span>
                <span v-else>{{ student.name }}</span>
              </td>
              <td>{{ student.score.toFixed(1) }}%</td>
              <td>
                <span class="grade-badge" :class="getGradeClass(student.grade)">
                  {{ student.grade }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="rankData.yourRank > 10" class="your-position">
        <h3>Your Position</h3>
        <table class="ranking-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student</th>
              <th>Score</th>
              <th>Grade</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rankData.surroundingStudents[0]">
              <td>{{ rankData.surroundingStudents[0].rank }}</td>
              <td>
                <span v-if="anonymousMode">Student {{ rankData.surroundingStudents[0].anonymousId }}</span>
                <span v-else>{{ rankData.surroundingStudents[0].name }}</span>
              </td>
              <td>{{ rankData.surroundingStudents[0].score.toFixed(1) }}%</td>
              <td>
                <span class="grade-badge" :class="getGradeClass(rankData.surroundingStudents[0].grade)">
                  {{ rankData.surroundingStudents[0].grade }}
                </span>
              </td>
            </tr>
            <tr class="your-row">
              <td>{{ rankData.yourRank }}</td>
              <td>You</td>
              <td>{{ rankData.yourScore.toFixed(1) }}%</td>
              <td>
                <span class="grade-badge" :class="getGradeClass(rankData.yourGrade)">
                  {{ rankData.yourGrade }}
                </span>
              </td>
            </tr>
            <tr v-if="rankData.surroundingStudents[1]">
              <td>{{ rankData.surroundingStudents[1].rank }}</td>
              <td>
                <span v-if="anonymousMode">Student {{ rankData.surroundingStudents[1].anonymousId }}</span>
                <span v-else>{{ rankData.surroundingStudents[1].name }}</span>
              </td>
              <td>{{ rankData.surroundingStudents[1].score.toFixed(1) }}%</td>
              <td>
                <span class="grade-badge" :class="getGradeClass(rankData.surroundingStudents[1].grade)">
                  {{ rankData.surroundingStudents[1].grade }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else-if="selectedCourseId" class="no-data">
      <p>No ranking data available for this course.</p>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api'
import Chart from 'chart.js/auto'

export default /* eslint-disable vue/multi-word-component-names */ {
  name: 'Ranking',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      rankData: null,
      error: '',
      loading: false,
      anonymousMode: true,
      distributionChart: null
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/courses`)
        this.courses = response.data
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },
    async fetchRanking() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/courses/${this.selectedCourseId}/ranking/${user.id}`)
        this.rankData = response.data
        
        this.renderDistributionChart()
      } catch (error) {
        this.error = 'Failed to load ranking data'
        console.error('Error fetching ranking:', error)
      } finally {
        this.loading = false
      }
    },
    renderDistributionChart() {
      if (!this.rankData) return
      
      if (this.distributionChart) {
        this.distributionChart.destroy()
      }
      
      const ctx = this.$refs.distributionChart
      if (!ctx) return
      
      // Create a histogram of scores
      this.distributionChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
          datasets: [{
            label: 'Number of Students',
            data: this.rankData.distribution,
            backgroundColor: '#3498db'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Class Score Distribution'
            }
          }
        }
      })
    },
    getGradeClass(grade) {
      const gradeClasses = {
        'A': 'excellent',
        'B': 'good',
        'C': 'average',
        'D': 'poor',
        'F': 'fail'
      }
      return gradeClasses[grade] || 'in-progress'
    }
  },
  mounted() {
    this.fetchCourses()
  },
  beforeUnmount() {
    if (this.distributionChart) {
      this.distributionChart.destroy()
    }
  }
}
</script>

<style scoped>
.ranking-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 32px;
  font-weight: 300;
  margin-bottom: 32px;
  color: #2c3e50;
}

.course-select {
  margin-bottom: 32px;
}

.course-select select {
  width: 100%;
  max-width: 400px;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  color: #2c3e50;
  background: white;
}

.ranking-header {
  display: grid;
  grid-template-columns: auto 1fr;
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
  gap: 24px;
}

.your-ranking {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #3498db;
  color: white;
  border-radius: 8px;
  min-width: 150px;
}

.rank-number {
  font-size: 64px;
  font-weight: 700;
  line-height: 1;
}

.rank-label {
  font-size: 16px;
  margin-top: 8px;
}

.rank-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.rank-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.stat-value {
  font-size: 32px;
  font-weight: 600;
  color: #2c3e50;
}

.stat-label {
  font-size: 14px;
  color: #7f8c8d;
  margin-top: 8px;
}

.chart-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
}

.chart-container h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0 0 24px 0;
}

.table-container, .your-position {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
  overflow-x: auto;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.table-header h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0;
}

.anonymous-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #7f8c8d;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #3498db;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.ranking-table {
  width: 100%;
  border-collapse: collapse;
}

.ranking-table th, .ranking-table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.ranking-table th {
  color: #7f8c8d;
  font-weight: 500;
}

.your-row {
  background-color: rgba(52, 152, 219, 0.1);
  font-weight: 600;
}

.grade-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

.grade-badge.excellent { 
  background: #e3fcef; 
  color: #27ae60; 
}

.grade-badge.good { 
  background: #e3f2fc; 
  color: #3498db; 
}

.grade-badge.average { 
  background: #fef9e7; 
  color: #f39c12; 
}

.grade-badge.poor { 
  background: #fde3dd; 
  color: #e67e22; 
}

.grade-badge.fail { 
  background: #fdeded; 
  color: #e74c3c; 
}

.your-position h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0 0 16px 0;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-data, .error-message {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  text-align: center;
  color: #7f8c8d;
}

.error-message {
  background: #fdeded;
  color: #e74c3c;
}
</style>