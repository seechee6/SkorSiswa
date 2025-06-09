<template>
  <div>
    <h3>Export Marks as CSV</h3>
    <div class="card add-form">
      <form @submit.prevent="exportCSV">
        <input v-model="course_id" placeholder="Course ID" required />
        <button type="submit">Download CSV</button>
      </form>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'ExportCSV',
  data() {
    return {
      course_id: '',
      error: ''
    }
  },
  methods: {
    async exportCSV() {
      this.error = ''
      try {
        const res = await api.get(`/courses/${this.course_id}/marks`)
        const marks = res.data
        if (!marks.length) {
          this.error = 'No marks found.'
          return
        }
        const header = Object.keys(marks[0]).join(',')
        const rows = marks.map(row => Object.values(row).join(','))
        const csv = [header, ...rows].join('\n')
        const blob = new Blob([csv], { type: 'text/csv' })
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `course_${this.course_id}_marks.csv`
        a.click()
        window.URL.revokeObjectURL(url)
      } catch (e) {
        this.error = 'Failed to export CSV.'
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
  max-width: 400px;
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
