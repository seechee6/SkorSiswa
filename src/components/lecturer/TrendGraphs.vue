<template>
  <div>
    <h3>Performance Trend Graphs</h3>
    <div class="card add-form">
      <form @submit.prevent="fetchTrend">
        <input v-model="course_id" placeholder="Course ID" required />
        <button type="submit">Show Trend</button>
      </form>
    </div>
    <div class="card">
      <canvas v-if="showChart" id="trendChart"></canvas>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import Chart from 'chart.js/auto'
import api from '../../api'
export default {
  name: 'TrendGraphs',
  data() {
    return {
      course_id: '',
      error: '',
      showChart: false
    }
  },
  methods: {
    async fetchTrend() {
      this.error = ''
      this.showChart = false
      try {
        const res = await api.get(`/courses/${this.course_id}/marks`)
        const marks = res.data
        if (!marks.length) {
          this.error = 'No marks found.'
          return
        }
        // Group by component and sort by component name (assumes chronological order)
        const labels = [...new Set(marks.map(m => m.component_name))]
        const data = labels.map(label => {
          const filtered = marks.filter(m => m.component_name === label)
          return filtered.reduce((sum, m) => sum + Number(m.mark), 0) / filtered.length
        })
        this.$nextTick(() => {
          if (this._chart) this._chart.destroy()
          const ctx = document.getElementById('trendChart')
          this._chart = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Class Average', data, fill: false, borderColor: 'blue' }] },
            options: { responsive: true }
          })
          this.showChart = true
        })
      } catch (e) {
        this.error = 'Failed to load trend data.'
      }
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
  max-width: 700px;
}
.add-form {
  max-width: 400px;
}
input {
  margin: 6px 0;
  padding: 8px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  margin-top: 8px;
  padding: 6px 14px;
  border: none;
  border-radius: 4px;
  background: #3498db;
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
}
button:hover {
  background: #2980b9;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}
</style>
