<template>
  <div class="advisee-list">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Advisee Management</h2>
        <p class="welcome-text">Manage and monitor your advisees' academic progress</p>
      </div>
      <div class="header-actions">
      </div>
    </div>
    
    <!-- Search and Filter Controls -->
    <div class="card controls-section">      <div class="controls-header">
        <h4>Search & Filter Advisees</h4>
      </div>
      
      <div class="controls-grid">
       <div class="search-row">
        <div class="search-box">
          <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search..."
            class="search-input"
          >
          </div>
        </div>
        
        <select v-model="statusFilter" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="at-risk">At-Risk</option>
          <option value="probation">Academic Probation</option>
          <option value="excellent">Excellent Performance</option>
        </select>
        
        <select v-model="yearFilter" class="filter-select">
          <option value="">All Years</option>
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
        
        <select v-model="programFilter" class="filter-select">
          <option value="">All Programs</option>
          <option value="CS">Computer Science</option>
          <option value="IS">Information Systems</option>
          <option value="SE">Software Engineering</option>
          <option value="IT">Information Technology</option>
        </select>
      </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ filteredAdvisees.length }}</div>
          <div class="stat-label">Total Advisees</div>
        </div>
      </div>
      
      <div class="stat-card at-risk">
        <div class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ atRiskCount }}</div>
          <div class="stat-label">At-Risk Students</div>
        </div>
      </div>
      
      <div class="stat-card excellent">
        <div class="stat-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ excellentCount }}</div>
          <div class="stat-label">Excellent Performance</div>
        </div>
      </div>
    </div>

    <!-- Advisee List -->
    <div class="card">
      <div class="card-header">
        <div class="header-section">
          <h4>Advisee List</h4>
          <p class="subtitle">{{ filteredAdvisees.length }} advisees found</p>
        </div>
        <div class="header-actions">
          <button @click="exportAdviseeList" class="export-btn">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 14H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h4a1.5 1.5 0 0 1 1.5 1.5v7.793L14.146 6.646a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L10.5 8.293V1.5z"/>
            </svg>
            Export List
          </button>
        </div>
      </div>
      
      <div class="advisee-table">
        <div class="table-header">
          <div class="header-cell">Student</div>
          <div class="header-cell">Program</div>
          <div class="header-cell">Year</div>
          <div class="header-cell">CGPA</div>
          <div class="header-cell">Status</div>
          <div class="header-cell">Actions</div>
        </div>
          <div v-if="loading" class="loading-state">
          <div class="loading-spinner"></div>
          <p>Loading advisees...</p>
        </div>
        
        <div v-else-if="error" class="error-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <h4>Error Loading Data</h4>
          <p>{{ error }}</p>
          <button @click="refreshAdvisees" class="retry-btn">
            Try Again
          </button>
        </div>
        
        <div v-else-if="filteredAdvisees.length === 0" class="empty-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h4>No advisees found</h4>
          <p>Try adjusting your search criteria or filters.</p>
        </div>
        
        <div v-else class="table-body">          <div 
            v-for="advisee in paginatedAdvisees" 
            :key="advisee.id" 
            class="table-row"
            :class="{ 
              'at-risk': advisee.isAtRisk || advisee.status === 'at-risk' || advisee.status === 'probation' || advisee.gpa <= 2.0,
              'excellent': advisee.status === 'excellent' 
            }"
          >
            <div class="cell student-cell">
              <div class="student-info">
                <div class="student-name">{{ advisee.name }}</div>
                <div class="student-id">{{ advisee.studentId }}</div>
              </div>
            </div>
            
            <div class="cell">
              <span class="program-badge" :class="advisee.program.toLowerCase()">
                {{ advisee.program }}
              </span>
            </div>
            
            <div class="cell">
              <span class="year-badge">Year {{ advisee.year }}</span>
            </div>
            
            <div class="cell">
              <div class="gpa-display">
                <span class="gpa-value" :class="getGpaClass(advisee.gpa)">
                  {{ advisee.gpa.toFixed(2) }}
                </span>
                <div class="gpa-bar">
                  <div 
                    class="gpa-fill" 
                    :style="{ width: (advisee.gpa / 4.0 * 100) + '%' }"
                    :class="getGpaClass(advisee.gpa)"
                  ></div>
                </div>
              </div>
            </div>
              <div class="cell">
              <div class="status-container">
                <span class="status-badge" :class="getEffectiveStatus(advisee)">
                  {{ getStatusLabel(getEffectiveStatus(advisee)) }}
                </span>
                <div v-if="(advisee.isAtRisk || advisee.status === 'at-risk' || advisee.status === 'probation' || advisee.gpa <= 2.0) && (advisee.atRiskReason && advisee.atRiskReason.length > 0 || advisee.gpa <= 2.0)" 
                     class="at-risk-reasons"
                     :title="getAtRiskReasons(advisee)">
                  <svg class="warning-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                  <span class="reason-text">{{ getAtRiskReasons(advisee) }}</span>
                </div>
              </div>
            </div>
              <div class="cell actions-cell">
              <div class="action-buttons">
                <button 
                  @click="viewAdviseeDetails(advisee)" 
                  class="action-btn view-btn"
                  title="View Details"
                >
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
                
                <button 
                  @click="viewOverallPerformance(advisee)" 
                  class="action-btn performance-btn"
                  title="View Overall Performance"
                >
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          @click="currentPage = 1" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          First
        </button>
        <button 
          @click="currentPage--" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          Previous
        </button>
        
        <div class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </div>
        
        <button 
          @click="currentPage++" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Next
        </button>
        <button 
          @click="currentPage = totalPages" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Last
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import advisorService from '../../services/advisor.js';
import auth from '../../utils/auth.js';

