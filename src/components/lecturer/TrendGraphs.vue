<template>
  <div>
    <h3>Performance Trend Analysis</h3>
    
    <!-- Course Selection -->
    <div class="card course-selection">
      <h4>Select Course</h4>
      <select v-model="selectedCourseId" @change="fetchTrendData" class="course-select">
        <option value="">Select a course...</option>
        <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <!-- Chart Type Selection -->
    <div class="card chart-options" v-if="selectedCourseId">
      <h4>Chart Options</h4>
      <div class="options-grid">
        <div class="option-group">
          <h5>Chart Type</h5>
          <div class="radio-group">
            <label class="radio-option">
              <input type="radio" v-model="chartType" value="line" @change="updateChart" />
              <span class="radio-label">Line Chart</span>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="chartType" value="bar" @change="updateChart" />
              <span class="radio-label">Bar Chart</span>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="chartType" value="area" @change="updateChart" />
              <span class="radio-label">Area Chart</span>
            </label>
          </div>
        </div>
        
        <div class="option-group">
          <h5>Data View</h5>
          <div class="radio-group">
            <label class="radio-option">
              <input type="radio" v-model="dataView" value="average" @change="updateChart" />
              <span class="radio-label">Class Average</span>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="dataView" value="individual" @change="updateChart" />
              <span class="radio-label">Individual Students</span>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="dataView" value="both" @change="updateChart" />
              <span class="radio-label">Both</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Summary -->
    <div class="card statistics" v-if="selectedCourseId && statistics">
      <h4>Performance Statistics</h4>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-value">{{ statistics.overallAverage }}%</div>
          <div class="stat-label">Overall Average</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ statistics.improvementTrend }}</div>
          <div class="stat-label">Trend Direction</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ statistics.bestAssessment }}</div>
          <div class="stat-label">Best Performance</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ statistics.worstAssessment }}</div>
          <div class="stat-label">Needs Attention</div>
        </div>
      </div>
    </div>

    <!-- Chart Container -->
    <div class="card chart-container" v-if="selectedCourseId && chartData">
      <div class="chart-header">
        <h4>{{ selectedCourseName }} - Performance Trends</h4>
        <div class="chart-controls">
          <button @click="exportChart" class="export-btn">
            Export Chart
          </button>
          <button @click="toggleFullscreen" class="fullscreen-btn">
            Fullscreen
          </button>
        </div>
      </div>
      <div class="chart-wrapper" :class="{ 'fullscreen': isFullscreen }">
        <canvas ref="trendChart"></canvas>
      </div>
    </div>

    <!-- Individual Student Performance -->
    <div class="card student-breakdown" v-if="selectedCourseId && dataView === 'individual' && studentData.length > 0">
      <h4>Individual Student Trends</h4>
      <div class="student-selector">
        <select v-model="selectedStudentId" @change="updateChart" class="student-select">
          <option value="">All Students</option>
          <option v-for="student in studentData" :key="student.id" :value="student.id">
            {{ student.name }} ({{ student.matric_no }})
          </option>
        </select>
      </div>
    </div>

    <!-- Assessment Comparison -->
    <div class="card assessment-comparison" v-if="selectedCourseId && assessmentComparison">
      <h4>Assessment Performance Comparison</h4>
      <div class="comparison-table">
        <table>
          <thead>
            <tr>
              <th>Assessment</th>
              <th>Average</th>
              <th>Highest</th>
              <th>Lowest</th>
              <th>Std Deviation</th>
              <th>Trend</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assessment in assessmentComparison" :key="assessment.name">
              <td>{{ assessment.name }}</td>
              <td>{{ assessment.average }}%</td>
              <td>{{ assessment.highest }}%</td>
              <td>{{ assessment.lowest }}%</td>
              <td>{{ assessment.stdDev }}%</td>
              <td>
                <span class="trend-indicator" :class="assessment.trendClass">
                  {{ assessment.trendIcon }} {{ assessment.trendText }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Course Not Selected -->
    <div class="card empty-state" v-else-if="!selectedCourseId">
      <div class="empty-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <h4>Select a Course</h4>
        <p>Choose a course to view performance trend analysis.</p>
      </div>
    </div>

    <!-- Error/Success Messages -->
    <div v-if="error" class="floating-message error">
      {{ error }}
    </div>
    <div v-if="success" class="floating-message success">
      {{ success }}
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import api from '../../api';

export default {
  name: 'TrendGraphs',
  data() {
    return {
      lecturerCourses: [],
      selectedCourseId: '',
      chartType: 'line',
      dataView: 'average',
      selectedStudentId: '',
      chartData: null,
      statistics: null,
      assessmentComparison: null,
      studentData: [],
      isFullscreen: false,
      chart: null,
      error: '',
      success: ''
    }
  },
  computed: {
    selectedCourseName() {
      const course = this.lecturerCourses.find(c => c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    }
  },
  methods: {
    async fetchLecturerCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get('/courses')
        this.lecturerCourses = res.data.filter(c => c.lecturer_id === user.id)
      } catch (e) {
        this.error = 'Failed to load courses.'
      }
    },
    
    async fetchTrendData() {
      if (!this.selectedCourseId) return
      
      try {
        // Fetch assessment marks
        const marksRes = await api.get(`/courses/${this.selectedCourseId}/marks`)
        
        this.processChartData(marksRes.data)
        this.calculateStatistics()
        this.generateAssessmentComparison(marksRes.data)
        this.updateChart()
        
      } catch (e) {
        this.error = 'Failed to load trend data.'
      }
    },
    
    processChartData(marksData) {
      const { assessment_marks, final_marks } = marksData
      
      // Group by assessment
      const assessmentGroups = {}
      assessment_marks.forEach(mark => {
        if (!assessmentGroups[mark.assessment_name]) {
          assessmentGroups[mark.assessment_name] = []
        }
        assessmentGroups[mark.assessment_name].push(mark)
      })
      
      // Calculate averages
      const labels = Object.keys(assessmentGroups).sort()
      const averages = labels.map(label => {
        const marks = assessmentGroups[label]
        const total = marks.reduce((sum, mark) => sum + parseFloat(mark.mark), 0)
        return Math.round(total / marks.length * 100) / 100
      })
      
      // Add final exam if exists
      if (final_marks && final_marks.length > 0) {
        labels.push('Final Exam')
        const finalTotal = final_marks.reduce((sum, mark) => sum + parseFloat(mark.mark), 0)
        averages.push(Math.round(finalTotal / final_marks.length * 100) / 100)
      }
      
      this.chartData = { labels, averages, assessmentGroups }
      
      // Process individual student data
      this.processStudentData(marksData)
    },
    
    processStudentData(marksData) {
      const { assessment_marks } = marksData
      const studentMap = new Map()
      
      assessment_marks.forEach(mark => {
        if (!studentMap.has(mark.student_id)) {
          studentMap.set(mark.student_id, {
            id: mark.student_id,
            name: mark.student_name,
            matric_no: mark.matric_no,
            marks: {}
          })
        }
        studentMap.get(mark.student_id).marks[mark.assessment_name] = parseFloat(mark.mark)
      })
      
      this.studentData = Array.from(studentMap.values())
    },
    
    calculateStatistics() {
      if (!this.previewData.length) return
      
      const averages = this.previewData.map(s => s.average).filter(a => a > 0)
      const classAverage = Math.round(averages.reduce((sum, avg) => sum + avg, 0) / averages.length)
      const highest = Math.max(...averages)
      const lowest = Math.min(...averages)
      const passCount = averages.filter(avg => avg >= 50).length
      const passRate = Math.round((passCount / averages.length) * 100)
      
      // Grade distribution
      const gradeDistribution = ['A', 'B', 'C', 'D', 'F'].map(grade => {
        const count = this.previewData.filter(s => s.grade === grade).length
        const percentage = Math.round((count / this.previewData.length) * 100)
        return { grade, count, percentage }
      })
      
      this.statistics = {
        classAverage,
        highest,
        lowest,
        passRate,
        gradeDistribution
      }
    },
    
    generateAssessmentComparison(marksData) {
      const { assessment_marks } = marksData
      const assessmentStats = {}
      
      assessment_marks.forEach(mark => {
        const name = mark.assessment_name
        if (!assessmentStats[name]) {
          assessmentStats[name] = []
        }
        assessmentStats[name].push(parseFloat(mark.mark))
      })
      
      this.assessmentComparison = Object.entries(assessmentStats).map(([name, marks]) => {
        const average = Math.round(marks.reduce((a, b) => a + b) / marks.length * 100) / 100
        const highest = Math.max(...marks)
        const lowest = Math.min(...marks)
        
        // Calculate standard deviation
        const variance = marks.reduce((sum, mark) => sum + Math.pow(mark - average, 2), 0) / marks.length
        const stdDev = Math.round(Math.sqrt(variance) * 100) / 100
        
        // Determine trend (simplified)
        let trendClass = 'stable'
        let trendIcon = '➡️'
        let trendText = 'Stable'
        
        if (average > 75) {
          trendClass = 'good'
          trendIcon = '📈'
          trendText = 'Good'
        } else if (average < 60) {
          trendClass = 'poor'
          trendIcon = '📉'
          trendText = 'Needs Attention'
        }
        
        return {
          name,
          average,
          highest,
          lowest,
          stdDev,
          trendClass,
          trendIcon,
          trendText
        }
      })
    },
    
    updateChart() {
      if (!this.chartData || !this.$refs.trendChart) return
      
      if (this.chart) {
        this.chart.destroy()
      }
      
      const ctx = this.$refs.trendChart.getContext('2d')
      const config = this.getChartConfig()
      
      this.chart = new Chart(ctx, config)
    },
    
    getChartConfig() {
      const { labels, averages } = this.chartData
      
      let datasets = []
      
      if (this.dataView === 'average' || this.dataView === 'both') {
        datasets.push({
          label: 'Class Average',
          data: averages,
          borderColor: '#457B9D',
          backgroundColor: this.chartType === 'area' ? 'rgba(69, 123, 157, 0.2)' : '#457B9D',
          fill: this.chartType === 'area',
          tension: 0.4
        })
      }
      
      if ((this.dataView === 'individual' || this.dataView === 'both') && this.selectedStudentId) {
        const student = this.studentData.find(s => s.id == this.selectedStudentId)
        if (student) {
          const studentMarks = labels.map(label => student.marks[label] || 0)
          datasets.push({
            label: student.name,
            data: studentMarks,
            borderColor: '#E63946',
            backgroundColor: 'rgba(230, 57, 70, 0.2)',
            fill: false,
            tension: 0.4
          })
        }
      }
      
      return {
        type: this.chartType === 'area' ? 'line' : this.chartType,
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            title: {
              display: true,
              text: `${this.selectedCourseName} Performance Trends`
            },
            legend: {
              display: true
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              title: {
                display: true,
                text: 'Marks (%)'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Assessments'
              }
            }
          }
        }
      }
    },
    
    exportChart() {
      if (!this.chart) return
      
      const url = this.chart.toBase64Image()
      const a = document.createElement('a')
      a.href = url
      a.download = `${this.selectedCourseName}_trends.png`
      a.click()
      
      this.success = 'Chart exported successfully!'
      setTimeout(() => this.success = '', 3000)
    },
    
    toggleFullscreen() {
      this.isFullscreen = !this.isFullscreen
      setTimeout(() => {
        if (this.chart) {
          this.chart.resize()
        }
      }, 100)
    }
  },
  
  mounted() {
    this.fetchLecturerCourses()
  },
  
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy()
    }
  }
}
</script>

