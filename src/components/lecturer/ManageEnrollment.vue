<template>
  <div>
    <h3>Manage Student Enrollment</h3>
    <div class="card add-form">
      <h4>Enroll Student</h4>
      <form @submit.prevent="enrollStudent">
        <input v-model="user_id" placeholder="Student User ID" required />
        <input v-model="course_id" placeholder="Course ID" required />
        <button type="submit">Enroll</button>
      </form>
    </div>
    <div class="card">
      <table class="enrollment-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="enrollment in enrollments" :key="enrollment.id">
            <td>{{ enrollment.student_name }}</td>
            <td>{{ enrollment.course_name }}</td>
            <td>
              <button @click="removeEnrollment(enrollment.id)" class="danger">Remove</button>
            </td>
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
  name: 'ManageEnrollment',
  data() {
    return {
      enrollments: [],
      error: '',
      user_id: '',
      course_id: ''
    }
  },
  methods: {
    async fetchEnrollments() {
      try {
        const res = await api.get('/enrollments')
        this.enrollments = res.data
      } catch (e) {
        this.error = 'Failed to load enrollments.'
      }
    },
    async enrollStudent() {
      try {
        await api.post('/enrollments', {
          user_id: Number(this.user_id),
          course_id: Number(this.course_id)
        })
        this.user_id = ''
        this.course_id = ''
        this.fetchEnrollments()
      } catch (e) {
        this.error = 'Failed to enroll student.'
      }
    },
    async removeEnrollment(id) {
      try {
        await api.delete(`/enrollments/${id}`)
        this.fetchEnrollments()
      } catch (e) {
        this.error = 'Failed to remove enrollment.'
      }
    }
  },
  mounted() {
    this.fetchEnrollments()
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
.enrollment-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 16px;
}
.enrollment-table th, .enrollment-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.enrollment-table th {
  background: #f0f3f6;
}
button {
  margin-right: 8px;
  padding: 6px 14px;
  border: none;
  border-radius: 4px;
  background: #3498db;
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
}
button.danger {
  background: #e74c3c;
}
button:hover {
  background: #2980b9;
}
button.danger:hover {
  background: #c0392b;
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
.error {
  color: #e74c3c;
  margin-top: 8px;
}
</style>
