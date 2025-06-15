<template>
  <div class="mark-breakdown">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Mark Breakdown</h2>
        <p class="subtitle">View your detailed course marks and progress</p>
      </div>
      <div class="header-actions">
        <div class="course-select">
          <select v-model="selectedCourseId" @change="fetchMarks">
            <option value="">Select a Course</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.code }} - {{ course.name }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading mark data...</p>
    </div>

    <div v-else-if="!selectedCourseId" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <h3>No Course Selected</h3>
      <p>Please select a course from the dropdown to view mark breakdown</p>
    </div>

    <div v-else-if="components.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
      </div>
      <h3>No Mark Data Available</h3>
      <p>There are no assessment components or marks available for this course yet</p>
    </div>

    <div v-else class="mark-content">
      <!-- Summary Cards -->
      <div class="dashboard-cards">
        <div class="card overview-card">
          <div class="card-icon grade-icon" :class="getGradeIconClass(overallGrade)">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ overallGrade }}</div>
            <div class="card-label">Current Grade</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon score-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ totalScore.toFixed(2) }}%</div>
            <div class="card-label">Overall Score</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon average-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ classAverage.toFixed(2) }}%</div>
            <div class="card-label">Class Average</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon status-icon" :class="totalScore >= 40 ? 'pass-icon' : 'fail-icon'">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ totalScore >= 40 ? 'PASS' : 'FAIL' }}</div>
            <div class="card-label">Status</div>
          </div>
        </div>
      </div>

      <!-- Progress Card -->
      <div class="card progress-card">
        <div class="card-header">
          <h3>Overall Progress</h3>
        </div>
        <div class="card-body">
          <div class="progress-container">
            <div class="progress-bar" :style="{ width: `${totalScore}%` }"></div>
          </div>
          <div class="progress-stats">
            <div class="stat">
              <span class="stat-label">Current</span>
              <span class="stat-value">{{ totalScore.toFixed(2) }}%</span>
            </div>
            <div class="stat">
              <span class="stat-label">Target</span>
              <span class="stat-value">100%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Mark Breakdown Table -->
      <div class="card table-card">
        <div class="card-header">
          <h3>Component Breakdown</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="mark-table">
              <thead>
                <tr>
                  <th>Component</th>
                  <th>Weight (%)</th>
                  <th>Your Mark</th>
                  <th>Max Mark</th>
                  <th>Weighted Score</th>
                  <th>Progress</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="component in components" :key="component.id">
                  <td>{{ component.name }}</td>
                  <td>{{ component.weight }}%</td>
                  <td>{{ component.mark }}</td>
                  <td>{{ component.max_marks }}</td>
                  <td>{{ calculateWeightedScore(component) }}</td>
                  <td>
                    <div class="mini-progress">
                      <div class="mini-progress-bar" 
                           :style="{ width: `${(component.mark / component.max_marks) * 100}%` }"
                           :class="getProgressClass(component.mark, component.max_marks)">
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="total-label">Total</td>
                  <td class="total-value">{{ totalScore.toFixed(2) }}%</td>
                  <td>
                    <div class="mini-progress">
                      <div class="mini-progress-bar" 
                           :style="{ width: `${totalScore}%` }"
                           :class="getProgressClass(totalScore, 100)">
                      </div>
                    </div>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Grade Distribution Chart -->
      <div class="card chart-card">
        <div class="card-header">
          <h3>Mark Distribution</h3>
        </div>
        <div class="card-body">
          <canvas ref="markChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'
import Chart from 'chart.js/auto'

