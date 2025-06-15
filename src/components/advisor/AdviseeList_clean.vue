<template>
  <div>
    <h3>Advisee Management</h3>
    
    <!-- Search and Filter Controls -->
    <div class="card controls-section">
      <div class="controls-header">
        <h4>Search & Filter Advisees</h4>
        <button @click="refreshAdvisees" class="refresh-btn">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
            <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
          </svg>
          Refresh
        </button>
      </div>
      
      <div class="controls-grid">
        <div class="search-box">
          <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search by name, student ID, or program..."
            class="search-input"
          >
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
          <div class="header-cell">GPA</div>
          <div class="header-cell">Status</div>
          <div class="header-cell">Last Meeting</div>
          <div class="header-cell">Actions</div>
        </div>
        
        <div v-if="loading" class="loading-state">
          <div class="loading-spinner"></div>
          <p>Loading advisees...</p>
        </div>
        
        <div v-else-if="filteredAdvisees.length === 0" class="empty-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h4>No advisees found</h4>
          <p>Try adjusting your search criteria or filters.</p>
        </div>
        
        <div v-else class="table-body">
          <div 
            v-for="advisee in paginatedAdvisees" 
            :key="advisee.id" 
            class="table-row"
            :class="{ 'at-risk': advisee.status === 'at-risk' }"
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
              <span class="status-badge" :class="advisee.status">
                {{ getStatusLabel(advisee.status) }}
              </span>
            </div>
            
            <div class="cell">
              <div class="meeting-info">
                <span class="meeting-date">{{ formatDate(advisee.lastMeeting) }}</span>
                <span class="meeting-type">{{ advisee.lastMeetingType }}</span>
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
                  @click="scheduleMeeting(advisee)" 
                  class="action-btn meeting-btn"
                  title="Schedule Meeting"
                >
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </button>
                
                <button 
                  @click="generateReport(advisee)" 
                  class="action-btn report-btn"
                  title="Generate Report"
                >
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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
      advisees: [
        {
          id: 1,
          name: 'Alice Johnson',
          studentId: 'CS2021001',
          program: 'CS',
          year: 3,
          gpa: 3.8,
          status: 'excellent',
          lastMeeting: '2024-01-15',
          lastMeetingType: 'Academic Review',
          email: 'alice.johnson@university.edu'
        },
        {
          id: 2,
          name: 'Bob Smith',
          studentId: 'IS2022003',
          program: 'IS',
          year: 2,
          gpa: 2.1,
          status: 'at-risk',
          lastMeeting: '2024-01-10',
          lastMeetingType: 'Support Meeting',
          email: 'bob.smith@university.edu'
        },
        {
          id: 3,
          name: 'Carol Davis',
          studentId: 'SE2020005',
          program: 'SE',
          year: 4,
          gpa: 3.2,
          status: 'active',
          lastMeeting: '2024-01-12',
          lastMeetingType: 'Career Planning',
          email: 'carol.davis@university.edu'
        },
        {
          id: 4,
          name: 'David Wilson',
          studentId: 'IT2023007',
          program: 'IT',
          year: 1,
          gpa: 1.8,
          status: 'probation',
          lastMeeting: '2024-01-08',
          lastMeetingType: 'Academic Warning',
          email: 'david.wilson@university.edu'
        },
        {
          id: 5,
          name: 'Emma Brown',
          studentId: 'CS2021009',
          program: 'CS',
          year: 3,
          gpa: 3.9,
          status: 'excellent',
          lastMeeting: '2024-01-14',
          lastMeetingType: 'Research Discussion',
          email: 'emma.brown@university.edu'
        }
      ]
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
    },
    atRiskCount() {
      return this.advisees.filter(advisee => advisee.status === 'at-risk' || advisee.status === 'probation').length;
    },
    excellentCount() {
      return this.advisees.filter(advisee => advisee.status === 'excellent').length;
    }
  },
  methods: {
    refreshAdvisees() {
      this.loading = true;
      // Simulate API call
      setTimeout(() => {
        this.loading = false;
      }, 1000);
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
        'excellent': 'Excellent Performance'
      };
      return labels[status] || status;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    },
    viewAdviseeDetails(advisee) {
      this.$router.push({ 
        name: 'AdvisorMarkBreakdown', 
        params: { studentId: advisee.studentId } 
      });
    },
    scheduleMapping(advisee) {
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
    },
    exportAdviseeList() {
      const csvContent = "data:text/csv;charset=utf-8," 
        + "Name,Student ID,Program,Year,GPA,Status,Last Meeting\n"
        + this.filteredAdvisees.map(advisee => 
            `${advisee.name},${advisee.studentId},${advisee.program},${advisee.year},${advisee.gpa},${advisee.status},${advisee.lastMeeting}`
          ).join("\n");
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "advisee_list.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
}
</script>

<style scoped>
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background: #e5e7eb;
  transform: translateY(-1px);
}

.controls-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 16px;
  align-items: end;
}

.search-box {
  position: relative;
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
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.filter-select {
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
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
  color: #1f2937;
  font-size: 20px;
  font-weight: 600;
}

.subtitle {
  margin: 4px 0 0 0;
  color: #6b7280;
  font-size: 14px;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.export-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.advisee-table {
  min-height: 400px;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 1.2fr 1fr;
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
  grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 1.2fr 1fr;
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
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6b7280;
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

.empty-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #374151;
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
