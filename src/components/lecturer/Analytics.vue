<template>
  <div>
    <h3>Performance Analytics & Averages</h3>
    <div class="card">
      <canvas id="lecturerAnalyticsChart"></canvas>
    </div>
  </div>
</template>
<script>
import Chart from 'chart.js/auto'
import api from '../../api'
export default {
  name: 'LecturerAnalytics',
  async mounted() {
    // Example: fetch marks for a course and show average
    const res = await api.get('/courses/1/marks')
    const marks = res.data
    const labels = [...new Set(marks.map(m => m.component_name))]
    const data = labels.map(label => {
      const filtered = marks.filter(m => m.component_name === label)
      return filtered.reduce((sum, m) => sum + Number(m.mark), 0) / filtered.length
    })
    new Chart(document.getElementById('lecturerAnalyticsChart'), {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Average Mark', data }] }
    })
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
</style>