export default {
  name: 'AdviseeList',
  data() {
    return {
      loading: false,
      searchQuery: '',
      statusFilter: '',
      yearFilter: '',
      programFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      advisees: [],
      error: null,
      currentUser: null
    }
  },
  async created() {
    // Get current user from auth utility
    this.currentUser = auth.getCurrentUser();
    
    if (this.currentUser && this.currentUser.id) {
      // Check if user is an advisor
      if (!auth.isAdvisor()) {
        this.error = 'Access denied. This page is only accessible to advisors.';
        return;
      }
      await this.fetchAdvisees();
    } else {
      this.error = 'User not authenticated. Please log in.';
      // Optionally redirect to login
      this.$router.push('/');
    }
  },
  computed: {
    filteredAdvisees() {
      return this.advisees.filter(advisee => {
        const matchesSearch = !this.searchQuery || 
          advisee.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          advisee.studentId.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          advisee.program.toLowerCase().includes(this.searchQuery.toLowerCase());
        
        const matchesStatus = !this.statusFilter || advisee.status === this.statusFilter;
        const matchesYear = !this.yearFilter || advisee.year.toString() === this.yearFilter;
        const matchesProgram = !this.programFilter || advisee.program === this.programFilter;
        
        return matchesSearch && matchesStatus && matchesYear && matchesProgram;
      });
    },
    paginatedAdvisees() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredAdvisees.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredAdvisees.length / this.itemsPerPage);
    },    atRiskCount() {
      return this.advisees.filter(advisee => 
        advisee.isAtRisk || 
        advisee.status === 'at-risk' || 
        advisee.status === 'probation' ||
        advisee.gpa <= 2.0
      ).length;
    },
    excellentCount() {
      return this.advisees.filter(advisee => advisee.status === 'excellent').length;
    }
  },  methods: {
    async fetchAdvisees() {
      if (!this.currentUser || !this.currentUser.id) {
        this.error = 'User not authenticated';
        return;
      }

      this.loading = true;
      this.error = null;
      
      try {
        const response = await advisorService.getAdvisees(this.currentUser.id);
        
        if (response.success) {
          this.advisees = response.data || [];
        } else {
          this.error = response.message || 'Failed to fetch advisees';
        }
      } catch (error) {
        console.error('Error fetching advisees:', error);
        this.error = 'Failed to load advisee data. Please try again.';
        
        // If it's a network error, you might want to show a more specific message
        if (error.code === 'NETWORK_ERROR') {
          this.error = 'Network error. Please check your connection and try again.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    async refreshAdvisees() {
      await this.fetchAdvisees();
    },
    getGpaClass(gpa) {
      if (gpa >= 3.5) return 'excellent';
      if (gpa >= 3.0) return 'good';
      if (gpa >= 2.5) return 'average';
      return 'poor';
    },
    getStatusLabel(status) {
      const labels = {
        'active': 'Active',
        'at-risk': 'At-Risk',
        'probation': 'Academic Probation',
        'excellent': 'Excellent Performance',
        'low-gpa': 'Low CGPA (At-Risk)'
      };
      return labels[status] || status;
    },
    
    getEffectiveStatus(advisee) {
      // If CGPA is 2.0 or below, override status to at-risk
      if (advisee.gpa <= 2.0) {
        return 'at-risk';
      }
      return advisee.status;
    },
    
    getAtRiskReasons(advisee) {
      const reasons = [];
      
      // Add existing at-risk reasons
      if (advisee.atRiskReason && advisee.atRiskReason.length > 0) {
        reasons.push(...advisee.atRiskReason);
      }
      
      // Add low CGPA reason if applicable
      if (advisee.gpa <= 2.0) {
        reasons.push(`Low CGPA (${advisee.gpa.toFixed(2)})`);
      }
      
      return reasons.join(', ') || 'At-Risk Student';
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    },    viewAdviseeDetails(advisee) {
      this.$router.push({ 
        name: 'AdvisorMarkBreakdown', 
        params: { studentId: advisee.id }
      });
    },
    viewOverallPerformance(advisee) {
      this.$router.push({ 
        name: 'AdviseeOverallPerformance', 
        params: { studentId: advisee.id }
      });
    },scheduleMeeting(advisee) {
      this.$router.push({ 
        name: 'AdvisorMeetingNotes', 
        query: { studentId: advisee.studentId, action: 'schedule' } 
      });
    },
    generateReport(advisee) {
      this.$router.push({ 
        name: 'AdvisorAdviseeReports', 
        query: { studentId: advisee.studentId } 
      });
    },    exportAdviseeList() {
      if (this.filteredAdvisees.length === 0) {
        alert('No data to export');
        return;
      }
      
      try {
        advisorService.exportAdviseesToCSV(this.filteredAdvisees);
      } catch (error) {
        console.error('Error exporting data:', error);
        alert('Failed to export data. Please try again.');
      }
    }
  }
}
</script>

<style scoped>
.advisee-list {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Dashboard Header - Following lecturer pattern */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content h2 {
  color: #1D3557;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.welcome-text {
  color: #6c757d;
  font-size: 16px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
  overflow: hidden;
}

.controls-section {
  padding: 24px;
}

.controls-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.controls-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 18px;
  font-weight: 600;
}

.controls-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 16px;
  align-items: end;
}

