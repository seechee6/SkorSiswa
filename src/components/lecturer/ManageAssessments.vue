<template>
  <div>
    <div class="header-section">
      <h3>Manage Assessments (70%)</h3>
      <p class="subtitle">Manage assessment components for your courses</p>
    </div>
    
    <!-- Courses Table -->
    <div class="card">
      <div class="table-header">
        <h4>Your Courses</h4>
        <span class="course-count">{{ lecturerCourses.length }} courses</span>
      </div>
      
      <div class="table-container" v-if="lecturerCourses.length > 0">
        <table class="courses-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Course Name</th>
              <th>Weight Used</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(course, index) in lecturerCourses" :key="course.id" class="course-row">
              <td class="number-cell">
                {{ index + 1 }}
              </td>
              <td class="course-cell">
                <div class="course-info">
                  <div class="course-name">{{ course.code }} - {{ course.name }}</div>
                  <div class="course-details">Semester {{ course.semester }} • {{ course.year }}</div>
                </div>
              </td>
              <td class="weight-cell">
                <div class="weight-display">
                  <div class="weight-main">
                    <span class="weight-badge" :class="getWeightClass(course.totalWeight)">
                      {{ course.totalWeight ? course.totalWeight.toFixed(1) : '0.0' }}%
                    </span>
                    <div class="weight-bar">
                      <div 
                        class="weight-progress-fill" 
                        :style="{ width: Math.min((course.totalWeight || 0) / 70 * 100, 100) + '%' }"
                        :class="getWeightClass(course.totalWeight)"
                      ></div>
                    </div>
                  </div>
                  <span class="weight-remaining">
                    {{ course.totalWeight ? (70 - course.totalWeight).toFixed(1) : '70.0' }}% remaining
                  </span>
                </div>
              </td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button @click="openAddAssessmentModal(course)" class="action-btn-small primary" title="Add Assessment">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                  </button>
                  
                  <button @click="openViewAssessmentsModal(course)" class="action-btn-small info" title="View Assessments">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h4>No Courses Found</h4>
        <p>You don't have any courses assigned yet.</p>
      </div>
    </div>

    <!-- Add Assessment Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeAddModal">
      <div class="modal-content add-modal">
        <div class="modal-header">
          <h4>Add Assessment Component</h4>
          <button @click="closeAddModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="course-info-header">
          <div class="course-name">{{ selectedCourse?.code }} - {{ selectedCourse?.name }}</div>
          <div class="weight-summary">
            <span class="weight-used">{{ selectedCourse?.totalWeight?.toFixed(1) || '0.0' }}% used</span>
            <span class="weight-remaining">{{ selectedCourse ? (70 - (selectedCourse.totalWeight || 0)).toFixed(1) : '70.0' }}% remaining</span>
          </div>
        </div>

        <form @submit.prevent="addAssessment" class="assessment-form">
          <div class="form-grid">
            <div class="form-group">
              <label for="component-name">Component Name</label>
              <input 
                id="component-name"
                v-model="newAssessment.name" 
                placeholder="e.g., Quiz 1, Assignment 1" 
                required 
              />
            </div>
            <div class="form-group">
              <label for="weight">Weight (%)</label>
              <input 
                id="weight"
                v-model.number="newAssessment.weight" 
                type="number" 
                step="0.1" 
                min="0" 
                max="70" 
                placeholder="15.0" 
                required 
              />
              <small v-if="weightWarning" class="warning">{{ weightWarning }}</small>
            </div>
            <div class="form-group">
              <label for="max-mark">Maximum Mark</label>
              <input 
                id="max-mark"
                v-model.number="newAssessment.max_mark" 
                type="number" 
                step="0.1" 
                min="0" 
                placeholder="100" 
                required 
              />
            </div>
          </div>
          
          <div class="modal-actions">
            <button 
              type="submit" 
              :disabled="!canAddAssessment"
              class="save-btn"
            >
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Component
            </button>
            <button type="button" @click="closeAddModal" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Assessments Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-content view-modal">
        <div class="modal-header">
          <h4>Assessment Components</h4>
          <button @click="closeViewModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="course-info-header">
          <div class="course-name">{{ selectedCourse?.code }} - {{ selectedCourse?.name }}</div>
          <div class="weight-summary">
            <div class="weight-progress">
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: Math.min(selectedCourse?.totalWeight || 0, 70) + '%' }"
                  :class="{ 'over-limit': (selectedCourse?.totalWeight || 0) > 70 }"
                ></div>
              </div>
              <span class="progress-text">{{ selectedCourse?.totalWeight?.toFixed(1) || '0.0' }}% / 70%</span>
            </div>
          </div>
        </div>

        <div class="assessments-list">
          <div class="assessments-header">
            <h5>Assessment Components</h5>
            <button @click="switchToAddModal" class="add-assessment-btn">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Assessment
            </button>
          </div>

          <div v-if="currentAssessments.length > 0" class="table-wrapper">
            <table class="assessment-table">
              <thead>
                <tr>
                  <th>Component Name</th>
                  <th>Weight (%)</th>
                  <th>Max Mark</th>
                  <th>Students Graded</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="assessment in currentAssessments" :key="assessment.id">
                  <td>
                    <div class="component-info">
                      <span class="component-name">{{ assessment.name }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="weight-badge">{{ assessment.weight }}%</span>
                  </td>
                  <td>{{ assessment.max_mark }}</td>
                  <td>
                    <span class="graded-count">{{ assessment.graded_count || 0 }}</span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button @click="editAssessment(assessment)" class="action-btn-small success" title="Edit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                      <button 
                        @click="deleteAssessment(assessment.id, assessment.name)" 
                        class="action-btn-small danger"
                        :disabled="assessment.graded_count > 0"
                        :title="assessment.graded_count > 0 ? 'Cannot delete: Students have been graded' : 'Delete component'"
                      >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State in Modal -->
          <div v-else class="empty-state-modal">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h4>No Assessment Components</h4>
            <p>This course doesn't have any assessment components yet.</p>
            <button @click="switchToAddModal" class="add-component-btn">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Component
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Assessment Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content edit-modal">
        <div class="modal-header">
          <h4>Edit Assessment Component</h4>
          <button @click="closeEditModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="updateAssessment" class="assessment-form">
          <div class="form-grid">
            <div class="form-group">
              <label for="edit-name">Component Name</label>
              <input id="edit-name" v-model="editAssessmentData.name" required />
            </div>
            <div class="form-group">
              <label for="edit-weight">Weight (%)</label>
              <input 
                id="edit-weight"
                v-model.number="editAssessmentData.weight" 
                type="number" 
                step="0.1" 
                min="0" 
                max="70" 
                required 
              />
              <small v-if="editWeightWarning" class="warning">{{ editWeightWarning }}</small>
            </div>
            <div class="form-group">
              <label for="edit-max-mark">Maximum Mark</label>
              <input 
                id="edit-max-mark"
                v-model.number="editAssessmentData.max_mark" 
                type="number" 
                step="0.1" 
                min="0" 
                required 
              />
            </div>
          </div>
          
          <div class="modal-actions">
            <button type="submit" :disabled="!canUpdateAssessment" class="save-btn">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Changes
            </button>
            <button type="button" @click="closeEditModal" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-content delete-modal">
        <div class="delete-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h4>Delete Assessment Component</h4>
        <p>Are you sure you want to delete <strong>{{ assessmentToDelete.name }}</strong>?</p>
        <p class="warning-text">This action cannot be undone and will remove all associated marks.</p>
        <div class="modal-actions">
          <button @click="confirmDeleteAssessment" class="delete-confirm-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete Component
          </button>
          <button @click="closeDeleteModal" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="success" class="toast success-toast">
      <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      {{ success }}
    </div>

    <div v-if="error" class="toast error-toast">
      <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api';

export default {
  name: 'ManageAssessments',
  data() {
    return {
      lecturerCourses: [],
      currentAssessments: [],
      selectedCourse: null,
      showAddModal: false,
      showViewModal: false,
      showEditModal: false,
      showDeleteModal: false,
      newAssessment: {
        name: '',
        weight: null,
        max_mark: null
      },
      editAssessmentData: {
        id: '',
        name: '',
        weight: null,
        max_mark: null
      },
      assessmentToDelete: { id: null, name: '' },
      error: '',
      success: ''
    }
  },
  computed: {
    totalWeight() {
      return this.currentAssessments.reduce((sum, assessment) => sum + Number(assessment.weight), 0)
    },
    weightWarning() {
      if (!this.newAssessment.weight || !this.selectedCourse) return ''
      const newTotal = (this.selectedCourse.totalWeight || 0) + Number(this.newAssessment.weight)
      if (newTotal > 70) {
        return `This would exceed the 70% limit by ${(newTotal - 70).toFixed(1)}%`
      }
      return ''
    },
    editWeightWarning() {
      if (!this.editAssessmentData.weight) return ''
      const currentWeight = this.currentAssessments.find(a => a.id === this.editAssessmentData.id)?.weight || 0
      const newTotal = this.totalWeight - currentWeight + Number(this.editAssessmentData.weight)
      if (newTotal > 70) {
        return `This would exceed the 70% limit by ${(newTotal - 70).toFixed(1)}%`
      }
      return ''
    },
    canAddAssessment() {
      if (!this.newAssessment.name || !this.newAssessment.weight || !this.newAssessment.max_mark || !this.selectedCourse) {
        return false
      }
      const newTotal = (this.selectedCourse.totalWeight || 0) + Number(this.newAssessment.weight)
      return newTotal <= 70
    },
    canUpdateAssessment() {
      if (!this.editAssessmentData.name || !this.editAssessmentData.weight || !this.editAssessmentData.max_mark) {
        return false
      }
      const currentWeight = this.currentAssessments.find(a => a.id === this.editAssessmentData.id)?.weight || 0
      const newTotal = this.totalWeight - currentWeight + Number(this.editAssessmentData.weight)
      return newTotal <= 70
    }
  },
  methods: {
    async fetchLecturerCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get('/courses')
        this.lecturerCourses = res.data.filter(c => c.lecturer_id === user.id)
        
        // Fetch total weight for each course
        for (let course of this.lecturerCourses) {
          await this.fetchCourseWeight(course)
        }
      } catch (e) {
        this.showError('Failed to load courses.')
      }
    },
    
    async fetchCourseWeight(course) {
      try {
        const res = await api.get(`/courses/${course.id}/assessments`)
        const assessments = res.data || []
        course.totalWeight = assessments.reduce((sum, assessment) => sum + Number(assessment.weight), 0)
        course.assessmentCount = assessments.length
      } catch (e) {
        course.totalWeight = 0
        course.assessmentCount = 0
      }
    },

    async fetchAssessments(courseId) {
      try {
        const res = await api.get(`/courses/${courseId}/assessments`)
        this.currentAssessments = res.data || []
        
        // Fetch graded count for each assessment (mock data for now)
        for (let assessment of this.currentAssessments) {
          assessment.graded_count = Math.floor(Math.random() * 20)
        }
      } catch (e) {
        this.currentAssessments = []
        this.showError('Failed to load assessments.')
      }
    },

    getWeightClass(weight) {
      if (!weight) return ''
      if (weight > 70) return 'over-limit'
      if (weight > 60) return 'warning'
      return 'normal'
    },

    openAddAssessmentModal(course) {
      this.selectedCourse = course
      this.newAssessment = { name: '', weight: null, max_mark: null }
      this.showAddModal = true
      this.fetchAssessments(course.id)
    },

    closeAddModal() {
      this.showAddModal = false
      this.selectedCourse = null
      this.newAssessment = { name: '', weight: null, max_mark: null }
      this.clearMessages()
    },

    async openViewAssessmentsModal(course) {
      this.selectedCourse = course
      await this.fetchAssessments(course.id)
      this.showViewModal = true
    },

    closeViewModal() {
      this.showViewModal = false
      this.selectedCourse = null
      this.currentAssessments = []
    },

    switchToAddModal() {
      this.showViewModal = false
      this.showAddModal = true
    },

    async addAssessment() {
      this.clearMessages()
      
      try {
        await api.post(`/courses/${this.selectedCourse.id}/assessments`, {
          name: this.newAssessment.name,
          weight: this.newAssessment.weight,
          max_mark: this.newAssessment.max_mark
        })
        
        this.showSuccess('Assessment component added successfully!')
        this.newAssessment = { name: '', weight: null, max_mark: null }
        
        // Refresh data
        await this.fetchAssessments(this.selectedCourse.id)
        await this.fetchCourseWeight(this.selectedCourse)
        
        // Close modal after a delay
        setTimeout(() => {
          this.closeAddModal()
        }, 1500)
        
      } catch (e) {
        this.showError('Failed to add assessment component.')
      }
    },

    editAssessment(assessment) {
      this.editAssessmentData = { ...assessment }
      this.showEditModal = true
    },

    closeEditModal() {
      this.showEditModal = false
      this.editAssessmentData = { id: '', name: '', weight: null, max_mark: null }
      this.clearMessages()
    },

    async updateAssessment() {
      this.clearMessages()
      
      try {
        await api.put(`/assessments/${this.editAssessmentData.id}`, {
          name: this.editAssessmentData.name,
          weight: this.editAssessmentData.weight,
          max_mark: this.editAssessmentData.max_mark
        })
        
        this.showSuccess('Assessment component updated successfully!')
        
        // Refresh data
        await this.fetchAssessments(this.selectedCourse.id)
        await this.fetchCourseWeight(this.selectedCourse)
        
        // Close modal after a delay
        setTimeout(() => {
          this.closeEditModal()
        }, 1500)
        
      } catch (e) {
        this.showError('Failed to update assessment component.')
      }
    },

    deleteAssessment(id, name) {
      this.assessmentToDelete = { id, name }
      this.showDeleteModal = true
    },

    closeDeleteModal() {
      this.showDeleteModal = false
      this.assessmentToDelete = { id: null, name: '' }
    },

    async confirmDeleteAssessment() {
      try {
        await api.delete(`/assessments/${this.assessmentToDelete.id}`)
        this.showSuccess('Assessment component deleted successfully!')
        
        // Refresh data
        await this.fetchAssessments(this.selectedCourse.id)
        await this.fetchCourseWeight(this.selectedCourse)
        
        this.closeDeleteModal()
      } catch (e) {
        this.showError('Failed to delete assessment component.')
        this.closeDeleteModal()
      }
    },

    showSuccess(message) {
      this.success = message
      this.error = ''
      setTimeout(() => {
        this.success = ''
      }, 5000)
    },

    showError(message) {
      this.error = message
      this.success = ''
      setTimeout(() => {
        this.error = ''
      }, 5000)
    },

    clearMessages() {
      this.success = ''
      this.error = ''
    }
  },
  
  mounted() {
    this.fetchLecturerCourses()
  }
}
</script>

