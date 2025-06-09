<template>
  <div>
    <h3>Full Mark Breakdown</h3>
    <div class="card add-form">
      <form @submit.prevent="fetchMarks">
        <input v-model="course_id" placeholder="Course ID" required />
        <button type="submit">View Breakdown</button>
      </form>
    </div>
    <div class="card">
      <table v-if="marks.length" class="breakdown-table">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Component</th>
            <th>Mark</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mark in marks" :key="mark.id">
            <td>{{ mark.user_id }}</td>
            <td>{{ mark.component_name }}</td>
            <td>{{ mark.mark }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'FullMarkBreakdown',
  data() {
    return {
      course_id: '',
      marks: [],
      error: ''
    }
  },
  methods: {
    async fetchMarks() {
      this.error = ''
      try {
        const res = await api.get(`/courses/${this.course_id}/marks`)
        this.marks = res.data
      } catch (e) {
        this.error = 'Failed to load marks.'
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
}
.add-form {
  max-width: 400px;
}
.breakdown-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
}
.breakdown-table th, .breakdown-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.breakdown-table th {
  background: #f0f3f6;
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
