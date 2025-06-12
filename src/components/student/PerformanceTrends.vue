<template>
  <div class="trends-container">
    <h2>Performance Trends</h2>
    <div class="chart-container">
      <div v-if="loading" class="loading">Loading performance data...</div>
      <line-chart 
        v-else-if="performanceData.length > 0"
        :data="chartData"
        :options="chartOptions"
      />
      <div v-else class="no-data">No performance data available</div>
    </div>
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
  name: 'StudentPerformanceTrends',
  components: {
    LineChart
  },  data() {
    return {
      loading: true,
      performanceData: [],
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
            max: 100
          }
        }
      }
    }
  },
  computed: {
    chartData() {
      return {
        labels: this.performanceData.map(d => d.semester),
        datasets: [{
          label: 'Overall Performance',
          data: this.performanceData.map(d => d.score),
          borderColor: '#3498db',
          tension: 0.4
        }]
      }
    }
  },
  methods: {    async fetchPerformanceData() {
      this.loading = true
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await this.$api.get(`/users/${user.id}/performance-trends`)
        this.performanceData = response.data
      } catch (error) {
        console.error('Failed to fetch performance trends:', error)
      } finally {
        this.loading = false
      }
    }
  },
  mounted() {
    this.fetchPerformanceData()
  }
}
</script>

<style scoped>
.trends-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

h2 {
  margin-bottom: 2rem;
  color: #2c3e50;
}

.chart-container {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 400px;
  position: relative;
}

.loading, .no-data {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.9);
  color: #7f8c8d;
  font-size: 1.1rem;
}

.loading {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>