<style scoped>
/* Header Section */
.header-section {
  margin-bottom: 32px;
}

.header-section h3 {
  margin: 0 0 8px 0;
  color: #1D3557;
  font-size: 28px;
  font-weight: 700;
}

.subtitle {
  margin: 0;
  font-size: 16px;
  color: #6c757d;
  font-weight: 400;
}

/* Card Styles */
.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
}

/* Table Header */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.table-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 18px;
  font-weight: 600;
}

.course-count {
  background: #F1FAEE;
  color: #1D3557;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Courses Table */
.table-container {
  overflow-x: auto;
}

.courses-table {
  width: 100%;
  border-collapse: collapse;
}

.courses-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 16px 12px;
  text-align: left;
  border-bottom: 2px solid #e9ecef;
}

.courses-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.course-row:hover {
  background: #f8f9fa;
  transition: background-color 0.2s ease;
}

.number-cell {
  width: 60px;
  text-align: center;
  font-weight: 600;
  color: #6c757d;
}

.course-cell .course-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.course-name {
  font-weight: 600;
  color: #1D3557;
  font-size: 15px;
  line-height: 1.4;
}

.course-details {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

.weight-cell .weight-display {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.weight-main {
  display: flex;
  align-items: center;
  gap: 8px;
}

.weight-badge {
  background: #457B9D;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  width: fit-content;
}

.weight-badge.normal {
  background: #457B9D;
}

.weight-badge.warning {
  background: #F4A261;
}

.weight-badge.over-limit {
  background: #E63946;
}

.weight-bar {
  height: 8px;
  background: #E5E5E5;
  border-radius: 4px;
  overflow: hidden;
  flex-grow: 1;
}

.weight-progress-fill {
  height: 100%;
  background: #457B9D;
  transition: width 0.3s ease;
  border-radius: 4px;
}

.weight-progress-fill.warning {
  background: #F4A261;
}

.weight-progress-fill.over-limit {
  background: #E63946;
}

.weight-remaining {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

/* Action Buttons */
.actions-cell {
  white-space: nowrap;
}

.action-buttons {
  display: flex;
  gap: 6px;
  align-items: center;
}

.action-btn-small {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 12px;
  font-weight: 600;
  position: relative;
}

.action-btn-small svg {
  width: 14px;
  height: 14px;
}

.action-btn-small.primary {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  box-shadow: 0 2px 6px rgba(69, 123, 157, 0.3);
}

.action-btn-small.primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.4);
}

.action-btn-small.info {
  background: linear-gradient(135deg, #17a2b8, #138496);
  color: white;
  box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
}

.action-btn-small.info:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
}

.action-btn-small.success {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
}

.action-btn-small.success:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.action-btn-small.danger {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
}

.action-btn-small.danger:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.action-btn-small:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Tooltip for action buttons */
.action-btn-small[title]:hover::after {
  content: attr(title);
  position: absolute;
  bottom: -35px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
  z-index: 100;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateX(-50%) translateY(-5px); }
  to { opacity: 1; transform: translateX(-50%) translateY(0); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #495057;
  font-size: 18px;
}

.empty-state p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

/* Modal Styles - Enhanced for smooth animations */
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
  backdrop-filter: blur(4px);
  /* Hardware acceleration for smoother performance */
  will-change: opacity;
  animation: modalOverlayFadeIn 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes modalOverlayFadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(4px);
  }
}

