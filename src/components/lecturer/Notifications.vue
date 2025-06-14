<template>
  <div>
    <h3>Notifications</h3>
    
    <!-- Notification Stats -->
    <div class="card stats-section">
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-value">{{ unreadCount }}</div>
          <div class="stat-label">Unread</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ todayCount }}</div>
          <div class="stat-label">Today</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ totalCount }}</div>
          <div class="stat-label">Total</div>
        </div>
      </div>
    </div>

    <!-- Notification Controls -->
    <div class="card controls-section">
      <div class="controls-header">
        <div class="filter-controls">
          <select v-model="filterType" @change="applyFilters" class="filter-select">
            <option value="all">All Notifications</option>
            <option value="unread">Unread Only</option>
            <option value="read">Read Only</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
          </select>
          
          <select v-model="categoryFilter" @change="applyFilters" class="filter-select">
            <option value="all">All Categories</option>
            <option value="student">Student Related</option>
            <option value="assessment">Assessment Updates</option>
            <option value="system">System Notifications</option>
            <option value="enrollment">Enrollment Changes</option>
          </select>
        </div>
        
        <div class="action-controls">
          <button @click="markAllAsRead" :disabled="unreadCount === 0" class="mark-all-btn">
            Mark All Read ({{ unreadCount }})
          </button>
          <button @click="refreshNotifications" class="refresh-btn">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
              <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
            </svg>
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="card notifications-section">
      <div class="notifications-header">
        <h4>Notifications</h4>
        <span class="showing-count">Showing {{ filteredNotifications.length }} of {{ totalCount }}</span>
      </div>
      
      <div class="notifications-container" v-if="filteredNotifications.length > 0">
        <div 
          v-for="notification in paginatedNotifications" 
          :key="notification.id"
          class="notification-item"
          :class="{ 
            'unread': !notification.is_read,
            'urgent': notification.priority === 'high',
            'system': notification.category === 'system'
          }"
        >
          <div class="notification-icon">
            <svg v-if="notification.category === 'student'" class="icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
            </svg>
            <svg v-else-if="notification.category === 'assessment'" class="icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else-if="notification.category === 'enrollment'" class="icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
            </svg>
            <svg v-else class="icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2L3 7v11c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7l-7-5z"></path>
            </svg>
          </div>
          
          <div class="notification-content">
            <div class="notification-title">{{ notification.title || 'Notification' }}</div>
            <div class="notification-message">{{ notification.message }}</div>
            <div class="notification-meta">
              <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
              <span class="notification-category">{{ formatCategory(notification.category) }}</span>
            </div>
          </div>
          
          <div class="notification-actions">
            <button 
              v-if="!notification.is_read" 
              @click="markAsRead(notification.id)"
              class="read-btn"
              title="Mark as read"
            >
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
              </svg>
            </button>
            <button 
              @click="deleteNotification(notification.id)"
              class="delete-btn"
              title="Delete notification"
            >
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-content">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 0 0-15 0v5h5l-5 5-5-5h5V7a9.5 9.5 0 0 1 19 0v10z"></path>
          </svg>
          <h4>No Notifications</h4>
          <p>{{ getEmptyMessage() }}</p>
        </div>
      </div>
      
      <!-- Pagination -->
      <div class="pagination" v-if="totalPages > 1">
        <button 
          @click="currentPage--" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          Previous
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button 
          @click="currentPage++" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Error/Success Messages -->
    <div v-if="error" class="floating-message error">
      {{ error }}
    </div>
    <div v-if="success" class="floating-message success">
      {{ success }}
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
      filteredNotifications: [],
      filterType: 'all',
      categoryFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      error: '',
      success: ''
    }
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.is_read).length
    },
    todayCount() {
      const today = new Date().toDateString()
      return this.notifications.filter(n => 
        new Date(n.created_at).toDateString() === today
      ).length
    },
    totalCount() {
      return this.notifications.length
    },
    paginatedNotifications() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredNotifications.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredNotifications.length / this.itemsPerPage)
    }
  },
  methods: {
    async fetchNotifications() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get(`/users/${user.id}/notifications`)
        
        // Add mock data structure for better functionality
        this.notifications = res.data.map(notification => ({
          ...notification,
          category: notification.category || this.inferCategory(notification.message),
          priority: notification.priority || 'normal',
          title: notification.title || this.generateTitle(notification.message)
        }))
        
        this.applyFilters()
      } catch (e) {
        this.error = 'Failed to load notifications.'
      }
    },
    
    async refreshNotifications() {
      this.error = ''
      this.success = ''
      await this.fetchNotifications()
      this.success = 'Notifications refreshed!'
      setTimeout(() => this.success = '', 2000)
    },
    
    async markAsRead(id) {
      try {
        await api.put(`/notifications/${id}/read`)
        const notification = this.notifications.find(n => n.id === id)
        if (notification) {
          notification.is_read = true
        }
        this.applyFilters()
        this.success = 'Marked as read!'
        setTimeout(() => this.success = '', 2000)
      } catch (e) {
        this.error = 'Failed to mark as read.'
      }
    },
    
    async markAllAsRead() {
      try {
        const unreadIds = this.notifications
          .filter(n => !n.is_read)
          .map(n => n.id)
        
        await Promise.all(unreadIds.map(id => 
          api.put(`/notifications/${id}/read`)
        ))
        
        this.notifications.forEach(notification => {
          notification.is_read = true
        })
        
        this.applyFilters()
        this.success = 'All notifications marked as read!'
        setTimeout(() => this.success = '', 3000)
      } catch (e) {
        this.error = 'Failed to mark all as read.'
      }
    },
    
    async deleteNotification(id) {
      if (!confirm('Are you sure you want to delete this notification?')) return
      
      try {
        await api.delete(`/notifications/${id}`)
        this.notifications = this.notifications.filter(n => n.id !== id)
        this.applyFilters()
        this.success = 'Notification deleted!'
        setTimeout(() => this.success = '', 2000)
      } catch (e) {
        this.error = 'Failed to delete notification.'
      }
    },
    
    applyFilters() {
      let filtered = [...this.notifications]
      
      // Apply read/unread filter
      if (this.filterType === 'unread') {
        filtered = filtered.filter(n => !n.is_read)
      } else if (this.filterType === 'read') {
        filtered = filtered.filter(n => n.is_read)
      } else if (this.filterType === 'today') {
        const today = new Date().toDateString()
        filtered = filtered.filter(n => 
          new Date(n.created_at).toDateString() === today
        )
      } else if (this.filterType === 'week') {
        const weekAgo = new Date()
        weekAgo.setDate(weekAgo.getDate() - 7)
        filtered = filtered.filter(n => 
          new Date(n.created_at) >= weekAgo
        )
      }
      
      // Apply category filter
      if (this.categoryFilter !== 'all') {
        filtered = filtered.filter(n => n.category === this.categoryFilter)
      }
      
      // Sort by creation date (newest first)
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      
      this.filteredNotifications = filtered
      this.currentPage = 1
    },
    
    inferCategory(message) {
      if (message.includes('student') || message.includes('enrollment')) return 'student'
      if (message.includes('assessment') || message.includes('mark') || message.includes('exam')) return 'assessment'
      if (message.includes('enroll')) return 'enrollment'
      return 'system'
    },
    
    generateTitle(message) {
      if (message.includes('mark')) return 'Grade Update'
      if (message.includes('enroll')) return 'Enrollment Change'
      if (message.includes('assessment')) return 'Assessment Update'
      return 'System Notification'
    },
    
    formatTime(timestamp) {
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) return 'Just now'
      if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`
      if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`
      if (diff < 604800000) return `${Math.floor(diff/86400000)}d ago`
      
      return date.toLocaleDateString()
    },
    
    formatCategory(category) {
      const categories = {
        student: 'Student',
        assessment: 'Assessment',
        system: 'System',
        enrollment: 'Enrollment'
      }
      return categories[category] || 'General'
    },
    
    getEmptyMessage() {
      if (this.filterType === 'unread') return 'No unread notifications'
      if (this.filterType === 'today') return 'No notifications today'
      if (this.categoryFilter !== 'all') return `No ${this.categoryFilter} notifications`
      return 'You have no notifications'
    }
  },
  
  mounted() {
    this.fetchNotifications()
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.stat-item {
  text-align: center;
  padding: 16px;
  background: #F1FAEE;
  border-radius: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #1D3557;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
}

.controls-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.filter-controls {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.action-controls {
  display: flex;
  gap: 8px;
}

.mark-all-btn, .refresh-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.mark-all-btn {
  background: #457B9D;
  color: white;
}

.mark-all-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.refresh-btn {
  background: #E9ECEF;
  color: #495057;
}

.notifications-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.showing-count {
  font-size: 14px;
  color: #6c757d;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  padding: 16px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
}

.notification-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.notification-item.unread {
  background: #f8f9ff;
  border-left: 4px solid #457B9D;
}

.notification-item.urgent {
  border-left-color: #E63946;
}

.notification-item.system {
  border-left-color: #F77F00;
}

.notification-icon {
  margin-right: 12px;
  padding: 8px;
  background: #f1f3f4;
  border-radius: 50%;
  flex-shrink: 0;
}

.icon {
  width: 20px;
  height: 20px;
  color: #457B9D;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600;
  color: #1D3557;
  margin-bottom: 4px;
}

.notification-message {
  color: #495057;
  margin-bottom: 8px;
  line-height: 1.4;
}

.notification-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: #6c757d;
}

.notification-actions {
  display: flex;
  gap: 4px;
  margin-left: 12px;
}

.read-btn, .delete-btn {
  padding: 6px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.read-btn {
  background: #d4edda;
  color: #155724;
}

.delete-btn {
  background: #f8d7da;
  color: #721c24;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  opacity: 0.5;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.pagination-btn {
  padding: 8px 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background: white;
  cursor: pointer;
}

.pagination-btn:disabled {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

.page-info {
  font-size: 14px;
  color: #495057;
}

.floating-message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 4px;
  z-index: 1000;
  font-weight: 500;
}

.floating-message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.floating-message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>