<template>
  <div class="ranking-container">
    <h2>Academic Standing</h2>

    <div v-if="loading" class="loading-overlay">
      Loading academic data...
    </div>

    <template v-else>
      <div class="overall-stats">
        <div class="stat-card">
          <h3>Overall CGPA</h3>
          <div class="value">{{ overallStats.cgpa || 'N/A' }}</div>
          <div class="sub-text">Out of 4.00</div>
        </div>
        <div class="stat-card">
          <h3>Batch Ranking</h3>
          <div class="value">{{ overallStats.ranking || 'N/A' }}</div>
          <div class="sub-text">
            Out of {{ overallStats.totalStudents }} Students
          </div>
        </div>
        <div class="stat-card">
          <h3>Percentile</h3>
          <div class="value">{{ overallStats.percentile || 'N/A' }}</div>
          <div class="sub-text">Top {{ getPercentileText(overallStats.percentile) }}</div>
        </div>
      </div>

      <div class="course-rankings">
        <h3>Course-wise Standing</h3>
        
        <div class="courses-grid">
          <div v-for="course in courseRankings" 
               :key="course.id" 
               class="course-card">
            <div class="course-header">
              <h4>{{ course.code }}</h4>
              <div class="grade" :class="getGradeClass(course.grade)">
                {{ course.grade }}
              </div>
            </div>
            
            <div class="ranking-details">
              <div class="detail-row">
                <span>Class Rank:</span>
                <span>{{ course.ranking }} / {{ course.totalStudents }}</span>
              </div>
              <div class="detail-row">
                <span>Percentile:</span>
                <span>{{ course.percentile }}th</span>
              </div>
              <div class="detail-row">
                <span>Class Average:</span>
                <span>{{ course.classAverage.toFixed(2) }}</span>
              </div>
            </div>

            <div class="distribution-chart">
              <div class="chart-bars">
                <div v-for="(count, grade) in course.gradeDistribution" 
                     :key="grade"
                     class="bar-container">
                  <div class="bar"
                       :style="{ height: getBarHeight(count, course.totalStudents) }"
                       :class="{ 'highlight': grade === course.grade }">
                  </div>
                  <span class="grade-label">{{ grade }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>    <div class="performance-trend">
        <h3>Performance Trend</h3>
        <div class="chart-container">
          <line-chart 
            v-if="trendData.length > 0"
            :data="trendChartData"
            :options="chartOptions"
          />
          <div v-else class="no-data">
            No trend data available
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { Line as LineChart } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import api from '../../api'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
)

export default {
  name: 'StudentRanking',
  components: {
    LineChart
  },  data() {
    return {
      loading: true,
      overallStats: {
        cgpa: null,
        ranking: null,
        totalStudents: 0,
        percentile: null
      },
      courseRankings: [],
      trendData: [],
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 4
          }
        }
      }
    }
  },
  computed: {
    trendChartData() {
      return {
        labels: this.trendData.map(d => d.semester),
        datasets: [{
          label: 'Your CGPA',
          data: this.trendData.map(d => d.cgpa),
          borderColor: '#3498db',
          tension: 0.4
        },
        {
          label: 'Batch Average',
          data: this.trendData.map(d => d.batchAverage),
          borderColor: '#95a5a6',
          tension: 0.4
        }]
      }
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
      return gradeClasses[grade] || 'pending'
    },
    getPercentileText(percentile) {
      if (!percentile) return ''
      return `${100 - percentile}%`
    },
    getBarHeight(count, total) {
      return `${(count / total * 100)}%`
    },    async fetchRankingData() {
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        
        // Fetch overall stats
        const statsResponse = await api.get(`/users/${user.id}/academic-standing`)
        this.overallStats = statsResponse.data

        // Fetch course rankings
        const coursesResponse = await api.get(`/users/${user.id}/course-rankings`)
        this.courseRankings = coursesResponse.data

        // Fetch trend data
        const trendResponse = await api.get(`/users/${user.id}/cgpa-trend`)
        this.trendData = trendResponse.data
      } catch (error) {
        console.error('Failed to fetch ranking data:', error)
      } finally {
        this.loading = false
      }
    }
  },
  mounted() {
    this.fetchRankingData()
  }
}
</script>

<style scoped>
.ranking-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h2 {
  margin-bottom: 2rem;
  color: #2c3e50;
}

.overall-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-card h3 {
  font-size: 0.9rem;
  color: #7f8c8d;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.value {
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.sub-text {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.course-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.course-header h4 {
  margin: 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.grade {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 500;
}

.ranking-details {
  margin-bottom: 1.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.detail-row span:first-child {
  color: #7f8c8d;
}

.distribution-chart {
  height: 150px;
  margin-top: 1.5rem;
}

.chart-bars {
  height: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
}

.bar-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 0.25rem;
}

.bar {
  width: 100%;
  background: #ecf0f1;
  transition: height 0.3s ease;
}

.bar.highlight {
  background: #3498db;
}

.grade-label {
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #7f8c8d;
}

.performance-trend {
  margin-top: 3rem;
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-container {
  height: 300px;
  margin-top: 1.5rem;
}

.excellent { background: #e3fcef; color: #27ae60; }
.good { background: #e3f2fc; color: #3498db; }
.average { background: #ffeeba; color: #f1c40f; }
.poor { background: #ffe3e3; color: #e74c3c; }
.fail { background: #fee; color: #c0392b; }
.pending { background: #f0f3f6; color: #7f8c8d; }

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #7f8c8d;
  animation: pulse 1.5s infinite;
  z-index: 100;
}

.no-data {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7f8c8d;
  font-size: 1.1rem;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>