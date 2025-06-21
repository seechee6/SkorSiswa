<template>
  <div class="remarks-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Remark Requests</h2>
        <p class="subtitle">Submit and manage your assessment remark requests</p>
      </div>
      <div class="header-actions">
        <button @click="showNewRequestForm = true" class="new-request-btn">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
          </svg>
          New Request
        </button>
      </div>
    </div>    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading remark requests...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="remarks.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
      </div>
      <h3>No Remark Requests</h3>
      <p>You haven't submitted any remark requests yet. Click "New Request" to get started.</p>
    </div>

    <!-- Remarks List -->
    <div v-else class="remarks-content">
      <div class="remarks-grid">
        <div v-for="remark in remarks" :key="remark.id" class="card remark-card">
          <div class="card-header">
            <div class="remark-title">
              <h3>{{ remark.component_name }}</h3>
              <p class="course-name">{{ remark.course_name }}</p>
            </div>
            <span :class="['status-badge', getStatusClass(remark.status)]">
              {{ remark.status }}
            </span>
          </div>
          
          <div class="card-body">
            <div class="remark-details">
              <div class="detail-row">
                <div class="detail-item">
                  <span class="detail-label">Submitted</span>
                  <span class="detail-value">{{ formatDate(remark.created_at) }}</span>
                </div>
                                <div class="detail-item">
                  <span class="detail-label">Potential Gain</span>
                  <span class="detail-value gain-value">+{{ (remark.requested_mark - remark.current_mark).toFixed(1) }}</span>
                </div>
              </div>
              <div class="detail-row">
                <div class="detail-item">
                  <span class="detail-label">Current Mark</span>
                  <span class="detail-value">{{ remark.current_mark }} / {{ remark.max_mark }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Requested Mark</span>
                  <span class="detail-value requested-mark">{{ remark.requested_mark }} / {{ remark.max_mark }}</span>
                </div>
              </div>
            </div>
            
            <div class="remark-justification">
              <h4>Justification</h4>
              <p>{{ remark.justification }}</p>
            </div>
            
            <div v-if="remark.lecturer_response" class="remark-response">
              <h4>Lecturer Response</h4>
              <p>{{ remark.lecturer_response }}</p>
            </div>
            
            <div class="remark-actions" v-if="remark.status === 'Pending'">
              <button @click="editRequest(remark)" class="edit-btn">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708L10.5 8.5 7.5 5.5 12.146.146zM11.207 1.5 13.5 3.793 4.793 12.5H2.5v-2.293L11.207 1.5z"/>
                </svg>
                Edit
              </button>
              <button @click="confirmCancel(remark.id)" class="cancel-btn">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
                Cancel
              </button>
            </div>
            <div class="remark-actions" v-else-if="remark.status === 'Under Review'">
              <button @click="confirmCancel(remark.id)" class="cancel-btn">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
                Cancel Request
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>    <!-- New Request Modal -->
    <div v-if="showNewRequestForm" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editMode ? 'Edit Remark Request' : 'New Remark Request' }}</h3>
          <button @click="closeModal" class="close-btn">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="submitRequest" class="remark-form">
            <div class="form-group">
              <label>Course</label>
              <select v-model="requestForm.course_id" @change="fetchComponents" required>
                <option value="">Select a Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.code }} - {{ course.name }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Assessment Component</label>
              <select v-model="requestForm.component_id" @change="updateCurrentMark" required>
                <option value="">Select a Component</option>
                <option v-for="component in components" :key="component.id" :value="component.id">
                  {{ component.name }}
                </option>
              </select>
            </div>
            
            <div v-if="currentMark !== null" class="form-group">
              <label>Current Mark</label>
              <div class="current-mark-display">
                <span class="mark-value">{{ currentMark }} / {{ currentMaxMark }}</span>
                <span class="mark-percentage">({{ ((currentMark / currentMaxMark) * 100).toFixed(1) }}%)</span>
              </div>
            </div>
            
            <div class="form-group">
              <label>Requested Mark</label>
              <input 
                type="number" 
                v-model.number="requestForm.requested_mark" 
                required 
                :min="currentMark" 
                :max="currentMaxMark"
                :disabled="currentMark === null"
                step="0.1"
              />
              <div v-if="requestForm.requested_mark && currentMark" class="mark-gain">
                Potential gain: +{{ (requestForm.requested_mark - currentMark).toFixed(1) }} marks
              </div>
            </div>
            
            <div class="form-group">
              <label>Justification</label>
              <textarea 
                v-model="requestForm.justification" 
                rows="5" 
                required 
                placeholder="Provide a detailed explanation for why you believe your mark should be reconsidered. Include specific points about your work that may have been overlooked or misunderstood..."
              ></textarea>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg v-if="isSubmitting" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="spinner-icon">
                  <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
                </svg>
                {{ isSubmitting ? 'Processing...' : (editMode ? 'Update Request' : 'Submit Request') }}
              </button>
              <button type="button" @click="closeModal" class="cancel-modal-btn">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showCancelConfirm" class="modal-overlay">
      <div class="modal confirmation-modal">
        <div class="modal-header">
          <h3>Cancel Remark Request</h3>
          <button @click="showCancelConfirm = false" class="close-btn">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="confirmation-content">
            <div class="warning-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <p>Are you sure you want to cancel this remark request? This action cannot be undone.</p>
          </div>
          <div class="form-actions">
            <button @click="cancelRequest" class="confirm-cancel-btn" :disabled="isSubmitting">
              <svg v-if="isSubmitting" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="spinner-icon">
                <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
              </svg>
              {{ isSubmitting ? 'Processing...' : 'Yes, Cancel Request' }}
            </button>
            <button @click="showCancelConfirm = false" class="cancel-modal-btn">No, Keep Request</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <div class="error-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <div class="error-content">
        <h4>Error</h4>
        <p>{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'RemarkRequests',
  data() {
    return {
      remarks: [],
      courses: [],
      components: [],
      loading: false,
      error: '',
      showNewRequestForm: false,
      showCancelConfirm: false,
      isSubmitting: false,
      editMode: false,
      cancelId: null,
      currentMark: null,
      currentMaxMark: null,
      requestForm: {
        course_id: '',
        component_id: '',
        requested_mark: null,
        justification: ''
      }
    }
  },
  methods: {    async fetchRemarks() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/students/${user.id}/remark-requests`)
        this.remarks = response.data.requests.map(request => ({
          id: request.id,
          component_name: request.assessment_name,
          course_name: `${request.course_code} - ${request.course_name}`,
          course_id: request.course_id || 0,
          component_id: request.assessment_id,
          current_mark: request.current_mark,
          max_mark: request.max_mark,
          requested_mark: request.requested_mark,
          status: this.capitalizeStatus(request.status),
          justification: request.justification,
          lecturer_response: request.lecturer_response,
          created_at: request.created_at
        }))
      } catch (error) {
        this.error = 'Failed to load remark requests'
        console.error('Error fetching remarks:', error)
      } finally {
        this.loading = false
      }
    },    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/students/${user.id}/courses-with-assessments`)
        this.courses = response.data.courses
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },    async fetchComponents() {
      if (!this.requestForm.course_id) return
      
      try {
        const selectedCourse = this.courses.find(c => c.id == this.requestForm.course_id)
        this.components = selectedCourse ? selectedCourse.assessments : []
        this.requestForm.component_id = ''
        this.currentMark = null
        this.currentMaxMark = null
      } catch (error) {
        this.error = 'Failed to load assessment components'
        console.error('Error fetching components:', error)
      }
    },    async updateCurrentMark() {
      if (!this.requestForm.component_id) {
        this.currentMark = null
        this.currentMaxMark = null
        return
      }
      
      try {
        const selectedComponent = this.components.find(c => c.id == this.requestForm.component_id)
        if (selectedComponent) {
          this.currentMark = selectedComponent.current_mark
          this.currentMaxMark = selectedComponent.max_mark
          this.requestForm.requested_mark = Math.min(
            Math.ceil(selectedComponent.current_mark * 1.1), // Increase by 10% as default
            selectedComponent.max_mark
          )
        }
      } catch (error) {
        this.error = 'Failed to retrieve current mark'
        console.error('Error fetching mark:', error)
      }
    },    editRequest(remark) {
      this.editMode = true
      this.requestForm = {
        id: remark.id,
        course_id: remark.course_id,
        component_id: remark.component_id,
        requested_mark: remark.requested_mark,
        justification: remark.justification
      }
      
      // Set current mark data
      this.currentMark = remark.current_mark
      this.currentMaxMark = remark.max_mark
      
      // Fetch components for the selected course
      this.fetchComponents()
      this.showNewRequestForm = true
    },    async submitRequest() {
      if (this.isSubmitting) return
      
      this.isSubmitting = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const requestData = {
          student_id: user.id,
          assessment_id: this.requestForm.component_id,
          requested_mark: this.requestForm.requested_mark,
          justification: this.requestForm.justification
        }
        
        if (this.editMode) {
          await api.put(`/remark-requests/${this.requestForm.id}`, requestData)
        } else {
          await api.post('/remark-requests', requestData)
        }
        
        this.closeModal()
        this.fetchRemarks()
      } catch (error) {
        this.error = error.response?.data?.error || `Failed to ${this.editMode ? 'update' : 'submit'} remark request`
        console.error('Error with remark request:', error)
      } finally {
        this.isSubmitting = false
      }
    },
    confirmCancel(id) {
      this.cancelId = id
      this.showCancelConfirm = true
    },    async cancelRequest() {
      if (this.isSubmitting) return
      
      this.isSubmitting = true
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.delete(`/remark-requests/${this.cancelId}`, {
          data: { student_id: user.id }
        })
        this.showCancelConfirm = false
        this.fetchRemarks()
      } catch (error) {
        this.error = error.response?.data?.error || 'Failed to cancel remark request'
        console.error('Error cancelling remark:', error)
      } finally {
        this.isSubmitting = false
      }
    },
    closeModal() {
      this.showNewRequestForm = false
      this.editMode = false
      this.requestForm = {
        course_id: '',
        component_id: '',
        requested_mark: null,
        justification: ''
      }
      this.currentMark = null
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    },
    getStatusClass(status) {
      const statusClasses = {
        'Pending': 'pending',
        'Approved': 'approved',
        'Rejected': 'rejected',
        'Cancelled': 'cancelled',
        'Under Review': 'under-review'
      }
      return statusClasses[status] || 'pending'
    },
    capitalizeStatus(status) {
      const statusMap = {
        'pending': 'Pending',
        'under_review': 'Under Review',
        'approved': 'Approved',
        'rejected': 'Rejected'
      }
      return statusMap[status] || status
    }
  },
  mounted() {
    this.fetchRemarks()
    this.fetchCourses()
  }
}
</script>

