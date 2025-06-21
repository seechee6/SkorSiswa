<template>
  <div class="remark-reviews-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Remark Requests</h2>
        <p class="subtitle">Review and respond to student remark requests</p>
      </div>
      <div class="header-actions">
        <div class="filter-controls">
          <select v-model="statusFilter" @change="filterRequests" class="status-filter">
            <option value="">All Requests</option>
            <option value="pending">Pending</option>
            <option value="under_review">Under Review</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading remark requests...</p>
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
      <button class="retry-button" @click="fetchRemarkRequests">
        Try Again
      </button>
    </div>

    <!-- Requests List -->
    <div v-else class="requests-content">
      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="card summary-card">
          <div class="card-icon pending-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ pendingCount }}</div>
            <div class="card-label">Pending Review</div>
          </div>
        </div>
        
        <div class="card summary-card">
          <div class="card-icon review-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414L2.586 8l3.707-3.707a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ underReviewCount }}</div>
            <div class="card-label">Under Review</div>
          </div>
        </div>
        
        <div class="card summary-card">
          <div class="card-icon total-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ filteredRequests.length }}</div>
            <div class="card-label">Total Requests</div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredRequests.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3>No Remark Requests</h3>
        <p>{{ statusFilter ? 'No requests match the selected filter' : 'No remark requests have been submitted yet' }}</p>
      </div>

      <!-- Requests List -->
      <div v-else class="requests-list">
        <div v-for="request in filteredRequests" :key="request.id" class="request-card">
          <div class="request-header">
            <div class="student-info">
              <h3>{{ request.student_name }}</h3>
              <span class="matric-no">{{ request.student_matric }}</span>
            </div>
            <span :class="['status-badge', getStatusClass(request.status)]">
              {{ capitalizeStatus(request.status) }}
            </span>
          </div>
          
          <div class="request-details">
            <div class="course-assessment">
              <h4>{{ request.course_code }} - {{ request.course_name }}</h4>
              <p class="assessment-name">{{ request.assessment_name }}</p>
            </div>
            
            <div class="marks-info">
              <div class="mark-item">
                <span class="mark-label">Current Mark:</span>
                <span class="mark-value">{{ request.current_mark }} / {{ request.max_mark }}</span>
              </div>
              <div class="mark-item">
                <span class="mark-label">Requested Mark:</span>
                <span class="mark-value requested">{{ request.requested_mark }} / {{ request.max_mark }}</span>
              </div>
              <div class="mark-item">
                <span class="mark-label">Difference:</span>
                <span :class="['mark-value', 'difference', getDifferenceClass(request.requested_mark - request.current_mark)]">
                  +{{ (request.requested_mark - request.current_mark).toFixed(1) }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="request-justification">
            <h4>Student Justification:</h4>
            <p>{{ request.justification }}</p>
          </div>
          
          <div v-if="request.lecturer_response" class="lecturer-response">
            <h4>Your Response:</h4>
            <p>{{ request.lecturer_response }}</p>
            <small class="response-date">Reviewed on {{ formatDate(request.reviewed_at) }}</small>
          </div>
          
          <div class="request-meta">
            <span class="submitted-date">Submitted {{ formatDate(request.created_at) }}</span>
          </div>
          
          <div v-if="request.status === 'pending' || request.status === 'under_review'" class="request-actions">
            <button @click="openResponseModal(request)" class="respond-btn">
              {{ request.status === 'pending' ? 'Review Request' : 'Update Response' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Response Modal -->
    <div v-if="showResponseModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Review Remark Request</h3>
          <button @click="closeResponseModal" class="close-btn">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="request-summary">
            <h4>{{ selectedRequest.student_name }} - {{ selectedRequest.assessment_name }}</h4>
            <div class="marks-summary">
              <span>Current: {{ selectedRequest.current_mark }}/{{ selectedRequest.max_mark }}</span>
              <span>→</span>
              <span>Requested: {{ selectedRequest.requested_mark }}/{{ selectedRequest.max_mark }}</span>
            </div>
          </div>
          
          <div class="justification-review">
            <h4>Student Justification:</h4>
            <p>{{ selectedRequest.justification }}</p>
          </div>
          
          <form @submit.prevent="submitResponse" class="response-form">
            <div class="form-group">
              <label>Decision</label>
              <select v-model="responseForm.status" required>
                <option value="">Select Decision</option>
                <option value="under_review">Mark as Under Review</option>
                <option value="approved">Approve Request</option>
                <option value="rejected">Reject Request</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Response to Student</label>
              <textarea 
                v-model="responseForm.lecturer_response" 
                rows="4" 
                required 
                placeholder="Provide your reasoning for this decision..."
              ></textarea>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                {{ isSubmitting ? 'Submitting...' : 'Submit Response' }}
              </button>
              <button type="button" @click="closeResponseModal" class="cancel-btn">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'RemarkReviews',
  data() {
    return {
      requests: [],
      filteredRequests: [],
      loading: false,
      error: '',
      statusFilter: '',
      showResponseModal: false,
      selectedRequest: null,
      isSubmitting: false,
      responseForm: {
        status: '',
        lecturer_response: ''
      }
    }
  },
  computed: {
    pendingCount() {
      return this.requests.filter(r => r.status === 'pending').length
    },
    underReviewCount() {
      return this.requests.filter(r => r.status === 'under_review').length
    }
  },
  methods: {
    async fetchRemarkRequests() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/lecturers/${user.id}/remark-requests`)
        this.requests = response.data.requests
        this.filterRequests()
      } catch (error) {
        this.error = 'Failed to load remark requests'
        console.error('Error fetching remark requests:', error)
      } finally {
        this.loading = false
      }
    },
    
    filterRequests() {
      if (this.statusFilter) {
        this.filteredRequests = this.requests.filter(request => request.status === this.statusFilter)
      } else {
        this.filteredRequests = [...this.requests]
      }
    },
    
    openResponseModal(request) {
      this.selectedRequest = request
      this.responseForm = {
        status: request.status === 'pending' ? '' : request.status,
        lecturer_response: request.lecturer_response || ''
      }
      this.showResponseModal = true
    },
    
    closeResponseModal() {
      this.showResponseModal = false
      this.selectedRequest = null
      this.responseForm = {
        status: '',
        lecturer_response: ''
      }
    },
    
    async submitResponse() {
      if (this.isSubmitting) return
      
      this.isSubmitting = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const responseData = {
          lecturer_id: user.id,
          status: this.responseForm.status,
          lecturer_response: this.responseForm.lecturer_response
        }
        
        await api.post(`/remark-requests/${this.selectedRequest.id}/respond`, responseData)
        
        this.closeResponseModal()
        this.fetchRemarkRequests()
      } catch (error) {
        this.error = error.response?.data?.error || 'Failed to submit response'
        console.error('Error submitting response:', error)
      } finally {
        this.isSubmitting = false
      }
    },
    
    capitalizeStatus(status) {
      const statusMap = {
        'pending': 'Pending',
        'under_review': 'Under Review',
        'approved': 'Approved',
        'rejected': 'Rejected'
      }
      return statusMap[status] || status
    },
    
    getStatusClass(status) {
      const statusClasses = {
        'pending': 'pending',
        'under_review': 'under-review',
        'approved': 'approved',
        'rejected': 'rejected'
      }
      return statusClasses[status] || 'pending'
    },
    
    getDifferenceClass(difference) {
      if (difference > 0) return 'positive'
      if (difference < 0) return 'negative'
      return 'neutral'
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
  },
  
  mounted() {
    this.fetchRemarkRequests()
  }
}
</script>

<style scoped>
.remark-reviews-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
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
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
}

.filter-controls {
  display: flex;
  gap: 12px;
}

.status-filter {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background-color: #f8fafc;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
  cursor: pointer;
}

.status-filter:hover {
  border-color: #a0aec0;
}

.status-filter:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
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

.pending-icon {
  background-color: #fef9e7;
  color: #f39c12;
}

.review-icon {
  background-color: #e3f2fd;
  color: #1976d2;
}

.total-icon {
  background-color: #e8f5e8;
  color: #4caf50;
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

/* Requests List */
.requests-list {
  display: grid;
  gap: 20px;
}

.request-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border: 1px solid #e2e8f0;
  transition: all 0.2s;
}

.request-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-color: #cbd5e0;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.student-info h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0 0 4px 0;
}

.matric-no {
  font-size: 14px;
  color: #718096;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.pending {
  background: #fef9e7;
  color: #f39c12;
}

.status-badge.under-review {
  background: #e3f2fd;
  color: #1976d2;
}

.status-badge.approved {
  background: #e8f5e8;
  color: #4caf50;
}

.status-badge.rejected {
  background: #ffebee;
  color: #f44336;
}

.request-details {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 24px;
  margin-bottom: 16px;
}

.course-assessment h4 {
  font-size: 16px;
  color: #2c3e50;
  margin: 0 0 4px 0;
}

.assessment-name {
  font-size: 14px;
  color: #718096;
  margin: 0;
}

.marks-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: end;
}

.mark-item {
  display: flex;
  gap: 8px;
  align-items: center;
}

.mark-label {
  font-size: 14px;
  color: #718096;
}

.mark-value {
  font-size: 14px;
  font-weight: 600;
  color: #2d3748;
}

.mark-value.requested {
  color: #3498db;
}

.mark-value.difference.positive {
  color: #e74c3c;
}

.mark-value.difference.negative {
  color: #27ae60;
}

.mark-value.difference.neutral {
  color: #95a5a6;
}

.request-justification, .lecturer-response {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.request-justification h4, .lecturer-response h4 {
  font-size: 14px;
  color: #2c3e50;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.request-justification p, .lecturer-response p {
  margin: 0;
  line-height: 1.6;
  color: #34495e;
}

.lecturer-response {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 6px;
  border: none;
}

.response-date {
  font-size: 12px;
  color: #95a5a6;
  font-style: italic;
}

.request-meta {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.submitted-date {
  font-size: 12px;
  color: #95a5a6;
}

.request-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.respond-btn {
  background: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s;
}

.respond-btn:hover {
  background: #2980b9;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #95a5a6;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #2c3e50;
}

.modal-body {
  padding: 24px;
}

.request-summary {
  margin-bottom: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.request-summary h4 {
  margin: 0 0 8px 0;
  color: #2c3e50;
}

.marks-summary {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #718096;
}

.justification-review {
  margin-bottom: 24px;
}

.justification-review h4 {
  margin: 0 0 8px 0;
  color: #2c3e50;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.justification-review p {
  margin: 0;
  line-height: 1.6;
  color: #34495e;
  background: white;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}

.response-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  color: #2c3e50;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input, .form-group select, .form-group textarea {
  padding: 12px;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  font-size: 14px;
  color: #2c3e50;
  transition: border-color 0.2s;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.submit-btn, .cancel-btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn {
  background: #27ae60;
  color: white;
}

.submit-btn:hover {
  background: #219653;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.cancel-btn {
  background: #e74c3c;
  color: white;
}

.cancel-btn:hover {
  background: #c0392b;
}

/* Loading and Error States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
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
  color: #e74c3c;
  text-align: center;
}

.error-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
}

.error-message h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
}

.error-message p {
  margin: 0 0 16px 0;
  color: #718096;
}

.retry-button {
  background: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 12px 24px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s;
}

.retry-button:hover {
  background: #2980b9;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #718096;
  text-align: center;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin-bottom: 16px;
  color: #cbd5e0;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  color: #4a5568;
}

.empty-state p {
  margin: 0;
  color: #718096;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
  }
  
  .request-details {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .marks-info {
    align-items: start;
  }
  
  .modal-content {
    max-width: 90%;
  }
}
</style>