.modal-overlay.modal-exit {
  animation: modalOverlayFadeOut 0.15s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes modalOverlayFadeOut {
  from {
    opacity: 1;
    backdrop-filter: blur(4px);
  }
  to {
    opacity: 0;
    backdrop-filter: blur(0px);
  }
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px 28px;
  min-width: 340px;
  max-width: 95vw;
  max-height: 95vh;
  overflow-y: auto;
  box-shadow: 
    0 8px 32px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.05);
  /* Hardware acceleration and smooth transforms */
  will-change: transform, opacity;
  transform-origin: center center;
  animation: modalContentSlideIn 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
  display: flex;
  flex-direction: column;
  /* Smooth scrolling */
  scroll-behavior: smooth;
}

@keyframes modalContentSlideIn {
  0% {
    opacity: 0;
    transform: scale(0.85) translateY(40px);
  }
  60% {
    opacity: 0.8;
    transform: scale(1.02) translateY(-5px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-content.modal-exit {
  animation: modalContentSlideOut 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes modalContentSlideOut {
  from {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
  to {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
}

/* Enhanced Modal Variations */
.add-modal, .edit-modal {
  min-width: 500px;
  max-width: 600px;
  animation-duration: 0.3s;
}

.view-modal {
  min-width: 700px;
  max-width: 900px;
  animation-duration: 0.3s;
}

.delete-modal {
  min-width: 420px;
  animation-duration: 0.25s;
  text-align: center;
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
  transition: border-color 0.2s ease;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
  animation: titleFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
}

@keyframes titleFadeIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  color: #6c757d;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  transform: scale(1);
}

.close-btn:hover {
  background: #f8f9fa;
  color: #495057;
  transform: scale(1.05);
}

.close-btn:active {
  transform: scale(0.95);
}

.close-btn svg {
  width: 20px;
  height: 20px;
  transition: transform 0.2s ease;
}

.close-btn:hover svg {
  transform: rotate(90deg);
}

/* Course Info Header in Modal */
.course-info-header {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
}

.course-info-header .course-name {
  font-weight: 600;
  color: #1D3557;
  font-size: 16px;
  margin-bottom: 8px;
}

.course-info-header .weight-summary {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #6c757d;
}

.weight-used {
  color: #457B9D;
  font-weight: 600;
}

.weight-remaining {
  color: #6c757d;
}

/* Weight Progress Bar */
.weight-progress {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.progress-bar {
  height: 8px;
  background: #E5E5E5;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #457B9D;
  transition: width 0.3s ease;
  border-radius: 4px;
}

.progress-fill.over-limit {
  background: #E63946;
}

.progress-text {
  font-size: 12px;
  color: #6c757d;
  text-align: center;
}

/* Assessment Form */
.assessment-form {
  width: 100%;
}

.form-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 6px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.form-group input {
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-group input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 
    0 0 0 3px rgba(69, 123, 157, 0.1),
    0 4px 12px rgba(69, 123, 157, 0.15);
  transform: translateY(-1px);
}

.form-group small {
  margin-top: 4px;
  font-size: 12px;
}

.warning {
  color: #E63946;
  font-weight: 500;
}

/* Assessment Table in Modal */
.assessments-list {
  margin-top: 16px;
}

.assessments-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.assessments-header h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1D3557;
}

.add-assessment-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.add-assessment-btn:hover {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.4);
}

.add-assessment-btn .btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.table-wrapper {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #f1f3f4;
}

.assessment-table {
  width: 100%;
  border-collapse: collapse;
}

.assessment-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.assessment-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.assessment-table tbody tr:hover {
  background: #f8f9fa;
}

.component-info .component-name {
  font-weight: 600;
  color: #1D3557;
}

.weight-badge {
  background: #457B9D;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.graded-count {
  color: #6c757d;
  font-weight: 500;
}

/* Empty State in Modal */
.empty-state-modal {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.empty-state-modal .empty-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

.empty-state-modal h4 {
  margin: 0 0 8px 0;
  color: #495057;
  font-size: 16px;
}

.empty-state-modal p {
  margin: 0 0 20px 0;
  color: #6c757d;
  font-size: 14px;
}

.add-component-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  margin: 0 auto;
}

.add-component-btn:hover {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.4);
}

.btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* Delete Modal */
.delete-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #dc3545;
  animation: deleteIconPulse 0.6s ease-out;
}

@keyframes deleteIconPulse {
  0% {
    transform: scale(0.8);
    opacity: 0.7;
  }
  50% {
    transform: scale(1.1);
    opacity: 1;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.delete-icon svg {
  width: 40px;
  height: 40px;
  transition: transform 0.2s ease;
}

.delete-modal h4 {
  animation: slideUpFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
}

.delete-modal p {
  animation: slideUpFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) 0.15s both;
}

.delete-modal .modal-actions {
  animation: slideUpFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

@keyframes slideUpFadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.warning-text {
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
  margin: 8px 0;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 32px;
}

.modal-actions button {
  transform: translateY(10px);
  opacity: 0;
  animation: buttonSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.modal-actions button:first-child {
  animation-delay: 0.15s;
}

.modal-actions button:last-child {
  animation-delay: 0.2s;
}

@keyframes buttonSlideUp {
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Enhanced Button Styles */
.save-btn, .delete-confirm-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
  font-size: 15px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.save-btn {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.3);
}

.save-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.4);
}

.save-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.save-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.3);
}

.delete-confirm-btn {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.delete-confirm-btn:hover {
  background: linear-gradient(135deg, #c82333, #a71e2a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.delete-confirm-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.cancel-btn {
  background: #f8f9fa;
  color: #6c757d;
  border: 1px solid #e1e5e9;
  padding: 14px 28px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 15px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.cancel-btn:hover {
  background: #e9ecef;
  border-color: #adb5bd;
  color: #495057;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.cancel-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

/* Toast Notifications */
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  padding: 16px 24px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  z-index: 1100;
  animation: toastSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-width: 400px;
}

@keyframes toastSlideIn {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.success-toast {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error-toast {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.toast-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    min-width: 0;
    margin: 20px;
    padding: 24px 20px;
    max-height: calc(100vh - 40px);
    border-radius: 12px;
  }
  
  .add-modal, .edit-modal, .view-modal {
    min-width: 0;
  }
  
  .delete-modal {
    min-width: 0;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .modal-actions button {
    width: 100%;
  }
  
  .course-info-header .weight-summary {
    flex-direction: column;
    gap: 4px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
  
  .action-btn-small {
    width: 100%;
    height: auto;
    padding: 8px 12px;
    border-radius: 4px;
  }
}

@media (max-width: 480px) {
  .modal-content {
    margin: 10px;
    padding: 20px 16px;
    max-height: calc(100vh - 20px);
  }
  
  .modal-header h4 {
    font-size: 18px;
  }
  
  .delete-modal .delete-icon {
    width: 60px;
    height: 60px;
  }
  
  .delete-modal .delete-icon svg {
    width: 32px;
    height: 32px;
  }
  
  .toast {
    bottom: 16px;
    right: 16px;
    left: 16px;
    max-width: none;
  }
}

/* Performance Optimizations */
@media (prefers-reduced-motion: reduce) {
  .modal-overlay,
  .modal-content,
  .delete-icon,
  .modal-actions button,
  .close-btn,
  .save-btn,
  .delete-confirm-btn,
  .cancel-btn,
  .action-btn-small,
  .toast {
    animation: none !important;
    transition: none !important;
  }
  
  .modal-content {
    transform: none !important;
  }
  
  .close-btn:hover svg {
    transform: none !important;
  }
}
</style>
