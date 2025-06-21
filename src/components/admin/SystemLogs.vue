<template>
  <div class="system-logs">
    <div class="header">
      <h2>System Activity Logs</h2>
      <div class="filter-actions">
        <select v-model="activityType" class="filter-select">
          <option value="">All Activities</option>
          <option value="user">User Management</option>
          <option value="course">Course Management</option>
          <option value="enrollment">Enrollment</option>
          <option value="grade">Grade Changes</option>
          <option value="system">System Events</option>
        </select>
        <input 
          type="date" 
          v-model="dateFilter"
          class="date-filter"
          :max="currentDate"
        >
        <button @click="clearFilters" class="btn-secondary">Clear Filters</button>
      </div>
    </div>

    <div class="logs-table">
      <table>        <thead>
          <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Activity Type</th>
            <th>Description</th>
            <th>Status</th>
          </tr>
        </thead>        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="loading-cell">
              <div class="loading-spinner">Loading system logs...</div>
            </td>
          </tr>
          <tr v-else-if="error">
            <td colspan="5" class="error-cell">
              <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                {{ error }}
                <button @click="fetchLogs" class="btn-retry">Retry</button>
              </div>
            </td>
          </tr>
          <tr v-else-if="logs.length === 0">
            <td colspan="5" class="no-logs-cell">
              <div class="no-logs-message">
                <i class="fas fa-inbox"></i>
                No system logs found
              </div>
            </td>
          </tr>
          <tr v-else v-for="log in filteredLogs" :key="log.id">
            <td>{{ formatDate(log.timestamp) }}</td>
            <td>{{ log.user }}</td>
            <td>
              <span :class="['activity-type', log.type]">{{ log.type }}</span>
            </td>
            <td>{{ log.description }}</td>
            <td>
              <span :class="['status', log.status]">{{ log.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <button 
        :disabled="currentPage === 1" 
        @click="changePage(currentPage - 1)"
        class="btn-page"
      >
        Previous
      </button>
      <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="changePage(currentPage + 1)"
        class="btn-page"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from '@/api'

export default {
  name: 'SystemLogs',
  data() {
    return {
      logs: [],
      loading: false,
      error: null,
      activityType: '',
      dateFilter: '',
      currentPage: 1,
      logsPerPage: 10,
      totalLogs: 0,
      currentDate: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    filteredLogs() {
      // Since we're doing server-side filtering now, just return logs
      return this.logs;
    },
    totalPages() {
      return Math.ceil(this.totalLogs / this.logsPerPage);
    }
  },
  watch: {
    // Watch for filter changes and refetch data
    activityType() {
      this.currentPage = 1;
      this.fetchLogs();
    },
    dateFilter() {
      this.currentPage = 1;
      this.fetchLogs();
    }
  },
  created() {
    this.fetchLogs();
  },
  methods: {
    async fetchLogs() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          limit: this.logsPerPage,
          offset: (this.currentPage - 1) * this.logsPerPage
        };
        
        if (this.activityType) {
          params.type = this.activityType;
        }
        
        if (this.dateFilter) {
          params.date = this.dateFilter;
        }
        
        const response = await axios.get('/api/admin/system-logs', { params });
        
        if (response.data.success) {
          this.logs = response.data.logs;
          this.totalLogs = response.data.total;
        } else {
          throw new Error(response.data.error || 'Failed to fetch system logs');
        }
      } catch (error) {
        console.error('Error fetching system logs:', error);
        this.error = error.response?.data?.error || error.message || 'Failed to fetch system logs';
        this.logs = [];
        this.totalLogs = 0;
      } finally {
        this.loading = false;
      }
    },    formatDate(timestamp) {
      return new Date(timestamp).toLocaleString();
    },
    changePage(page) {
      this.currentPage = page;
      this.fetchLogs();
    },
    clearFilters() {
      this.activityType = '';
      this.dateFilter = '';
      this.currentPage = 1;
      this.fetchLogs();
    }
  }
}
</script>

<style scoped>
.system-logs {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-actions {
  display: flex;
  gap: 10px;
}

.filter-select, .date-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.logs-table {
  overflow-x: auto;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.activity-type {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.9em;
}

.activity-type.user { background-color: #E3F2FD; color: #1565C0; }
.activity-type.course { background-color: #E8F5E9; color: #2E7D32; }
.activity-type.enrollment { background-color: #FFF3E0; color: #EF6C00; }
.activity-type.grade { background-color: #F3E5F5; color: #7B1FA2; }
.activity-type.system { background-color: #ECEFF1; color: #455A64; }

.status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.9em;
}

.status.success { background-color: #E8F5E9; color: #2E7D32; }
.status.warning { background-color: #FFF3E0; color: #EF6C00; }
.status.error { background-color: #FFEBEE; color: #C62828; }

.btn-review {
  background-color: #2196F3;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.reviewed-text {
  color: #4CAF50;
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
}

.btn-page {
  background-color: #f5f6fa;
  border: 1px solid #ddd;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #666;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

/* Loading and error states */
.loading-cell, .error-cell, .no-logs-cell {
  text-align: center;
  padding: 40px 20px;
}

.loading-spinner {
  color: #007bff;
  font-size: 16px;
}

.error-message {
  color: #dc3545;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn-retry {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 3px;
  cursor: pointer;
  font-size: 12px;
}

.btn-retry:hover {
  background-color: #c82333;
}

.no-logs-message {
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 16px;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 15px;
  }

  .filter-actions {
    flex-direction: column;
    width: 100%;
  }

  .filter-select, .date-filter {
    width: 100%;
  }
}
</style>