export default {
  name: 'MarkBreakdown',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      components: [],
      totalScore: 0,
      overallGrade: 'N/A',
      classAverage: 0,
      loading: false,
      error: '',
      chart: null
    }
  },
  methods: {
    async fetchCourses() {
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        if (!user) {
          this.$router.push('/login')
          return
        }
        
        const response = await api.get('/student/courses')
        this.courses = response.data
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      } finally {
        this.loading = false
      }
    },
    async fetchMarks() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/student/marks/${user.id}/${this.selectedCourseId}`)
        
        this.components = response.data.components
        this.totalScore = response.data.totalMarks
        this.overallGrade = response.data.grade
        
        // For demo purposes, generate a class average
        this.classAverage = this.totalScore - (Math.random() * 10) + (Math.random() * 5)
        if (this.classAverage > 100) this.classAverage = 98.5
        if (this.classAverage < 0) this.classAverage = 35.0
        
        this.$nextTick(() => {
          this.createMarkChart()
        })
      } catch (error) {
        this.error = 'Failed to load mark data'
        console.error('Error fetching marks:', error)
      } finally {
        this.loading = false
      }
    },
    calculateWeightedScore(component) {
      if (!component.max_marks) return '0.00'
      return ((component.mark / component.max_marks) * component.weight).toFixed(2)
    },    getGradeClass(grade) {
      if (!grade || grade === 'N/A') return 'grade-na';
      
      const gradeMap = {
        'A+': 'grade-a-plus',
        'A': 'grade-a',
        'A-': 'grade-a-minus',
        'B+': 'grade-b-plus',
        'B': 'grade-b',
        'B-': 'grade-b-minus',
        'C+': 'grade-c-plus',
        'C': 'grade-c',
        'C-': 'grade-c-minus',
        'D+': 'grade-d-plus',
        'D': 'grade-d',
        'F': 'grade-f'
      };
      
      return gradeMap[grade] || 'grade-na';
    },
    getGradeIconClass(grade) {
      if (!grade || grade === 'N/A') return 'grade-na-icon';
      
      if (grade.startsWith('A')) return 'grade-a-icon';
      if (grade.startsWith('B')) return 'grade-b-icon';
      if (grade.startsWith('C')) return 'grade-c-icon';
      if (grade.startsWith('D')) return 'grade-d-icon';
      if (grade === 'F') return 'grade-f-icon';
      
      return 'grade-na-icon';
    },
    getProgressClass(mark, maxMark) {
      if (!maxMark) return 'progress-na';
      
      const percentage = (mark / maxMark) * 100;
      
      if (percentage >= 80) return 'progress-excellent';
      if (percentage >= 70) return 'progress-good';
      if (percentage >= 60) return 'progress-fair';
      if (percentage >= 40) return 'progress-pass';
      
      return 'progress-fail';
    },
    createMarkChart() {
      if (this.chart) {
        this.chart.destroy();
      }
      
      const ctx = this.$refs.markChart.getContext('2d');
      
      // Generate component names and scores for chart
      const labels = this.components.map(c => c.name);
      const scores = this.components.map(c => (c.mark / c.max_marks) * 100);
      
      // Create chart
      this.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Your Score (%)',
            data: scores,
            backgroundColor: '#4c51bf',
            borderColor: '#3c40a7',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              title: {
                display: true,
                text: 'Score (%)'
              }
            }
          },
          plugins: {
            title: {
              display: true,
              text: 'Component Performance'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `Score: ${context.raw.toFixed(2)}%`;
                }
              }
            }
          }
        }
      });
    }
  },
  async mounted() {
    await this.fetchCourses();
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
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
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

.grade-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.score-icon {
  background-color: #c4f1f9;
  color: #0987a0;
}

.average-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.status-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.pass-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.grade-a-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.grade-b-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.grade-c-icon {
  background-color: #feebc8;
  color: #c05621;
}

.grade-d-icon {
  background-color: #fed7d7;
  color: #e53e3e;
}

.grade-f-icon {
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

/* Progress Bar Styles */
.progress-container {
  height: 12px;
  background-color: #edf2f7;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-bar {
  height: 100%;
  background-color: #4c51bf;
  border-radius: 6px;
  transition: width 0.5s ease;
}

.progress-stats {
  display: flex;
  justify-content: space-between;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.8rem;
  color: #718096;
}

.stat-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2d3748;
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

.mark-table tfoot tr {
  border-top: 2px solid #e2e8f0;
}

.total-label {
  font-weight: 600;
  text-align: right;
  color: #2d3748;
}

.total-value {
  font-weight: 700;
  color: #2d3748;
}

.mini-progress {
  height: 8px;
  background-color: #edf2f7;
  border-radius: 4px;
  overflow: hidden;
  width: 100%;
}

.mini-progress-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-excellent {
  background-color: #48bb78;
}

.progress-good {
  background-color: #4c51bf;
}

.progress-fair {
  background-color: #ed8936;
}

.progress-pass {
  background-color: #ecc94b;
}

.progress-fail {
  background-color: #f56565;
}

/* Chart Card */
.chart-card .card-body {
  height: 300px;
}

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
}
</style>
