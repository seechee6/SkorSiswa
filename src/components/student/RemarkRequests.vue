<template>
  <div class="remarks-container">
    <h2 class="page-title">Remark Requests</h2>
    
    <div class="action-buttons">
      <button @click="showNewRequestForm = true" class="action-btn">
        <i class="fas fa-plus"></i> New Request
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading requests...</p>
    </div>

    <div v-else class="remarks-content">
      <div v-if="remarks.length === 0" class="no-remarks">
        <i class="fas fa-inbox empty-icon"></i>
        <p>You haven't submitted any remark requests yet.</p>
      </div>

      <div v-else class="remarks-list">
        <div v-for="remark in remarks" :key="remark.id" class="remark-card">
          <div class="remark-header">
            <h3>{{ remark.component_name }} - {{ remark.course_name }}</h3>
            <span :class="['status-badge', getStatusClass(remark.status)]">
              {{ remark.status }}
            </span>
          </div>
          <div class="remark-details">
            <div class="detail-item">
              <span class="detail-label">Submitted:</span>
              <span>{{ formatDate(remark.created_at) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Current Mark:</span>
              <span>{{ remark.current_mark }} / {{ remark.max_mark }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Requested Mark:</span>
              <span>{{ remark.requested_mark }} / {{ remark.max_mark }}</span>
            </div>
          </div>
          <div class="remark-justification">
            <h4>Justification:</h4>
            <p>{{ remark.justification }}</p>
          </div>
          <div v-if="remark.lecturer_response" class="remark-response">
            <h4>Lecturer Response:</h4>
            <p>{{ remark.lecturer_response }}</p>
          </div>
          <div class="remark-actions" v-if="remark.status === 'Pending'">
            <button @click="editRequest(remark)" class="edit-btn">Edit</button>
            <button @click="confirmCancel(remark.id)" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- New Request Modal -->
    <div v-if="showNewRequestForm" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ editMode ? 'Edit Remark Request' : 'New Remark Request' }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
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
            <input type="text" :value="currentMark + ' / ' + currentMaxMark" disabled />
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
            />
          </div>
          
          <div class="form-group">
            <label>Justification</label>
            <textarea 
              v-model="requestForm.justification" 
              rows="5" 
              required 
              placeholder="Provide a detailed explanation for why you believe your mark should be reconsidered..."
            ></textarea>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              {{ isSubmitting ? 'Submitting...' : (editMode ? 'Update Request' : 'Submit Request') }}
            </button>
            <button type="button" @click="closeModal" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showCancelConfirm" class="modal">
      <div class="modal-content confirmation">
        <div class="modal-header">
          <h3>Cancel Remark Request</h3>
          <button @click="showCancelConfirm = false" class="close-btn">&times;</button>
        </div>
        <p>Are you sure you want to cancel this remark request? This action cannot be undone.</p>
        <div class="form-actions">
          <button @click="cancelRequest" class="submit-btn" :disabled="isSubmitting">
            {{ isSubmitting ? 'Processing...' : 'Yes, Cancel Request' }}
          </button>
          <button @click="showCancelConfirm = false" class="cancel-btn">No, Keep Request</button>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
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
  methods: {
    async fetchRemarks() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/remarks`)
        this.remarks = response.data
      } catch (error) {
        this.error = 'Failed to load remark requests'
        console.error('Error fetching remarks:', error)
      } finally {
        this.loading = false
      }
    },
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/courses`)
        this.courses = response.data
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },
    async fetchComponents() {
      if (!this.requestForm.course_id) return
      
      try {
        const response = await api.get(`/courses/${this.requestForm.course_id}/components`)
        this.components = response.data
        this.requestForm.component_id = ''
        this.currentMark = null
      } catch (error) {
        this.error = 'Failed to load assessment components'
        console.error('Error fetching components:', error)
      }
    },
    async updateCurrentMark() {
      if (!this.requestForm.component_id) {
        this.currentMark = null
        return
      }
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const enrollment = await api.get(`/courses/${this.requestForm.course_id}/enrollment/${user.id}`)
        const enrollmentId = enrollment.data.id
        
        const marksRes = await api.get(`/enrollments/${enrollmentId}/marks`)
        const componentId = parseInt(this.requestForm.component_id)
        const markEntry = marksRes.data.find(m => m.component_id === componentId)
        
        const component = this.components.find(c => c.id === componentId)
        this.currentMaxMark = component.max_mark
        
        if (markEntry) {
          this.currentMark = markEntry.mark
          this.requestForm.requested_mark = Math.min(
            Math.ceil(markEntry.mark * 1.1), // Increase by 10% as default
            component.max_mark
          )
        } else {
          this.currentMark = 0
          this.requestForm.requested_mark = 0
        }
      } catch (error) {
        this.error = 'Failed to retrieve current mark'
        console.error('Error fetching mark:', error)
      }
    },
    editRequest(remark) {
      this.editMode = true
      this.requestForm = {
        id: remark.id,
        course_id: remark.course_id,
        component_id: remark.component_id,
        requested_mark: remark.requested_mark,
        justification: remark.justification
      }
      this.fetchComponents()
      this.currentMark = remark.current_mark
      this.currentMaxMark = remark.max_mark
      this.showNewRequestForm = true
    },
    async submitRequest() {
      if (this.isSubmitting) return
      
      this.isSubmitting = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const requestData = {
          ...this.requestForm,
          user_id: user.id
        }
        
        if (this.editMode) {
          await api.put(`/remarks/${this.requestForm.id}`, requestData)
        } else {
          await api.post('/remarks', requestData)
        }
        
        this.closeModal()
        this.fetchRemarks()
      } catch (error) {
        this.error = `Failed to ${this.editMode ? 'update' : 'submit'} remark request`
        console.error('Error with remark request:', error)
      } finally {
        this.isSubmitting = false
      }
    },
    confirmCancel(id) {
      this.cancelId = id
      this.showCancelConfirm = true
    },
    async cancelRequest() {
      if (this.isSubmitting) return
      
      this.isSubmitting = true
      
      try {
        await api.delete(`/remarks/${this.cancelId}`)
        this.showCancelConfirm = false
        this.fetchRemarks()
      } catch (error) {
        this.error = 'Failed to cancel remark request'
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
        'Cancelled': 'cancelled'
      }
      return statusClasses[status] || 'pending'
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
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 32px;
  font-weight: 300;
  margin-bottom: 32px;
  color: #2c3e50;
}

