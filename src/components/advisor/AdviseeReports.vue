<template>
  <div>
    <!-- Notification -->
    <div v-if="notification.show" :class="['notification', notification.type]">
      {{ notification.message }}
      <button @click="notification.show = false" class="close-btn">&times;</button>
    </div>
    
    <h3>Advisee Reports & Analytics</h3>
    
    <!-- Student Selection -->
    <div class="card student-selection">
      <div class="selection-header">
        <h4>Generate Report For</h4>
        <div class="selection-actions">
          <button @click="generateBulkReport" class="action-btn primary" :disabled="selectedStudents.length === 0">
            <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
            Generate Bulk Report ({{ selectedStudents.length }})
          </button>
          <button @click="refreshStudentList" class="action-btn secondary">
            <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            Refresh
          </button>
        </div>
      </div>
      
      <div class="selection-controls">
        <div class="search-box">
          <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search advisees..."
            class="search-input"
          >
        </div>
        
        <select v-model="filterProgram" class="filter-select">
          <option value="">All Programs</option>
          <option value="CS">Computer Science</option>
          <option value="IS">Information Systems</option>
          <option value="SE">Software Engineering</option>
          <option value="IT">Information Technology</option>
        </select>
        
        <select v-model="filterYear" class="filter-select">
          <option value="">All Years</option>
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
        
        <button @click="selectAllVisible" class="action-btn outline">
          {{ allVisibleSelected ? 'Deselect All' : 'Select All' }}
        </button>
      </div>      <div class="students-grid" v-if="!loading">
        <div v-if="error" class="error-message">
          {{ error }}
          <button @click="fetchStudents" class="retry-btn">Retry</button>
        </div>
        
        <div v-else-if="filteredStudents.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <p>No advisees found</p>
        </div>
        
        <div 
          v-else
          v-for="student in filteredStudents" 
          :key="student.id"
          class="student-card"
          :class="{ selected: selectedStudents.includes(student.id) }"
          @click="toggleStudentSelection(student.id)"
        >
          <div class="student-header">
            <div class="student-avatar">
              <input 
                type="checkbox" 
                :checked="selectedStudents.includes(student.id)"
                @click.stop
                @change="toggleStudentSelection(student.id)"
                class="student-checkbox"
              >
              <span class="avatar-text">{{ getInitials(student.name) }}</span>
            </div>            <div class="student-info">
              <h5 class="student-name">{{ student.name }}</h5>
              <div class="student-details">
                <span class="student-id">{{ student.studentId }}</span>
                <span class="student-program">{{ student.program }} Year {{ student.year }}</span>
              </div>
            </div>
          </div>
          
          <div class="student-metrics">
            <div class="metric-item">
              <span class="metric-label">GPA</span>
              <span class="metric-value" :class="getGPAClass(student.gpa)">{{ student.gpa }}</span>
            </div>
            <div class="metric-item">
              <span class="metric-label">Status</span>
              <span class="status-badge" :class="student.status?.toLowerCase().replace(' ', '-')">
                {{ student.status }}
              </span>            </div>
          </div>
        </div>
      </div>
      
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
        <p>Loading advisees...</p>
      </div>
    </div>

    <!-- Report Configuration -->
    <div class="card report-config">
      <h4>Report Configuration</h4>
      
      <div class="config-grid">
        <div class="config-section">
          <h5>Report Type</h5>
          <div class="report-types">
            <label class="report-type-option">
              <input type="radio" v-model="reportType" value="comprehensive" />
              <div class="option-content">
                <div class="option-title">Comprehensive Report</div>
                <div class="option-description">Complete academic performance with all details</div>
              </div>
            </label>
            
            <label class="report-type-option">
              <input type="radio" v-model="reportType" value="summary" />
              <div class="option-content">
                <div class="option-title">Summary Report</div>
                <div class="option-description">Key metrics and performance overview</div>
              </div>
            </label>
            
            <label class="report-type-option">
              <input type="radio" v-model="reportType" value="at-risk" />
              <div class="option-content">
                <div class="option-title">At-Risk Analysis</div>
                <div class="option-description">Focus on struggling students and interventions</div>
              </div>
            </label>
            
            <label class="report-type-option">
              <input type="radio" v-model="reportType" value="progress" />
              <div class="option-content">
                <div class="option-title">Progress Tracking</div>
                <div class="option-description">Semester-by-semester progress analysis</div>
              </div>
            </label>
          </div>
        </div>

        <div class="config-section">
          <h5>Include Sections</h5>
          <div class="checkbox-group">
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.academicPerformance" />
              <span>Academic Performance & GPA Trends</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.courseBreakdown" />
              <span>Course-by-Course Breakdown</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.attendanceAnalysis" />
              <span>Attendance Analysis</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.meetingHistory" />
              <span>Advisor Meeting History</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.riskAssessment" />
              <span>Risk Assessment & Interventions</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.recommendations" />
              <span>Recommendations & Action Items</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.comparativeAnalysis" />
              <span>Peer Comparison Analysis</span>
            </label>
            <label class="checkbox-option">
              <input type="checkbox" v-model="reportSections.charts" />
              <span>Visual Charts & Graphs</span>
            </label>
          </div>
        </div>

        <div class="config-section">
          <h5>Time Period</h5>
          <div class="period-controls">
            <select v-model="timePeriod" class="period-select">
              <option value="current">Current Semester</option>
              <option value="academic-year">Full Academic Year</option>
              <option value="all-time">All Time</option>
              <option value="custom">Custom Period</option>
            </select>
            
            <div v-if="timePeriod === 'custom'" class="custom-period">
              <input 
                type="date" 
                v-model="customPeriod.start" 
                class="date-input"
                placeholder="Start Date"
              />
              <span class="date-separator">to</span>
              <input 
                type="date" 
                v-model="customPeriod.end" 
                class="date-input"
                placeholder="End Date"
              />
            </div>
          </div>
        </div>

        <div class="config-section">
          <h5>Output Format</h5>
          <div class="format-options">
            <label class="format-option">
              <input type="radio" v-model="outputFormat" value="pdf" />
              <svg class="format-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
              </svg>
              <span>PDF Report</span>
            </label>
            
            <label class="format-option">
              <input type="radio" v-model="outputFormat" value="excel" />
              <svg class="format-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
              </svg>
              <span>Excel Spreadsheet</span>
            </label>
            
            <label class="format-option">
              <input type="radio" v-model="outputFormat" value="html" />
              <svg class="format-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              <span>HTML Report</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Preview & Generation -->
    <div class="card report-actions">
      <div class="actions-header">
        <h4>Generate Report</h4>
        <div class="report-summary">
          <span class="summary-item">
            <strong>{{ selectedStudents.length }}</strong> student{{ selectedStudents.length !== 1 ? 's' : '' }} selected
          </span>
          <span class="summary-item">
            <strong>{{ reportType }}</strong> report type
          </span>
          <span class="summary-item">
            <strong>{{ outputFormat.toUpperCase() }}</strong> format
          </span>
        </div>
      </div>
      
      <div class="action-buttons">
        <button 
          @click="previewReport" 
          class="action-btn secondary large"
          :disabled="selectedStudents.length === 0"
        >
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
          </svg>
          Preview Report
        </button>
        
        <button 
          @click="generateReport" 
          class="action-btn primary large"
          :disabled="selectedStudents.length === 0 || generating"
        >
          <svg v-if="!generating" class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
          <svg v-else class="btn-icon animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ generating ? 'Generating...' : 'Generate Report' }}
        </button>
        
        <button 
          @click="scheduleReport" 
          class="action-btn outline large"
        >
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
          </svg>
          Schedule Recurring
        </button>
      </div>
    </div>

    <!-- Recent Reports -->
    <div class="card recent-reports">
      <div class="reports-header">
        <h4>Recent Reports</h4>
        <button @click="refreshRecentReports" class="action-btn outline">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
          </svg>
          Refresh
        </button>
      </div>
      
      <div class="reports-list" v-if="recentReports.length > 0">
        <div v-for="report in recentReports" :key="report.id" class="report-item">
          <div class="report-info">
            <div class="report-title">{{ report.title }}</div>
            <div class="report-details">
              <span class="report-date">{{ formatDate(report.created_at) }}</span>
              <span class="report-type">{{ report.type }}</span>
              <span class="report-students">{{ report.student_count }} students</span>
            </div>
          </div>
          <div class="report-actions">
            <button @click="downloadReport(report)" class="action-btn small secondary">
              <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              Download
            </button>
            <button @click="shareReport(report)" class="action-btn small outline">
              <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path>
              </svg>
              Share
            </button>
          </div>
        </div>
      </div>
      
      <div v-else class="empty-state">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>No reports generated yet.</p>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api'

