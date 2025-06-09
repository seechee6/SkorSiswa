<template>
  <div>
    <h3>Add Feedback/Remarks per Student</h3>
    <div class="card add-form">
      <h4>Submit Feedback/Remark</h4>
      <form @submit.prevent="submitFeedback">
        <input v-model="student_id" placeholder="Student User ID" required />
        <textarea v-model="message" placeholder="Feedback/Remark" required></textarea>
        <button type="submit">Submit Feedback</button>
      </form>
      <div v-if="success" class="success">Feedback submitted!</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
    <div class="card">
      <h4>Feedback/Remarks for Student</h4>
      <button @click="fetchFeedbacks" :disabled="!student_id">Load Feedback</button>
      <table v-if="feedbacks.length" class="feedback-table">
        <thead>
          <tr>
            <th>Message</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="fb in feedbacks" :key="fb.id">
            <td>{{ fb.message }}</td>
            <td>{{ new Date(fb.created_at).toLocaleString() }}</td>
            <td>
              <button @click="deleteFeedback(fb.id)" class="danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="feedbacks.length === 0 && feedbackLoaded" class="info">No feedback found for this student.</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'FeedbackRemarks',
  data() {
    return {
      student_id: '',
      message: '',
      success: false,
      error: '',
      feedbacks: [],
      feedbackLoaded: false
    }
  },
  methods: {
    async submitFeedback() {
      this.success = false
      this.error = ''
      try {
        await api.post(`/users/${this.student_id}/notifications`, {
          message: this.message
        })
        this.success = true
        this.student_id = ''
        this.message = ''
        this.feedbackLoaded = false
        this.feedbacks = []
      } catch (e) {
        this.error = 'Failed to submit feedback.'
      }
    },
    async fetchFeedbacks() {
      this.error = ''
      this.feedbackLoaded = false
      try {
        const res = await api.get(`/users/${this.student_id}/notifications`)
        this.feedbacks = res.data
        this.feedbackLoaded = true
      } catch (e) {
        this.error = 'Failed to load feedback.'
      }
    },
    async deleteFeedback(id) {
      this.error = ''
      try {
        await api.delete(`/notifications/${id}`)
        this.fetchFeedbacks()
      } catch (e) {
        this.error = 'Failed to delete feedback.'
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
.feedback-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
}
.feedback-table th, .feedback-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.feedback-table th {
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
input, textarea {
  margin: 6px 0;
  padding: 8px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
}
textarea {
  min-height: 60px;
  resize: vertical;
}
.success {
  color: #27ae60;
  margin-top: 8px;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}
.info {
  color: #888;
  margin-top: 8px;
}
</style>