.search-box {
  position: relative;
  width: 50%;
}
.search-row {
  display: flex;
  justify-content: flex-start;
}

.filters-row {
  display: flex;
  justify-content: flex-start;
}
.search-row,
  .filters-row {
    justify-content: stretch;
  }


.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.search-input {
  width: 100%;
  padding: 12px 12px 12px 44px;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #457B9D;
}

.filter-select {
  padding: 12px;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #457B9D;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-card.at-risk {
  border-left: 4px solid #ef4444;
}

.stat-card.excellent {
  border-left: 4px solid #10b981;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  color: #6b7280;
}

.stat-card.at-risk .stat-icon {
  background: #fef2f2;
  color: #ef4444;
}

.stat-card.excellent .stat-icon {
  background: #f0fdf4;
  color: #10b981;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.header-section h4 {
  margin: 0;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
}

.subtitle {
  margin: 4px 0 0 0;
  color: #6c757d;
  font-size: 14px;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #457B9D;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.export-btn:hover {
  background: #1D3557;
  transform: translateY(-1px);
}

.advisee-table {
  min-height: 400px;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 1.2fr 1.5fr;
  gap: 16px;
  padding: 16px 24px;
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.table-body {
  max-height: 600px;
  overflow-y: auto;
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 1.2fr 1.5fr;
  gap: 16px;
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s;
}

.table-row:hover {
  background: #f9fafb;
}

.table-row.at-risk {
  background: #fef2f2;
  border-left: 4px solid #ef4444;
}

.table-row.excellent {
  background: #f0f9ff;
  border-left: 4px solid #06b6d4;
}

.cell {
  display: flex;
  align-items: center;
  font-size: 14px;
}

.student-cell {
  flex-direction: column;
  align-items: flex-start;
}

.student-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2px;
}

