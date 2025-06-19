<template>
  <div class="compare-container">
    <h2 class="page-title">Compare with Coursemates</h2>
    <div class="course-select">
      <select v-model="selectedCourseId" @change="fetchComparison">
        <option value="">Select a Course</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading comparison data...</p>
    </div>

    <div v-else-if="selectedCourseId && components.length > 0" class="comparison-content">
      <div class="stats-cards">
        <div class="stat-card">
          <h3>Your Percentile</h3>
          <div class="stat-value">{{ percentile }}th</div>
        </div>
        <div class="stat-card">
          <h3>Class Average</h3>
          <div class="stat-value">{{ classAverage.toFixed(1) }}%</div>
        </div>
        <div class="stat-card">
          <h3>Your Score</h3>
          <div class="stat-value">{{ yourScore.toFixed(1) }}%</div>
        </div>
        <div class="stat-card">
          <h3>Class Size</h3>
          <div class="stat-value">{{ classSize }}</div>
        </div>
      </div>

      <div class="comparison-charts">
        <div class="chart-container">
          <h3>Score Distribution</h3>
          <canvas ref="distributionChart"></canvas>
        </div>
        <div class="chart-container">
          <h3>Component Comparison</h3>
          <canvas ref="radarChart"></canvas>
        </div>
      </div>

      <div class="table-container">
        <table class="comparison-table">
          <thead>
            <tr>
              <th>Component</th>
              <th>Your Score</th>
              <th>Class Average</th>
              <th>Difference</th>
              <th>Your Position</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comp in components" :key="comp.id">
              <td>{{ comp.name }}</td>
              <td>{{ comp.yourScore.toFixed(1) }}%</td>
              <td>{{ comp.average.toFixed(1) }}%</td>
              <td>
                <span :class="['difference', comp.difference > 0 ? 'positive' : 'negative']">
                  {{ comp.difference > 0 ? '+' : '' }}{{ comp.difference.toFixed(1) }}%
                </span>
              </td>
              <td>{{ comp.position }} of {{ classSize }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else-if="selectedCourseId" class="no-data">
      <p>No comparison data available for this course.</p>
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
  name: 'CompareWithCoursemates',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      components: [],
      percentile: 0,
      classAverage: 0,
      yourScore: 0,
      classSize: 0,
      error: '',
      loading: false,
      distributionChart: null,
      radarChart: null
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
    async fetchComparison() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/courses/${this.selectedCourseId}/comparison/${user.id}`)
        const data = response.data
        
        this.components = data.components
        this.percentile = data.percentile
        this.classAverage = data.classAverage
        this.yourScore = data.yourScore
        this.classSize = data.classSize
        
        this.updateCharts(data)
      } catch (error) {
        this.error = 'Failed to load comparison data'
        console.error('Error fetching comparison:', error)
      } finally {
        this.loading = false
      }
    },
    updateCharts(data) {
      if (this.distributionChart) this.distributionChart.destroy()
      if (this.radarChart) this.radarChart.destroy()
      
      const distCtx = this.$refs.distributionChart
      if (!distCtx) return
      
      this.distributionChart = new Chart(distCtx, {
        type: 'bar',
        data: {
          labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
          datasets: [{
            label: 'Number of Students',
            data: data.distribution,
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
      
      const radarCtx = this.$refs.radarChart
      if (!radarCtx) return
      
      this.radarChart = new Chart(radarCtx, {
        type: 'radar',
        data: {
          labels: this.components.map(c => c.name),
          datasets: [{
            label: 'Your Scores',
            data: this.components.map(c => c.yourScore),
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.2)'
          }, {
            label: 'Class Average',
            data: this.components.map(c => c.average),
            borderColor: '#e74c3c',
            backgroundColor: 'rgba(231, 76, 60, 0.2)'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Component Comparison'
            }
          }
        }
      })
    }
  },
  mounted() {
    this.fetchCourses()
  },
  beforeUnmount() {
    if (this.distributionChart) this.distributionChart.destroy()
    if (this.radarChart) this.radarChart.destroy()
  }
}
</script>

<style scoped>
.compare-container {
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

.comparison-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.chart-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.chart-container h3 {
  font-size: 16px;
  color: #2c3e50;
  margin: 0 0 16px 0;
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

.table-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
  overflow-x: auto;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
}

.comparison-table th, .comparison-table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.comparison-table th {
  color: #7f8c8d;
  font-weight: 500;
}

.difference {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.difference.positive {
  background: #e3fcef;
  color: #27ae60;
}

.difference.negative {
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