export default {
  name: 'AdviseeReports',
  data() {
    return {
      students: [],
      selectedStudents: [],
      searchQuery: '',
      filterProgram: '',
      filterYear: '',
      reportType: 'comprehensive',
      reportSections: {
        academicPerformance: true,
        courseBreakdown: true,
        attendanceAnalysis: false,
        meetingHistory: false,
        riskAssessment: true,
        recommendations: true,
        comparativeAnalysis: false,
        charts: true
      },
      timePeriod: 'current',
      customPeriod: {
        start: '',
        end: ''
      },
      outputFormat: 'pdf',
      generating: false,      recentReports: [],
      loading: false,
      error: '',
      notification: {
        show: false,
        message: '',
        type: 'success' // success, error, info
      }
    }
  },  computed: {
    filteredStudents() {
      let filtered = this.students
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(student => 
          student.name?.toLowerCase().includes(query) ||
          student.studentId?.toLowerCase().includes(query)
        )
      }

      if (this.filterProgram) {
        filtered = filtered.filter(student => 
          student.program === this.filterProgram
        )
      }

      if (this.filterYear) {
        filtered = filtered.filter(student => 
          student.year === parseInt(this.filterYear)
        )
      }

      return filtered
    },
    allVisibleSelected() {
      return this.filteredStudents.length > 0 && 
             this.filteredStudents.every(s => this.selectedStudents.includes(s.id))
    }
  },
  async mounted() {
    await this.fetchStudents()
    await this.fetchRecentReports()
    
    // Handle pre-selected student from URL
    const urlParams = new URLSearchParams(window.location.search)
    const studentId = urlParams.get('student')
    if (studentId) {
      this.selectedStudents = [parseInt(studentId)]
    }
  },
  methods: {    async fetchStudents() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/advisors/${user.id}/advisees`)
        
        if (response.data?.success) {
          this.students = response.data.data || []
        } else {
          this.error = response.data?.message || 'Failed to fetch students'
          this.students = []
        }
      } catch (error) {
        console.error('Error fetching students:', error)
        this.error = 'Failed to fetch students. Please try again.'
        this.students = []      } finally {
        this.loading = false
      }
    },
    
    async fetchRecentReports() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/advisors/${user.id}/reports/recent`)
        
        if (response.data?.success) {
          this.recentReports = response.data.data || []
        } else {
          this.recentReports = []
        }
      } catch (error) {
        console.error('Error fetching recent reports:', error)
        this.recentReports = []
      }
    },
    
    refreshStudentList() {
      this.fetchStudents()
    },
    
    refreshRecentReports() {
      this.fetchRecentReports()
    },
    
    toggleStudentSelection(studentId) {
      const index = this.selectedStudents.indexOf(studentId)
      if (index > -1) {
        this.selectedStudents.splice(index, 1)
      } else {
        this.selectedStudents.push(studentId)
      }
    },
    
    selectAllVisible() {
      if (this.allVisibleSelected) {
        this.selectedStudents = this.selectedStudents.filter(id => 
          !this.filteredStudents.some(s => s.id === id)
        )
      } else {
        const newSelections = this.filteredStudents
          .filter(s => !this.selectedStudents.includes(s.id))
          .map(s => s.id)
        this.selectedStudents.push(...newSelections)
      }
    },
    
    getInitials(name) {
      return name ? name.split(' ').map(n => n[0]).join('').toUpperCase() : 'NA'
    },
    
    getGPAClass(gpa) {
      const numGpa = parseFloat(gpa)
      if (numGpa >= 3.5) return 'excellent'
      if (numGpa >= 3.0) return 'good'
      if (numGpa >= 2.5) return 'average'
      return 'poor'
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },    previewReport() {
      if (this.selectedStudents.length === 0) {
        this.showNotification('Please select at least one student to preview the report.', 'info')
        return
      }
      
      const message = `Report Preview: ${this.reportType} report for ${this.selectedStudents.length} student(s) in ${this.outputFormat.toUpperCase()} format`
      this.showNotification(message, 'info')
    },
      async generateReport() {
      this.generating = true
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const reportData = {
          students: this.selectedStudents,
          type: this.reportType,
          sections: this.reportSections,
          period: this.timePeriod,
          format: this.outputFormat
        }
        
        const response = await api.post(`/advisors/${user.id}/reports/generate`, reportData)
          if (response.data?.success) {
          // Success - show success message
          this.showNotification('Report generated successfully!', 'success')
          
          // Download the report
          if (response.data.download_url) {
            window.open(`${api.defaults.baseURL}${response.data.download_url}`, '_blank')
          }
          
          // Refresh recent reports
          await this.fetchRecentReports()
          
          // Clear selections
          this.selectedStudents = []
        } else {
          throw new Error(response.data?.message || 'Failed to generate report')
        }      } catch (error) {
        console.error('Error generating report:', error)
        this.showNotification(error.message || 'Error generating report. Please try again.', 'error')
      } finally {
        this.generating = false
      }
    },
    
    generateBulkReport() {
      if (this.selectedStudents.length === 0) return
      this.generateReport()
    },
      scheduleReport() {
      this.showNotification('Report scheduling feature will be implemented soon.', 'info')
    },
      downloadReport(report) {
      const user = JSON.parse(localStorage.getItem('user'))
      const downloadUrl = `${api.defaults.baseURL}/advisors/${user.id}/reports/${report.id}/download`
      window.open(downloadUrl, '_blank')
    },    shareReport(report) {
      // Simple share functionality - copy link to clipboard
      const shareUrl = `${window.location.origin}/advisor/reports?id=${report.id}`
      navigator.clipboard.writeText(shareUrl).then(() => {
        this.showNotification('Report link copied to clipboard!', 'success')
      }).catch(() => {
        this.showNotification('Unable to copy link. Please try again.', 'error')
      })
    },
    
    showNotification(message, type = 'success') {
      this.notification = {
        show: true,
        message,
        type
      }
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        this.notification.show = false
      }, 5000)
    }
  }
}
</script>

