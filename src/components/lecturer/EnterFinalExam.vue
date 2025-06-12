<template>
  <div>
    <h3>Enter Final Exam Marks (30%)</h3>
    
    <!-- Course Selection -->
    <div class="card course-selection">
      <h4>Select Course</h4>
      <select v-model="selectedCourseId" @change="fetchStudents" class="course-select">
        <option value="">Select a course...</option>
        <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <!-- Final Exam Instructions -->
    <div class="card instructions" v-if="selectedCourseId">
      <div class="instruction-header">
        <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h4>Final Exam Guidelines</h4>
      </div>
      <ul class="instruction-list">
        <li>Final exam carries <strong>30%</strong> of the total course grade</li>
        <li>Enter marks out of <strong>100</strong> (will be automatically weighted)</li>
        <li>Use inline editing or batch upload for efficiency</li>
        <li>Students will be notified automatically when marks are entered</li>
      </ul>
    </div>

    <!-- Students Table -->
    <div class="card" v-if="selectedCourseId">
      <div class="table-header">
        <h4>{{ selectedCourseName }} - Final Exam Marks</h4>
        <div class="table-controls">
          <input 
            v-model="searchTerm" 
            placeholder="Search students..."
            class="search-input"
          />
          <button 
            @click="showBatchModal = true" 
            class="batch-btn"
            :disabled="students.length === 0"
          >
            Batch Entry
          </button>
          <button 
            @click="submitAllMarks" 
            class="submit-all-btn"
            :disabled="!hasUnsavedChanges"
          >
            Submit All ({{ Object.keys(pendingMarks).length }})
          </button>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="progress-section" v-if="students.length > 0">
        <div class="progress-info">
          <span class="progress-label">Grading Progress:</span>
          <span class="progress-count">{{ gradedCount }} / {{ students.length }} students</span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: (gradedCount / students.length * 100) + '%' }"
          ></div>
        </div>
      </div>

      <!-- Students Table -->
      <div class="table-wrapper" v-if="filteredStudents.length > 0">
        <table class="marks-table">
          <thead>
            <tr>
              <th>Matric No</th>
              <th>Student Name</th>
              <th>Final Exam Mark</th>
              <th>Weighted (30%)</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="student in paginatedStudents" 
              :key="student.id"
              :class="{ 'has-changes': pendingMarks[student.enrollment_id] !== undefined }"
            >
              <td>{{ student.matric_no }}</td>
              <td>{{ student.student_name }}</td>
              <td>
                <div class="mark-input-wrapper">
                  <input 
                    v-model.number="student.final_mark"
                    @input="onMarkChange(student)"
                    type="number" 
                    step="0.1" 
                    min="0" 
                    max="100" 
                    class="mark-input"
                    :class="{ 
                      'has-error': getMarkError(student.final_mark),
                      'has-changes': pendingMarks[student.enrollment_id] !== undefined
                    }"
                    placeholder="0.0"
                  />
                  <span class="mark-suffix">/ 100</span>
                  <div v-if="getMarkError(student.final_mark)" class="input-error">
                    {{ getMarkError(student.final_mark) }}
                  </div>
                </div>
              </td>
              <td>
                <span class="weighted-mark">
                  {{ student.final_mark ? (student.final_mark * 0.3).toFixed(1) : '0.0' }}
                </span>
              </td>
              <td>
                <span 
                  class="status-badge" 
                  :class="getStatusClass(student)"
                >
                  {{ getStatus(student) }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button 
                    v-if="pendingMarks[student.enrollment_id] !== undefined"
                    @click="saveIndividualMark(student)"
                    class="save-btn"
                    title="Save this mark"
                  >
                    Save
                  </button>
                  <button 
                    v-if="student.original_mark !== null"
                    @click="resetMark(student)"
                    class="reset-btn"
                    title="Reset to original"
                  >
                    Reset
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

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

      <!-- Empty State -->
      <div v-else-if="selectedCourseId && students.length === 0" class="empty-state">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <h4>No Students Enrolled</h4>
        <p>No students are enrolled in this course.</p>
      </div>
    </div>

    <!-- Course Not Selected -->
    <div class="card empty-state" v-else>
      <div class="empty-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h4>Select a Course</h4>
        <p>Choose a course to enter final exam marks.</p>
      </div>
    </div>

    <!-- Batch Entry Modal -->
    <div v-if="showBatchModal" class="modal">
      <div class="modal-content batch-modal">
        <h4>Batch Mark Entry</h4>
        <p>Enter marks for all students separated by commas in the same order as displayed in the table.</p>
        
        <div class="batch-students">
          <h5>Student Order:</h5>
          <ol class="student-list">
            <li v-for="student in filteredStudents" :key="student.id">
              {{ student.matric_no }} - {{ student.student_name }}
            </li>
          </ol>
        </div>

        <div class="batch-input">
          <label>Enter marks (comma-separated):</label>
          <textarea 
            v-model="batchMarks"
            placeholder="85.5, 92.0, 78.5, 88.0, ..."
            rows="4"
            class="batch-textarea"
          ></textarea>
          <small>Example: 85.5, 92.0, 78.5, 88.0</small>
        </div>

        <div class="modal-actions">
          <button @click="applyBatchMarks" :disabled="!batchMarks.trim()">
            Apply Marks
          </button>
          <button @click="showBatchModal = false" type="button">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="success" class="floating-message success">
      {{ success }}
    </div>
    <div v-if="error" class="floating-message error">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api';

export default {
  name: 'EnterFinalExam',
  data() {
    return {
      lecturerCourses: [],
      students: [],
      selectedCourseId: '',
      searchTerm: '',
      pendingMarks: {},
      showBatchModal: false,
      batchMarks: '',
      currentPage: 1,
      itemsPerPage: 15,
      success: '',
      error: ''
    }
  },
  computed: {
    selectedCourseName() {
      const course = this.lecturerCourses.find(c => c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    },
    filteredStudents() {
      if (!this.searchTerm) return this.students
      
      const term = this.searchTerm.toLowerCase()
      return this.students.filter(student => 
        student.student_name.toLowerCase().includes(term) ||
        student.matric_no.toLowerCase().includes(term)
      )
    },
    paginatedStudents() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredStudents.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.itemsPerPage)
    },
    gradedCount() {
      return this.students.filter(s => s.final_mark !== null && s.final_mark !== '').length
    },
    hasUnsavedChanges() {
      return Object.keys(this.pendingMarks).length > 0
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
    async fetchStudents() {
      if (!this.selectedCourseId) {
        this.students = []
        return
      }

      try {
        const enrollmentsRes = await api.get(`/courses/${this.selectedCourseId}/enrollments`)
        this.students = enrollmentsRes.data.map(enrollment => ({
          ...enrollment,
          final_mark: enrollment.final_mark || null,
          original_mark: enrollment.final_mark || null
        }))
        this.pendingMarks = {}
        this.currentPage = 1
      } catch (e) {
        this.error = 'Failed to load students.'
      }
    },
    onMarkChange(student) {
      if (student.final_mark !== student.original_mark) {
        this.pendingMarks[student.enrollment_id] = student.final_mark
      } else {
        delete this.pendingMarks[student.enrollment_id]
      }
    },
    getMarkError(mark) {
      if (mark === null || mark === '' || mark === undefined) return ''
      if (isNaN(mark)) return 'Invalid number'
      if (mark < 0) return 'Mark cannot be negative'
      if (mark > 100) return 'Mark cannot exceed 100'
      return ''
    },
    getStatus(student) {
      if (this.pendingMarks[student.enrollment_id] !== undefined) {
        return 'Unsaved'
      }
      if (student.original_mark !== null) {
        return 'Graded'
      }
      return 'Pending'
    },
    getStatusClass(student) {
      const status = this.getStatus(student)
      return {
        'status-unsaved': status === 'Unsaved',
        'status-graded': status === 'Graded',
        'status-pending': status === 'Pending'
      }
    },
    async saveIndividualMark(student) {
      try {
        await api.post(`/enrollments/${student.enrollment_id}/final-mark`, {
          mark: student.final_mark
        })
        
        student.original_mark = student.final_mark
        delete this.pendingMarks[student.enrollment_id]
        this.showSuccess('Mark saved successfully!')
        
      } catch (e) {
        this.error = 'Failed to save mark.'
      }
    },
    async submitAllMarks() {
      if (Object.keys(this.pendingMarks).length === 0) return

      try {
        const promises = Object.entries(this.pendingMarks).map(([enrollmentId, mark]) => {
          return api.post(`/enrollments/${enrollmentId}/final-mark`, { mark })
        })

        await Promise.all(promises)

        // Update original marks
        this.students.forEach(student => {
          if (this.pendingMarks[student.enrollment_id] !== undefined) {
            student.original_mark = student.final_mark
          }
        })

        this.pendingMarks = {}
        this.showSuccess(`${promises.length} marks submitted successfully!`)

      } catch (e) {
        this.error = 'Failed to submit some marks. Please try again.'
      }
    },
    resetMark(student) {
      student.final_mark = student.original_mark
      delete this.pendingMarks[student.enrollment_id]
    },
    applyBatchMarks() {
      try {
        const marks = this.batchMarks.split(',').map(mark => {
          const trimmed = mark.trim()
          return trimmed === '' ? null : parseFloat(trimmed)
        })

        if (marks.length !== this.filteredStudents.length) {
          this.error = `Expected ${this.filteredStudents.length} marks, got ${marks.length}`
          return
        }

        marks.forEach((mark, index) => {
          if (mark !== null && (isNaN(mark) || mark < 0 || mark > 100)) {
            throw new Error(`Invalid mark at position ${index + 1}: ${mark}`)
          }
        })

        this.filteredStudents.forEach((student, index) => {
          student.final_mark = marks[index]
          this.onMarkChange(student)
        })

        this.showBatchModal = false
        this.batchMarks = ''
        this.showSuccess('Batch marks applied successfully!')

      } catch (e) {
        this.error = e.message || 'Failed to apply batch marks.'
      }
    },
    showSuccess(message) {
      this.success = message
      setTimeout(() => {
        this.success = ''
      }, 3000)
    }
  },
  mounted() {
    this.fetchLecturerCourses()
  },
  watch: {
    searchTerm() {
      this.currentPage = 1
    }
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

.course-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.instructions {
  background: linear-gradient(135deg, #E3F2FD 0%, #F1FAEE 100%);
  border-left: 4px solid #457B9D;
}

.instruction-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.info-icon {
  width: 24px;
  height: 24px;
  color: #457B9D;
}

.instruction-list {
  margin: 0;
  padding-left: 20px;
}

.instruction-list li {
  margin: 8px 0;
  color: #1D3557;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.table-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-input {
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 200px;
}

.batch-btn, .submit-all-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.batch-btn {
  background: #3498db;
  color: white;
}

.batch-btn:hover:not(:disabled) {
  background: #2980b9;
}

.submit-all-btn {
  background: #27ae60;
  color: white;
}

.submit-all-btn:hover:not(:disabled) {
  background: #219a52;
}

.submit-all-btn:disabled, .batch-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.progress-section {
  margin-bottom: 20px;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.progress-label {
  font-weight: 600;
  color: #1D3557;
}

.progress-count {
  color: #6c757d;
  font-size: 14px;
}

.progress-bar {
  height: 8px;
  background: #E5E5E5;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #457B9D 0%, #27ae60 100%);
  transition: width 0.3s ease;
}

.marks-table {
  width: 100%;
  border-collapse: collapse;
}

.marks-table th,
.marks-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.marks-table th {
  background: #F1FAEE;
  font-weight: 600;
  color: #1D3557;
}

.marks-table tr.has-changes {
  background: rgba(52, 152, 219, 0.05);
}

.mark-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
}

.mark-input {
  width: 80px;
  padding: 6px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  text-align: center;
}

.mark-input.has-error {
  border-color: #E63946;
}

.mark-input.has-changes {
  border-color: #3498db;
  background: rgba(52, 152, 219, 0.1);
}

.mark-suffix {
  color: #6c757d;
  font-size: 12px;
}

.input-error {
  position: absolute;
  top: 100%;
  left: 0;
  font-size: 11px;
  color: #E63946;
  margin-top: 2px;
}

.weighted-mark {
  font-weight: 600;
  color: #1D3557;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: #FEF3C7;
  color: #92400E;
}

.status-graded {
  background: #D1FAE5;
  color: #065F46;
}

.status-unsaved {
  background: #DBEAFE;
  color: #1E40AF;
}

.action-buttons {
  display: flex;
  gap: 6px;
}

.save-btn, .reset-btn {
  padding: 4px 8px;
  border: none;
  border-radius: 3px;
  font-size: 11px;
  cursor: pointer;
}

.save-btn {
  background: #27ae60;
  color: white;
}

.reset-btn {
  background: #95a5a6;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
}

.pagination-btn {
  padding: 8px 16px;
  border: 1px solid #3498db;
  background: white;
  color: #3498db;
  border-radius: 4px;
  cursor: pointer;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn:not(:disabled):hover {
  background: #3498db;
  color: white;
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

.batch-modal {
  background: white;
  padding: 24px;
  border-radius: 8px;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
}

.batch-students {
  margin: 16px 0;
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #eee;
  border-radius: 4px;
  padding: 12px;
}

.student-list {
  margin: 0;
  padding-left: 20px;
  font-size: 12px;
}

.batch-input {
  margin: 16px 0;
}

.batch-textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-family: monospace;
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
}

.floating-message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 4px;
  font-weight: 500;
  z-index: 1001;
  animation: slideIn 0.3s ease;
}

.floating-message.success {
  background: #D1FAE5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.floating-message.error {
  background: #FEE2E2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .table-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .table-controls {
    justify-content: space-between;
  }
  
  .search-input {
    width: 150px;
  }
}
</style>
