<template>
  <div>
    <h3>Notifications</h3>
    <div class="card">
      <button @click="fetchNotifications">Load My Notifications</button>
      <ul class="notification-list">
        <li v-for="note in notifications" :key="note.id">
          {{ note.message }} <span v-if="!note.is_read">(unread)</span>
          <button v-if="!note.is_read" @click="markRead(note.id)">Mark as read</button>
        </li>
      </ul>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'LecturerNotifications',
  data() {
    return {
      notifications: [],
      error: ''
    }
  },
  methods: {
    async fetchNotifications() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get(`/users/${user.id}/notifications`)
        this.notifications = res.data
      } catch (e) {
        this.error = 'Failed to load notifications.'
      }
    },
    async markRead(id) {
      try {
        await api.put(`/notifications/${id}/read`)
        this.fetchNotifications()
      } catch (e) {
        this.error = 'Failed to mark as read.'
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
  max-width: 600px;
}
.notification-list {
  list-style: none;
  padding: 0;
  margin: 16px 0 0 0;
}
.notification-list li {
  padding: 10px 0;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
button {
  margin-left: 12px;
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