<style scoped>
/* Base Styles */
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 20px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 400px;
}

.notification.success {
  background: #28a745;
}

.notification.error {
  background: #dc3545;
}

.notification.info {
  background: #17a2b8;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
  padding: 0;
  margin-left: auto;
}

.close-btn:hover {
  opacity: 0.8;
}

h3 {
  color: #1D3557;
  margin-bottom: 24px;
  font-size: 24px;
  font-weight: 600;
}

h4 {
  color: #1D3557;
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 600;
}

h5 {
  color: #1D3557;
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Student Selection */
.selection-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.selection-actions {
  display: flex;
  gap: 12px;
}

.selection-controls {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
}

.search-box {
  position: relative;
  width: 50%
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #6c757d;
}

.search-input {
  width: 100%;
  padding: 12px 12px 12px 44px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
}

.filter-select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
  background: white;
}

.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.student-card {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.student-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}

.student-card.selected {
  border-color: #457B9D;
  box-shadow: 0 0 0 2px rgba(69, 123, 157, 0.2);
}

.student-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.student-avatar {
  position: relative;
  width: 40px;
  height: 40px;
  background: #457B9D;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.student-checkbox {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 16px;
  height: 16px;
}

.student-name {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #1D3557;
}

.student-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.student-id {
  font-size: 12px;
  color: #6c757d;
}