.student-id {
  color: #6b7280;
  font-size: 12px;
}

.program-badge {
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.program-badge.cs {
  background: #dbeafe;
  color: #1d4ed8;
}

.program-badge.is {
  background: #f3e8ff;
  color: #7c3aed;
}

.program-badge.se {
  background: #fef3c7;
  color: #d97706;
}

.program-badge.it {
  background: #dcfce7;
  color: #16a34a;
}

.year-badge {
  padding: 4px 8px;
  background: #f3f4f6;
  border-radius: 6px;
  font-size: 12px;
  color: #374151;
}

.gpa-display {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 100%;
}

.gpa-value {
  font-weight: 600;
  font-size: 16px;
}

.gpa-value.excellent {
  color: #10b981;
}

.gpa-value.good {
  color: #3b82f6;
}

.gpa-value.average {
  color: #f59e0b;
}

.gpa-value.poor {
  color: #ef4444;
}

.gpa-bar {
  height: 4px;
  background: #e5e7eb;
  border-radius: 2px;
  overflow: hidden;
}

.gpa-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.3s;
}

.gpa-fill.excellent {
  background: #10b981;
}

.gpa-fill.good {
  background: #3b82f6;
}

.gpa-fill.average {
  background: #f59e0b;
}

.gpa-fill.poor {
  background: #ef4444;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #e0f2fe;
  color: #0277bd;
}

.status-badge.at-risk {
  background: #fef2f2;
  color: #dc2626;
}

.status-badge.probation {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.excellent {
  background: #f0fdf4;
  color: #16a34a;
}

.meeting-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meeting-date {
  font-weight: 500;
  color: #1f2937;
}

.meeting-type {
  font-size: 12px;
  color: #6b7280;
}

.actions-cell {
  justify-content: center;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.view-btn {
  background: #e0f2fe;
  color: #0277bd;
}

.view-btn:hover {
  background: #b3e5fc;
  transform: scale(1.1);
}

.performance-btn {
  background: #fef3c7;
  color: #d97706;
}

.performance-btn:hover {
  background: #fde68a;
  transform: scale(1.1);
}

.meeting-btn {
  background: #f3e8ff;
  color: #7c3aed;
}

.meeting-btn:hover {
  background: #e9d5ff;
  transform: scale(1.1);
}

.report-btn {
  background: #f0fdf4;
  color: #16a34a;
}

.report-btn:hover {
  background: #dcfce7;
  transform: scale(1.1);
}

.loading-state,
.empty-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6b7280;
}

.error-state {
  color: #dc2626;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state svg,
.error-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
}

.empty-state h4,
.error-state h4 {
  margin: 0 0 8px 0;
  color: #374151;
}

.error-state h4 {
  color: #dc2626;
}

.retry-btn {
  margin-top: 16px;
  padding: 10px 20px;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background: #b91c1c;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}

.pagination-btn {
  padding: 8px 16px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #6b7280;
  font-size: 14px;
  margin: 0 16px;
}

.status-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.at-risk-reasons {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #dc2626;
  background: #fef2f2;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid #fecaca;
}

.warning-icon {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.reason-text {
  font-weight: 500;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .controls-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .table-header,
  .table-row {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .header-actions {
    width: 100%;
  }
  
  .export-btn {
    width: 100%;
  }
}
</style>
