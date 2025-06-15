<template>
  <div class="trends-container">
    <h2 class="page-title">Performance Trends</h2>
    
    <div class="term-selector">
      <button 
        v-for="semester in semesters" 
        :key="semester" 
        :class="['term-btn', selectedSemester === semester ? 'active' : '']"
        @click="selectSemester(semester)"
      >
        {{ semester }}
      </button>
      <button 
        :class="['term-btn', selectedSemester === 'all' ? 'active' : '']"
        @click="selectSemester('all')"
      >
        All Terms
      </button>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading performance data...</p>
    </div>

    <div v-else-if="performanceData.length > 0" class="trends-content">
      <div class="chart-container">
        <h3>GPA Trends</h3>
        <canvas ref="gpaChart"></canvas>
      </div>
      
      <div class="chart-container">
        <h3>Course Performance Comparison</h3>
        <canvas ref="courseChart"></canvas>
      </div>
      
      <div class="stats-cards">
        <div class="stat-card">
          <h3>Current CGPA</h3>
          <div class="stat-value">{{ currentCGPA }}</div>
        </div>
        <div class="stat-card">
          <h3>Highest GPA</h3>
          <div class="stat-value">{{ highestGPA }}</div>
          <div class="stat-detail">{{ highestGPASemester }}</div>
        </div>
        <div class="stat-card">
          <h3>Courses Completed</h3>
          <div class="stat-value">{{ completedCourses }}</div>
        </div>
        <div class="stat-card">
          <h3>Credits Earned</h3>
          <div class="stat-value">{{ totalCredits }}</div>
        </div>
      </div>
      
      <div class="table-container">
        <table class="trends-table">
          <thead>
            <tr>
              <th>Semester</th>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Credit Hours</th>
              <th>Grade</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(course, index) in filteredCourses" :key="index">
              <td>{{ course.semester }}</td>
              <td>{{ course.code }}</td>
              <td>{{ course.name }}</td>
              <td>{{ course.creditHours }}</td>
              <td>
                <span class="grade-badge" :class="getGradeClass(course.grade)">
                  {{ course.grade }}
                </span>
              </td>
              <td>
                <span :class="['status-badge', course.status.toLowerCase()]">
                  {{ course.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="no-data">
      <p>No performance data available.</p>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api'
import Chart from 'chart.js/auto'

export default {
  name: 'PerformanceTrends',
  data() {
    return {
      performanceData: [],
      semesters: [],
      selectedSemester: 'all',
      loading: false,
      error: '',
      gpaChart: null,
      courseChart: null
    }
  },
  computed: {
    currentCGPA() {
      if (!this.performanceData.length) return 'N/A'
      
      const totalPoints = this.performanceData.reduce((sum, course) => {
        return sum + (this.getGradePoint(course.grade) * course.creditHours)
      }, 0)
      
      const totalCredits = this.performanceData.reduce((sum, course) => {
        return sum + course.creditHours
      }, 0)
      
      return (totalPoints / totalCredits).toFixed(2)
    },
    highestGPA() {
      if (!this.semesters.length) return 'N/A'
      
      const gpaByTerm = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return { semester, gpa: 0 }
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return { 
          semester, 
          gpa: totalPoints / totalCredits
        }
      })
      
      const highest = gpaByTerm.reduce((max, term) => {
        return term.gpa > max.gpa ? term : max
      }, { gpa: 0 })
      
      return highest.gpa.toFixed(2)
    },
    highestGPASemester() {
      if (!this.semesters.length) return ''
      
      const gpaByTerm = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return { semester, gpa: 0 }
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return { 
          semester, 
          gpa: totalPoints / totalCredits
        }
      })
      
      const highest = gpaByTerm.reduce((max, term) => {
        return term.gpa > max.gpa ? term : max
      }, { gpa: 0 })
      
      return highest.semester
    },
    completedCourses() {
      return this.performanceData.filter(course => 
        course.status === 'Completed'
      ).length
    },
    totalCredits() {
      return this.performanceData
        .filter(course => course.status === 'Completed')
        .reduce((sum, course) => sum + course.creditHours, 0)
    },
    filteredCourses() {
      if (this.selectedSemester === 'all') {
        return this.performanceData
      } else {
        return this.performanceData.filter(course => 
          course.semester === this.selectedSemester
        )
      }
    }
  },
  methods: {
    async fetchPerformanceData() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/performance`)
        this.performanceData = response.data
        
        // Extract unique semesters
        this.semesters = [...new Set(this.performanceData.map(course => course.semester))]
        
        this.renderCharts()
      } catch (error) {
        this.error = 'Failed to load performance data'
        console.error('Error fetching performance:', error)
      } finally {
        this.loading = false
      }
    },
    selectSemester(semester) {
      this.selectedSemester = semester
    },
    renderCharts() {
      this.renderGPAChart()
      this.renderCourseChart()
    },
    renderGPAChart() {
      if (this.gpaChart) {
        this.gpaChart.destroy()
      }
      
      const ctx = this.$refs.gpaChart
      if (!ctx) return
      
      // Calculate GPA for each term
      const gpaData = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return 0
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return totalPoints / totalCredits
      })
      
      this.gpaChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.semesters,
          datasets: [{
            label: 'Term GPA',
            data: gpaData,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.3,
            fill: true
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              max: 4,
              title: {
                display: true,
                text: 'GPA'
              }
            }
          },
          plugins: {
            title: {
              display: true,
              text: 'GPA by Term'
            }
          }
        }
      })
    },
    renderCourseChart() {
      if (this.courseChart) {
        this.courseChart.destroy()
      }
      
      const ctx = this.$refs.courseChart
      if (!ctx) return
      
      // Group courses by category and calculate average grade
      const categories = [...new Set(this.performanceData.map(course => this.getCourseCategory(course.code)))]
      
      const courseData = categories.map(category => {
        const coursesInCategory = this.performanceData.filter(c => 
          this.getCourseCategory(c.code) === category
        )
        
        if (!coursesInCategory.length) return 0
        
        const totalPoints = coursesInCategory.reduce((sum, course) => {
          return sum + this.getGradePoint(course.grade)
        }, 0)
        
        return (totalPoints / coursesInCategory.length) * 25 // Scale to 0-100
      })
      
      this.courseChart = new Chart(ctx, {
        type: 'radar',
        data: {
          labels: categories,
          datasets: [{
            label: 'Performance by Course Category',
            data: courseData,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.2)'
          }]
        },
        options: {
          responsive: true,
          scales: {
            r: {
              min: 0,
              max: 100,
              ticks: {
                stepSize: 20
              }
            }
          },
          plugins: {
            title: {
              display: true,
              text: 'Performance by Course Category'
            }
          }
        }
      })
    },
    getGradePoint(grade) {
      switch(grade) {
        case 'A+': return 4.0
        case 'A': return 4.0
        case 'A-': return 3.7
        case 'B+': return 3.3
        case 'B': return 3.0
        case 'B-': return 2.7
        case 'C+': return 2.3
        case 'C': return 2.0
        case 'C-': return 1.7
        case 'D+': return 1.3
        case 'D': return 1.0
        case 'F': return 0.0
        default: return 0.0
      }
    },
    getCourseCategory(courseCode) {
      // Extract the subject area from course code (e.g., "SECJ" from "SECJ3303")
      const match = courseCode.match(/^([A-Z]+)/)
      return match ? match[1] : 'Other'
    },
    getGradeClass(grade) {
      if (grade.startsWith('A')) return 'excellent'
      if (grade.startsWith('B')) return 'good'
      if (grade.startsWith('C')) return 'average'
      if (grade.startsWith('D')) return 'poor'
      return 'fail'
    }
  },
  mounted() {
    this.fetchPerformanceData()
  },
  beforeUnmount() {
    if (this.gpaChart) {
      this.gpaChart.destroy()
    }
    if (this.courseChart) {
      this.courseChart.destroy()
    }
  }
}
</script>

<style scoped>
.trends-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 32px;
  font-weight: 300;
  margin-bottom: 32px;
  color: #2c3e50;
}

.term-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 32px;
}

.term-btn {
  padding: 10px 16px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  color: #2c3e50;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.term-btn:hover {
  border-color: #3498db;
  color: #3498db;
}

.term-btn.active {
  background: #3498db;
  color: white;
  border-color: #3498db;
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

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  text-align: center;
}

.stat-card h3 {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0 0 16px 0;
}

.stat-value {
  font-size: 36px;
  font-weight: 600;
  color: #2c3e50;
}

.stat-detail {
  font-size: 14px;
  color: #7f8c8d;
  margin-top: 8px;
}

.table-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
  overflow-x: auto;
}

.trends-table {
  width: 100%;
  border-collapse: collapse;
}

.trends-table th, .trends-table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.trends-table th {
  color: #7f8c8d;
  font-weight: 500;
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

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
}

.status-badge.completed {
  background: #e3fcef;
  color: #27ae60;
}

.status-badge.inprogress {
  background: #e3f2fc;
  color: #3498db;
}

.status-badge.pending {
  background: #fef9e7;
  color: #f39c12;
}

.status-badge.withdrawn {
  background: #fdeded;
  color: #e74c3c;
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