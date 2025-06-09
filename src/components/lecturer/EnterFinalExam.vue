<template>
  <div>
    <h3>Enter Final Exam Marks (30%)</h3>
    <div class="card add-form">
      <form @submit.prevent="submitMark">
        <input v-model="enrollment_id" placeholder="Enrollment ID" required />
        <input v-model="component_id" placeholder="Final Exam Component ID" required />
        <input v-model="mark" placeholder="Mark" required />
        <button type="submit">Submit Mark</button>
      </form>
      <div v-if="success" class="success">Mark submitted!</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'EnterFinalExam',
  data() {
    return {
      enrollment_id: '',
      component_id: '',
      mark: '',
      success: false,
      error: ''
    }
  },
  methods: {
    async submitMark() {
      this.success = false
      this.error = ''
      try {
        await api.post(`/enrollments/${this.enrollment_id}/marks`, {
          component_id: Number(this.component_id),
          mark: Number(this.mark)
        })
        this.success = true
        this.enrollment_id = ''
        this.component_id = ''
        this.mark = ''
      } catch (e) {
        this.error = 'Failed to submit mark.'
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
.success {
  color: #27ae60;
  margin-top: 8px;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}
</style>