<style scoped>
.card {
  background: #fff;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.course-select, .student-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
}

.option-group h5 {
  margin: 0 0 12px 0;
  color: #1D3557;
  font-weight: 600;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.radio-label {
  font-size: 14px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.stat-item {
  text-align: center;
  padding: 16px;
  background: #F1FAEE;
  border-radius: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #1D3557;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-controls {
  display: flex;
  gap: 8px;
}

.export-btn, .fullscreen-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  background: #457B9D;
  color: white;
  cursor: pointer;
  font-size: 12px;
}

.export-btn:hover, .fullscreen-btn:hover {
  background: #356a83;
}

.chart-wrapper {
  position: relative;
  height: 400px;
  transition: all 0.3s ease;
}

.chart-wrapper.fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  background: white;
  height: 100vh;
  padding: 20px;
}

.comparison-table {
  overflow-x: auto;
}

.comparison-table table {
  width: 100%;
  border-collapse: collapse;
}

.comparison-table th,
.comparison-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.comparison-table th {
  background: #f8f9fa;
  font-weight: 600;
}

.trend-indicator {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: 500;
}

.trend-indicator.good {
  background: #d4edda;
  color: #155724;
}

.trend-indicator.poor {
  background: #f8d7da;
  color: #721c24;
}

.trend-indicator.stable {
  background: #fff3cd;
  color: #856404;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  opacity: 0.5;
}

.floating-message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 4px;
  z-index: 1000;
  font-weight: 500;
}

.floating-message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.floating-message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>