.action-buttons {
  margin-bottom: 32px;
}

.action-btn {
  background: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 12px 20px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.action-btn:hover {
  background: #2980b9;
}

.remarks-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 24px;
}

.remark-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.remark-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.remark-header h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

.status-badge.pending {
  background: #fef9e7;
  color: #f39c12;
}

.status-badge.approved {
  background: #e3fcef;
  color: #27ae60;
}

.status-badge.rejected {
  background: #fdeded;
  color: #e74c3c;
}

.status-badge.cancelled {
  background: #f5f6fa;
  color: #7f8c8d;
}

.remark-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 4px;
}

.remark-justification, .remark-response {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.remark-justification h4, .remark-response h4 {
  font-size: 16px;
  color: #2c3e50;
  margin: 0 0 8px 0;
}

.remark-justification p, .remark-response p {
  margin: 0;
  line-height: 1.5;
  color: #34495e;
}

.remark-response {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 6px;
  margin-top: 16px;
}

.remark-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.edit-btn, .cancel-btn, .submit-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s;
}

.edit-btn {
  background: #3498db;
  color: white;
}

.edit-btn:hover {
  background: #2980b9;
}

.cancel-btn {
  background: #e74c3c;
  color: white;
}

.cancel-btn:hover {
  background: #c0392b;
}

.submit-btn {
  background: #27ae60;
  color: white;
}

.submit-btn:hover {
  background: #219653;
}

.submit-btn:disabled, .cancel-btn:disabled, .edit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.no-remarks {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 8px;
  padding: 48px 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  text-align: center;
}

.empty-icon {
  font-size: 48px;
  color: #bdc3c7;
  margin-bottom: 16px;
}

.no-remarks p {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0;
}

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
  border-radius: 8px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
}

.modal-content.confirmation {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.modal-header h3 {
  font-size: 24px;
  color: #2c3e50;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #7f8c8d;
  cursor: pointer;
}

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
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 8px;
}

.form-group input, .form-group select, .form-group textarea {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  color: #2c3e50;
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

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  background: #fdeded;
  color: #e74c3c;
  padding: 16px;
  border-radius: 8px;
  margin-top: 24px;
}

@media (max-width: 768px) {
  .remarks-list {
    grid-template-columns: 1fr;
  }
  
  .remark-details {
    grid-template-columns: 1fr 1fr;
  }
  
  .modal-content {
    max-width: 90%;
  }
}
</style>
