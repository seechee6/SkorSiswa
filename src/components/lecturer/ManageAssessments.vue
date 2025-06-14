<template>
  <div>
    <h3>Manage Assessments (70%)</h3>
    
    <!-- Course Selection -->
    <div class="card course-selection">
      <h4>Select Course</h4>
      <select v-model="selectedCourseId" @change="fetchAssessments" class="course-select">
        <option value="">Select a course...</option>
        <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <!-- Weight Summary -->
    <div class="card weight-summary" v-if="selectedCourseId">
      <div class="weight-info">
        <div class="weight-used">
          <span class="weight-label">Total Weight Used:</span>
          <span class="weight-value" :class="{ 'over-limit': totalWeight > 70 }">
            {{ totalWeight.toFixed(1) }}%
          </span>
        </div>
        <div class="weight-remaining">
          <span class="weight-label">Remaining:</span>
          <span class="weight-value" :class="{ 'negative': remainingWeight < 0 }">
            {{ remainingWeight.toFixed(1) }}%
          </span>
        </div>
        <div class="weight-progress">
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: Math.min(totalWeight, 70) + '%' }"
              :class="{ 'over-limit': totalWeight > 70 }"
            ></div>
          </div>
          <span class="progress-text">70% Maximum</span>
        </div>
      </div>
    </div>

    <!-- Add Assessment Form -->
    <div class="card add-form" v-if="selectedCourseId">
      <h4>Add Assessment Component</h4>
      <form @submit.prevent="addAssessment">
        <div class="form-grid">
          <div class="form-group">
            <label>Component Name</label>
            <input 
              v-model="newAssessment.name" 
              placeholder="e.g., Quiz 1, Assignment 1" 
              required 
            />
          </div>
          <div class="form-group">
            <label>Weight (%)</label>
            <input 
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
            <label>Maximum Mark</label>
            <input 
              v-model.number="newAssessment.max_mark" 
              type="number" 
              step="0.1" 
              min="0" 
              placeholder="100" 
              required 
            />
          </div>
        </div>
        <button 
          type="submit" 
          :disabled="!canAddAssessment"
          class="add-btn"
        >
          Add Component
        </button>
      </form>
      <div v-if="success" class="success">{{ success }}</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>

    <!-- Assessment Components Table -->
    <div class="card" v-if="selectedCourseId">
      <div class="table-header">
        <h4>Assessment Components</h4>
        <div class="table-actions">
          <span class="component-count">{{ assessments.length }} components</span>
        </div>
      </div>
      
      <div class="table-wrapper" v-if="assessments.length > 0">
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
            <tr v-for="assessment in assessments" :key="assessment.id">
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
                  <button @click="editAssessment(assessment)" class="edit-btn">
                    Edit
                  </button>
                  <button 
                    @click="deleteAssessment(assessment.id, assessment.name)" 
                    class="danger"
                    :disabled="assessment.graded_count > 0"
                    :title="assessment.graded_count > 0 ? 'Cannot delete: Students have been graded' : 'Delete component'"
                  >
                    Delete
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        <h4>No Assessment Components</h4>
        <p>Add assessment components to start grading students.</p>
      </div>
    </div>

    <!-- Course Not Selected -->
    <div class="card empty-state" v-else>
      <div class="empty-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h4>Select a Course</h4>
        <p>Choose a course to manage its assessment components.</p>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal">
      <div class="modal-content">
        <h4>Edit Assessment Component</h4>
        <form @submit.prevent="updateAssessment">
          <div class="form-group">
            <label>Component Name</label>
            <input v-model="editAssessmentData.name" required />
          </div>
          <div class="form-group">
            <label>Weight (%)</label>
            <input 
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
            <label>Maximum Mark</label>
            <input 
              v-model.number="editAssessmentData.max_mark" 
              type="number" 
              step="0.1" 
              min="0" 
              required 
            />
          </div>
          <div class="modal-actions">
            <button type="submit" :disabled="!canUpdateAssessment">Save Changes</button>
            <button type="button" @click="showEditModal = false">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal">
      <div class="modal-content">
        <h4>Confirm Deletion</h4>
        <p>Are you sure you want to delete the assessment component <strong>{{ assessmentToDelete.name }}</strong>?</p>
        <p class="warning-text">This action cannot be undone.</p>
        <div class="modal-actions">
          <button @click="confirmDeleteAssessment" class="danger">Yes, Delete</button>
          <button @click="showDeleteModal = false">Cancel</button>
        </div>
      </div>
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
      assessments: [],
      selectedCourseId: '',
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
      showEditModal: false,
      showDeleteModal: false,
      assessmentToDelete: { id: null, name: '' },
      error: '',
      success: ''
    }
  },
  computed: {
    totalWeight() {
      return this.assessments.reduce((sum, assessment) => sum + Number(assessment.weight), 0)
    },
    remainingWeight() {
      return 70 - this.totalWeight
    },
    weightWarning() {
      if (!this.newAssessment.weight) return ''
      const newTotal = this.totalWeight + Number(this.newAssessment.weight)
      if (newTotal > 70) {
        return `This would exceed the 70% limit by ${(newTotal - 70).toFixed(1)}%`
      }
      return ''
    },
    editWeightWarning() {
      if (!this.editAssessmentData.weight) return ''
      const currentWeight = this.assessments.find(a => a.id === this.editAssessmentData.id)?.weight || 0
      const newTotal = this.totalWeight - currentWeight + Number(this.editAssessmentData.weight)
      if (newTotal > 70) {
        return `This would exceed the 70% limit by ${(newTotal - 70).toFixed(1)}%`
      }
      return ''
    },
    canAddAssessment() {
      if (!this.newAssessment.name || !this.newAssessment.weight || !this.newAssessment.max_mark) {
        return false
      }
      const newTotal = this.totalWeight + Number(this.newAssessment.weight)
      return newTotal <= 70
    },
    canUpdateAssessment() {
      if (!this.editAssessmentData.name || !this.editAssessmentData.weight || !this.editAssessmentData.max_mark) {
        return false
      }
      const currentWeight = this.assessments.find(a => a.id === this.editAssessmentData.id)?.weight || 0
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
      } catch (e) {
        this.error = 'Failed to load courses.'
      }
    },
    async fetchAssessments() {
      if (!this.selectedCourseId) {
        this.assessments = []
        return
      }
      
      try {
        const res = await api.get(`/courses/${this.selectedCourseId}/assessments`)
        this.assessments = res.data
        
        // Fetch graded count for each assessment (simplified - would need proper API endpoint)
        for (let assessment of this.assessments) {
          assessment.graded_count = Math.floor(Math.random() * 20) // Mock data
        }
      } catch (e) {
        this.error = 'Failed to load assessments.'
      }
    },
    async addAssessment() {
      this.error = ''
      this.success = ''
      
      try {
        await api.post(`/courses/${this.selectedCourseId}/assessments`, {
          name: this.newAssessment.name,
          weight: this.newAssessment.weight,
          max_mark: this.newAssessment.max_mark
        })
        
        this.success = 'Assessment component added successfully!'
        this.newAssessment = { name: '', weight: null, max_mark: null }
        this.fetchAssessments()
        
        setTimeout(() => {
          this.success = ''
        }, 3000)
        
      } catch (e) {
        this.error = 'Failed to add assessment component.'
      }
    },
    editAssessment(assessment) {
      this.editAssessmentData = { ...assessment }
      this.showEditModal = true
    },
    async updateAssessment() {
      this.error = ''
      
      try {
        await api.put(`/assessments/${this.editAssessmentData.id}`, {
          name: this.editAssessmentData.name,
          weight: this.editAssessmentData.weight,
          max_mark: this.editAssessmentData.max_mark
        })
        
        this.showEditModal = false
        this.fetchAssessments()
        
      } catch (e) {
        this.error = 'Failed to update assessment component.'
      }
    },
    deleteAssessment(id, name) {
      this.assessmentToDelete = { id, name }
      this.showDeleteModal = true
    },
    async confirmDeleteAssessment() {
      try {
        await api.delete(`/assessments/${this.assessmentToDelete.id}`)
        this.showDeleteModal = false
        this.assessmentToDelete = { id: null, name: '' }
        this.fetchAssessments()
      } catch (e) {
        this.error = 'Failed to delete assessment component.'
        this.showDeleteModal = false
      }
    }
  },
  mounted() {
    this.fetchLecturerCourses()
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

.course-selection {
  margin-bottom: 24px;
}

.course-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.weight-summary {
  background: linear-gradient(135deg, #F1FAEE 0%, #E8F5E8 100%);
  border-left: 4px solid #457B9D;
}

.weight-info {
  display: grid;
  grid-template-columns: auto auto 1fr;
  gap: 24px;
  align-items: center;
}

.weight-used, .weight-remaining {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.weight-label {
  font-size: 12px;
  color: #6c757d;
  margin-bottom: 4px;
  text-transform: uppercase;
  font-weight: 600;
}

.weight-value {
  font-size: 24px;
  font-weight: 700;
  color: #1D3557;
}

.weight-value.over-limit {
  color: #E63946;
}

.weight-value.negative {
  color: #E63946;
}

.weight-progress {
  display: flex;
  flex-direction: column;
  justify-self: end;
  min-width: 200px;
}

.progress-bar {
  height: 8px;
  background: #E5E5E5;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 4px;
}

.progress-fill {
  height: 100%;
  background: #457B9D;
  transition: width 0.3s ease;
}

.progress-fill.over-limit {
  background: #E63946;
}

.progress-text {
  font-size: 12px;
  color: #6c757d;
  text-align: right;
}

.form-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #1D3557;
  margin-bottom: 4px;
}

.form-group input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.form-group small {
  margin-top: 4px;
  font-size: 12px;
}

.warning {
  color: #E63946;
}

.add-btn {
  background: #457B9D;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.add-btn:hover:not(:disabled) {
  background: #1D3557;
}

.add-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.component-count {
  font-size: 14px;
  color: #6c757d;
}

.assessment-table {
  width: 100%;
  border-collapse: collapse;
}

.assessment-table th,
.assessment-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.assessment-table th {
  background: #F1FAEE;
  font-weight: 600;
  color: #1D3557;
}

.component-name {
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
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.edit-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.edit-btn:hover {
  background: #2980b9;
}

button.danger {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

button.danger:hover:not(:disabled) {
  background: #c0392b;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
}

.empty-content {
  max-width: 300px;
  margin: 0 auto;
}

.empty-icon {
  width: 64px;
  height: 64px;
  color: #ccc;
  margin: 0 auto 16px;
}

.empty-state h4 {
  color: #1D3557;
  margin-bottom: 8px;
}

.empty-state p {
  color: #6c757d;
  font-size: 14px;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  min-width: 400px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
}

.warning-text {
  color: #E63946;
  font-size: 14px;
  margin: 8px 0;
}

.success {
  color: #27ae60;
  margin-top: 8px;
  font-weight: 500;
}

.error {
  color: #e74c3c;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .weight-info {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .weight-progress {
    justify-self: auto;
  }
}
</style>