<style scoped>
.remarks-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Page Header Styles */
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

.new-request-btn {
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

.new-request-btn:hover {
  background-color: #4c51bf;
  transform: translateY(-1px);
}

/* Loading & Empty State Styles */
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

/* Card Styles */
.remarks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 20px;
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

.card-header {
  padding: 16px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  text-align: left;
}

.remark-title h3 {
  margin: 0 0 4px 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.course-name {
  margin: 0;
  font-size: 0.85rem;
  color: #718096;
}

.card-body {
  padding: 16px;
}

/* Status Badge */
.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.pending {
  background-color: #fef5e7;
  color: #c05621;
}

.status-badge.approved {
  background-color: #c6f6d5;
  color: #2f855a;
}

.status-badge.rejected {
  background-color: #fed7d7;
  color: #c53030;
}

.status-badge.cancelled {
  background-color: #edf2f7;
  color: #718096;
}

.status-badge.under-review {
  background-color: #e9d8fd;
  color: #6b46c1;
}

/* Detail Rows */
.remark-details {
  margin-bottom: 16px;
}

.detail-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 12px;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2d3748;
}

.requested-mark {
  color: #4c51bf;
}

.gain-value {
  color: #48bb78;
}

/* Justification & Response */
.remark-justification, .remark-response {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #edf2f7;
}

.remark-justification h4, .remark-response h4 {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 8px 0;
}

.remark-justification p, .remark-response p {
  margin: 0;
  line-height: 1.5;
  color: #2d3748;
  font-size: 0.9rem;
}

.remark-response {
  background-color: #f7fafc;
  padding: 12px;
  border-radius: 6px;
  border-left: 4px solid #4c51bf;
}

/* Action Buttons */
.remark-actions {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.edit-btn, .cancel-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.edit-btn {
  background-color: #4c51bf;
  color: white;
}

.edit-btn:hover {
  background-color: #4c51bf;
  transform: translateY(-1px);
}

.cancel-btn {
  background-color: #fed7d7;
  color: #c53030;
}

.cancel-btn:hover {
  background-color: #feb2b2;
  transform: translateY(-1px);
}

/* Modal Styles */
.modal-overlay {
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
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.confirmation-modal {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #edf2f7;
}

.modal-header h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: #edf2f7;
  color: #4a5568;
}

.modal-body {
  padding: 20px;
}

/* Form Styles */
.remark-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 6px;
}

.form-group input, .form-group select, .form-group textarea {
  padding: 10px 12px;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #2d3748;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none;
  border-color: #4c51bf;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-group input:disabled {
  background-color: #f7fafc;
  color: #718096;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.current-mark-display {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  background-color: #f7fafc;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
}

.mark-value {
  font-weight: 600;
  color: #2d3748;
}

.mark-percentage {
  font-size: 0.85rem;
  color: #718096;
}

.mark-gain {
  font-size: 0.8rem;
  color: #48bb78;
  margin-top: 4px;
  font-weight: 500;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.submit-btn, .cancel-modal-btn, .confirm-cancel-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn {
  background-color: #48bb78;
  color: white;
  flex: 1;
}

.submit-btn:hover:not(:disabled) {
  background-color: #38a169;
  transform: translateY(-1px);
}

.submit-btn:disabled {
  background-color: #a0aec0;
  cursor: not-allowed;
}

.confirm-cancel-btn {
  background-color: #e53e3e;
  color: white;
  flex: 1;
}

.confirm-cancel-btn:hover:not(:disabled) {
  background-color: #c53030;
  transform: translateY(-1px);
}

.confirm-cancel-btn:disabled {
  background-color: #a0aec0;
  cursor: not-allowed;
}

.cancel-modal-btn {
  background-color: #edf2f7;
  color: #4a5568;
}

.cancel-modal-btn:hover {
  background-color: #e2e8f0;
  transform: translateY(-1px);
}

/* Confirmation Modal */
.confirmation-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 20px;
}

.warning-icon {
  width: 48px;
  height: 48px;
  color: #ed8936;
  margin-bottom: 16px;
}

.warning-icon svg {
  width: 100%;
  height: 100%;
}

.confirmation-content p {
  margin: 0;
  color: #4a5568;
  line-height: 1.5;
}

/* Error Message */
.error-message {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background-color: #fed7d7;
  border: 1px solid #feb2b2;
  border-radius: 6px;
  padding: 16px;
  margin-top: 20px;
}

.error-icon {
  width: 20px;
  height: 20px;
  color: #c53030;
  flex-shrink: 0;
  margin-top: 2px;
}

.error-content h4 {
  margin: 0 0 4px 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #c53030;
}

.error-content p {
  margin: 0;
  font-size: 0.85rem;
  color: #9b2c2c;
  line-height: 1.4;
}

/* Spinner Animation */
.spinner-icon {
  animation: spin 1s linear infinite;
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
  
  .new-request-btn {
    width: 100%;
    justify-content: center;
  }
  
  .remarks-grid {
    grid-template-columns: 1fr;
  }
  
  .detail-row {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .modal {
    max-width: 95%;
    margin: 20px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .remark-actions {
    flex-direction: column;
  }
}
</style>
