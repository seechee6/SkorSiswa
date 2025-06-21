<template>
  <div class="notifications-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Notifications</h2>
        <p class="subtitle">Stay updated with your latest marks and course announcements</p>
      </div>
      <div class="header-actions">
        <button 
          v-if="unreadCount > 0" 
          @click="markAllAsRead" 
          class="mark-all-read-btn"
          :disabled="loading"
        >
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
          </svg>
          Mark All as Read
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading notifications...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-message">
      <div class="error-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <h3>Something went wrong</h3>
      <p>{{ error }}</p>
      <button class="retry-button" @click="fetchNotifications">
        Try Again
      </button>
    </div>

    <!-- Notifications List -->
    <div v-else class="notifications-content">
      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="card summary-card">
          <div class="card-icon total-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ notifications.length }}</div>
            <div class="card-label">Total Notifications</div>
          </div>
        </div>
        
        <div class="card summary-card">
          <div class="card-icon unread-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ unreadCount }}</div>
            <div class="card-label">Unread</div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="notifications.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 17h5l-5 5v-5zM21 12H3M21 6H3"></path>
          </svg>
        </div>
        <h3>No Notifications</h3>
        <p>You don't have any notifications yet. Check back later for updates on your marks and courses.</p>
      </div>

      <!-- Notifications List -->
      <div v-else class="notifications-list">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          :class="['notification-item', { 'unread': !notification.is_read }]"
          @click="markAsRead(notification.id)"
        >
          <div class="notification-content">
            <div class="notification-icon">
              <svg v-if="isMarkNotification(notification.message)" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="notification-text">
              <p class="notification-message">{{ notification.message }}</p>
              <p class="notification-time">{{ formatTime(notification.created_at) }}</p>
            </div>
          </div>
          <div v-if="!notification.is_read" class="unread-indicator"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'StudentNotifications',
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      loading: false,
      error: ''
    }
  },
  methods: {
    async fetchNotifications() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/student/notifications/${user.id}`)
        const data = response.data
        
        if (data.success) {
          this.notifications = data.notifications
          this.unreadCount = data.unread_count
        } else {
          this.error = data.error || 'Failed to load notifications'
        }
      } catch (error) {
        this.error = 'Failed to load notifications'
        console.error('Error fetching notifications:', error)
      } finally {
        this.loading = false
      }
    },
    
    async markAsRead(notificationId) {
      const notification = this.notifications.find(n => n.id === notificationId)
      if (!notification || notification.is_read) return
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post(`/student/notifications/${user.id}/mark-read`, {
          notification_ids: [notificationId]
        })
        
        // Update local state
        notification.is_read = true
        this.unreadCount = Math.max(0, this.unreadCount - 1)
      } catch (error) {
        console.error('Error marking notification as read:', error)
      }
    },
    
    async markAllAsRead() {
      if (this.unreadCount === 0) return
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post(`/student/notifications/${user.id}/mark-read`)
        
        // Update local state
        this.notifications.forEach(notification => {
          notification.is_read = true
        })
        this.unreadCount = 0
      } catch (error) {
        console.error('Error marking all notifications as read:', error)
      }
    },
    
    isMarkNotification(message) {
      return message.includes('mark') || message.includes('Mark')
    },
    
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diffMs = now - date
      const diffMinutes = Math.floor(diffMs / 60000)
      const diffHours = Math.floor(diffMinutes / 60)
      const diffDays = Math.floor(diffHours / 24)
      
      if (diffMinutes < 1) {
        return 'Just now'
      } else if (diffMinutes < 60) {
        return `${diffMinutes} minutes ago`
      } else if (diffHours < 24) {
        return `${diffHours} hours ago`
      } else if (diffDays < 7) {
        return `${diffDays} days ago`
      } else {
        return date.toLocaleDateString()
      }
    }
  },
  
  mounted() {
    this.fetchNotifications()
  }
}
</script>

<style scoped>
.notifications-container {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.header-content h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  text-align: left;
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
}

.mark-all-read-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: #4c51bf;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.mark-all-read-btn:hover:not(:disabled) {
  background-color: #4c51bf;
  transform: translateY(-1px);
}

.mark-all-read-btn:disabled {
  background-color: #a0aec0;
  cursor: not-allowed;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.summary-card {
  display: flex;
  align-items: center;
  padding: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  margin-right: 16px;
}

.card-icon svg {
  width: 28px;
  height: 28px;
}

.total-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.unread-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 4px;
}

.card-label {
  font-size: 0.85rem;
  color: #718096;
}

/* Loading & Error States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: #718096;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(66, 153, 225, 0.3);
  border-radius: 50%;
  border-top-color: #4c51bf;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #718096;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e0;
}

.error-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #e53e3e;
}

.error-icon svg {
  width: 100%;
  height: 100%;
}

.error-message h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #4a5568;
}

.error-message p {
  font-size: 0.95rem;
  text-align: center;
  max-width: 400px;
  margin: 0 0 16px 0;
}

.retry-button {
  background-color: #4c51bf;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.retry-button:hover {
  background-color: #4c51bf;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #718096;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e0;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #a0aec0;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #4a5568;
}

.empty-state p {
  font-size: 0.95rem;
  text-align: center;
  max-width: 400px;
  margin: 0;
}

/* Notifications List */
.notifications-list {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.notification-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid #edf2f7;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background-color: #f8fafc;
}

.notification-item.unread {
  background-color: #fef5e7;
  border-left: 4px solid #f6ad55;
}

.notification-content {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  flex: 1;
}

.notification-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: #e6fffa;
  color: #319795;
  flex-shrink: 0;
}

.notification-icon svg {
  width: 20px;
  height: 20px;
}

.notification-text {
  flex: 1;
}

.notification-message {
  font-size: 0.9rem;
  color: #2d3748;
  margin: 0 0 4px 0;
  line-height: 1.4;
}

.notification-time {
  font-size: 0.75rem;
  color: #718096;
  margin: 0;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  background-color: #f6ad55;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header-actions {
    margin-top: 16px;
    width: 100%;
  }
  
  .mark-all-read-btn {
    width: 100%;
    justify-content: center;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
  }
  
  .notification-item {
    padding: 12px;
  }
  
  .notification-content {
    gap: 8px;
  }
  
  .notification-icon {
    width: 32px;
    height: 32px;
  }
  
  .notification-icon svg {
    width: 16px;
    height: 16px;
  }
}
</style>