.student-program {
  font-size: 12px;
  color: #6c757d;
}

.student-metrics {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.metric-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.metric-label {
  font-size: 10px;
  color: #6c757d;
  text-transform: uppercase;
  font-weight: 500;
}

.metric-value {
  font-size: 14px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
}

.metric-value.excellent { background: #d4edda; color: #155724; }
.metric-value.good { background: #d1ecf1; color: #0c5460; }
.metric-value.average { background: #fff3cd; color: #856404; }
.metric-value.poor { background: #f8d7da; color: #721c24; }

.status-badge {
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-badge.active { background: #d4edda; color: #155724; }
.status-badge.at-risk { background: #f8d7da; color: #721c24; }
.status-badge.academic-probation { background: #ffeaa7; color: #e17055; }
.status-badge.excellent-performance { background: #a8e6cf; color: #2d3436; }

/* Report Configuration */
.config-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
}

.config-section {
  display: flex;
  flex-direction: column;
}

.report-types {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.report-type-option {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.report-type-option:hover {
  border-color: #457B9D;
  background: #f8f9fa;
}

.report-type-option input[type="radio"] {
  margin-top: 2px;
}

.option-content {
  flex: 1;
}

.option-title {
  font-weight: 600;
  color: #1D3557;
  margin-bottom: 4px;
}

.option-description {
  font-size: 14px;
  color: #6c757d;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-option input[type="checkbox"] {
  width: 16px;
  height: 16px;
}

.period-controls {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.period-select {
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
  background: white;
}

.custom-period {
  display: flex;
  align-items: center;
  gap: 12px;
}

.date-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
}

.date-separator {
  color: #6c757d;
  font-size: 14px;
}

.format-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.format-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.format-option:hover {
  border-color: #457B9D;
  background: #f8f9fa;
}

.format-icon {
  width: 20px;
  height: 20px;
  color: #6c757d;
}

/* Action Buttons */
.action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  text-decoration: none;
  font-size: 14px;
}

.action-btn.large {
  padding: 12px 24px;
  font-size: 16px;
}

.action-btn.small {
  padding: 6px 12px;
  font-size: 12px;
}

.action-btn.primary {
  background: #457B9D;
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  background: #3a6b8a;
}

.action-btn.secondary {
  background: #F1FAEE;
  color: #1D3557;
}

.action-btn.secondary:hover {
  background: #e9ecef;
}

.action-btn.outline {
  background: transparent;
  color: #6c757d;
  border: 1px solid #dee2e6;
}

.action-btn.outline:hover {
  background: #f8f9fa;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Report Actions */
.actions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.report-summary {
  display: flex;
  gap: 16px;
}

.summary-item {
  font-size: 14px;
  color: #6c757d;
}

.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

/* Recent Reports */
.reports-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.reports-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.report-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  transition: all 0.2s;
}

.report-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.report-info {
  flex: 1;
}

.report-title {
  font-weight: 600;
  color: #1D3557;
  margin-bottom: 4px;
}

.report-details {
  display: flex;
  gap: 16px;
  font-size: 14px;
  color: #6c757d;
}

.report-actions {
  display: flex;
  gap: 8px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

/* Loading Spinner */
.loading-spinner {
  text-align: center;
  padding: 48px 24px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 16px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #457B9D;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Error Message */
.error-message {
  text-align: center;
  padding: 24px;
  background: #f8d7da;
  color: #721c24;
  border-radius: 8px;
  margin: 16px 0;
}

.retry-btn {
  margin-left: 8px;
  padding: 4px 12px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.retry-btn:hover {
  background: #c82333;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .config-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}

@media (max-width: 768px) {
  .selection-controls {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .students-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .report-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .report-actions {
    align-self: stretch;
    justify-content: space-between;
  }
}
</style>