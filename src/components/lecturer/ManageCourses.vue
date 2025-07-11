<template>
  <div>
    <div class="header-section">
      <h3>Manage Courses</h3>
      <button @click="showAddForm = !showAddForm" class="add-btn">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        {{ showAddForm ? 'Cancel' : 'Add New Course' }}
      </button>
    </div>

    <!-- Add New Course Form (collapsible) -->
    <div class="card add-form" v-show="showAddForm">
      <h4>Add New Course</h4>
      <form @submit.prevent="createCourse" class="course-form">
        <div class="form-grid">
          <div class="form-group">
            <label for="code">Course Code</label>
            <input 
              id="code"
              v-model="newCourse.code" 
              placeholder="e.g. CS101" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="name">Course Name</label>
            <input 
              id="name"
              v-model="newCourse.name" 
              placeholder="e.g. Introduction to Computer Science" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester" v-model="newCourse.semester" required>
              <option value="">Select Semester</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
              <option value="3">Semester 3</option>
            </select>
          </div>
          <div class="form-group">
            <label for="year">Academic Year</label>
            <select id="year" v-model="newCourse.year" required>
              <option value="">Select Academic Year</option>
              <option v-for="year in academicYears" :key="year.value" :value="year.value">
                {{ year.label }}
              </option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Course
          </button>
          <button type="button" @click="cancelAdd" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Courses Table -->
    <div class="card">
      <div class="table-header">
        <h4>Your Courses</h4>
        <span class="course-count">{{ courses.length }} courses</span>
      </div>
      
      <div class="table-container">
        <table class="course-table">
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="course in courses" :key="course.id" class="course-row">
              <td class="code-cell">
                <span class="course-code">{{ course.code }}</span>
              </td>
              <td class="name-cell">
                <div class="course-name">{{ course.name }}</div>
              </td>
              <td class="semester-cell">
                <span class="semester-badge">Semester {{ course.semester }}</span>
              </td>
              <td class="year-cell">{{ course.year }}</td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button @click="editCourse(course)" class="action-btn-small info" title="Edit Course">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  
                  <button @click="openEnrollModal(course)" class="action-btn-small primary" title="Enroll Students">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                  </button>
                  
                  <button @click="viewCourseEnrollments(course)" class="action-btn-small success" title="View Enrolled Students">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  
                  <button @click="confirmDelete(course)" class="action-btn-small danger" title="Delete Course">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Empty State -->
        <div v-if="courses.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <h4>No Courses Yet</h4>
          <p>Click "Add New Course" to create your first course.</p>
        </div>
      </div>
      
      <div v-if="error" class="error-message">{{ error }}</div>
      <div v-if="success" class="success-message">{{ success }}</div>
    </div>

    <!-- Edit Course Modal -->
    <div v-if="editing" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content edit-modal">
        <div class="modal-header">
          <h4>Edit Course</h4>
          <button @click="closeEditModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <form @submit.prevent="updateCourse" class="course-form">
          <div class="form-grid">
            <div class="form-group">
              <label for="edit-code">Course Code</label>
              <input 
                id="edit-code"
                v-model="editCourseData.code" 
                placeholder="Course Code" 
                required 
              />
            </div>
            <div class="form-group">
              <label for="edit-name">Course Name</label>
              <input 
                id="edit-name"
                v-model="editCourseData.name" 
                placeholder="Course Name" 
                required 
              />
            </div>
            <div class="form-group">
              <label for="edit-semester">Semester</label>
              <select id="edit-semester" v-model="editCourseData.semester" required>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
              </select>
            </div>
            <div class="form-group">
              <label for="edit-year">Academic Year</label>
              <select id="edit-year" v-model="editCourseData.year" required>
                <option v-for="year in academicYears" :key="year.value" :value="year.value">
                  {{ year.label }}
                </option>
              </select>
            </div>
          </div>
          <div class="modal-actions">
            <button type="submit" class="save-btn">
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
    <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="closeDeleteConfirm">
      <div class="modal-content delete-modal">
        <div class="delete-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h4>Delete Course</h4>
        <p>Are you sure you want to delete <strong>{{ courseToDelete?.name }}</strong>?</p>
        <p class="warning-text">This action cannot be undone and will remove all associated data.</p>
        <div class="modal-actions">
          <button @click="deleteCourse" class="delete-confirm-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete Course
          </button>
          <button @click="closeDeleteConfirm" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Enrollment Modal - This should appear as a centered popup -->
    <ManageEnrollment 
      v-if="showEnrollmentModal"
      :isModalOnly="true"
      :selectedCourse="selectedCourse"
      @enrollment-success="handleEnrollmentSuccess"
      @enrollment-error="handleEnrollmentError"
      @enrollment-closed="handleEnrollmentClosed"
    />
  </div>
</template>
<script>
import api from '../../api'
import ManageEnrollment from './ManageEnrollment.vue'

export default {
  name: 'ManageCourses',
  components: {
    ManageEnrollment
  },
  data() {
    return {
      courses: [],
      enrollments: [],
      allStudents: [],
      error: '',
      success: '',
      showAddForm: false,
      showDeleteConfirm: false,
      courseToDelete: null,
      editing: false,
      newCourse: { 
        code: '', 
        name: '', 
        semester: '', 
        year: ''
      },
      editCourseData: { 
        id: '', 
        code: '', 
        name: '', 
        semester: '', 
        year: '' 
      },
      // Enrollment functionality
      showEnrollmentModal: false,
      selectedCourse: null
    }
  },
  
  computed: {
    academicYears() {
      const currentYear = new Date().getFullYear()
      return Array.from({ length: 5 }, (_, i) => {
        const year = currentYear + i - 1
        return { 
          value: `${year}/${year + 1}`, 
          label: `${year}/${year + 1}` 
        }
      })
    },

    selectedCourseName() {
      const course = this.courses.find(c => c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    },
    
    filteredStudents() {
      let students = [...this.allStudents]
      
      if (this.selectedCourseId) {
        const enrolledStudentIds = this.enrollments
          .filter(e => e.course_id == this.selectedCourseId)
          .map(e => e.student_id)
        
        students = students.filter(student => !enrolledStudentIds.includes(student.id))
      }
      
      if (this.studentSearchTerm && this.studentSearchTerm.length > 0) {
        const term = this.studentSearchTerm.toLowerCase()
        students = students.filter(student => 
          student.full_name.toLowerCase().includes(term) ||
          student.matric_no.toLowerCase().includes(term)
        )
      }
      
      return students.slice(0, 10)
    },

    availableStudents() {
      if (!this.selectedCourseId) return []
      
      const enrolledStudentIds = this.enrollments
        .filter(e => e.course_id == this.selectedCourseId)
        .map(e => e.student_id)
      
      return this.allStudents.filter(student => 
        !enrolledStudentIds.includes(student.id)
      )
    },

    canEnroll() {
      if (this.enrollmentMethod === 'single') {
        return !!this.selectedStudentId
      } else if (this.enrollmentMethod === 'bulk') {
        return this.bulkSelectedStudents.length > 0
      } else if (this.enrollmentMethod === 'csv') {
        return this.csvPreview.length > 0
      }
      return false
    },

    getEnrollButtonText() {
      if (this.enrollmentMethod === 'single') {
        return 'Enroll Student'
      } else if (this.enrollmentMethod === 'bulk') {
        return `Enroll Students (${this.bulkSelectedStudents.length})`
      } else if (this.enrollmentMethod === 'csv') {
        return `Enroll Students (${this.csvPreview.length})`
      }
      return 'Enroll'
    }
  },
  
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        
        if (!user || !user.id) {
          this.showError('User session expired. Please login again.')
          return
        }
        
        console.log('Fetching courses for user:', user.id) // Debug log
        
        // Fetch courses first (most important)
        const coursesRes = await api.get('/courses')
        console.log('All courses from API:', coursesRes.data) // Debug log
        
        // Filter courses for current lecturer
        this.courses = coursesRes.data.filter(c => c.lecturer_id == user.id)
        console.log('Filtered courses for lecturer:', this.courses) // Debug log
        
        // Fetch additional data (these are less critical)
        try {
          const enrollmentsRes = await api.get('/enrollments')
          this.enrollments = enrollmentsRes.data || []
        } catch (e) {
          console.warn('Failed to fetch enrollments:', e)
          this.enrollments = []
        }
        
        try {
          const studentsRes = await api.get('/students')
          this.allStudents = studentsRes.data || []
        } catch (e) {
          console.warn('Failed to fetch students:', e)
          this.allStudents = []
        }
        
        this.clearMessages()
        
      } catch (e) {
        console.error('Error fetching courses:', e) // Debug log
        console.error('Error details:', e.response?.data) // Debug log
        this.showError('Failed to load courses. Please try again.')
      }
    },
    
    async createCourse() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post('/courses', {
          ...this.newCourse,
          lecturer_id: user.id
        })
        
        this.resetNewCourse()
        this.showAddForm = false
        this.showSuccess('Course created successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to create course. Please check your input and try again.')
      }
    },
    
    editCourse(course) {
      this.editing = true
      this.editCourseData = { ...course }
      this.clearMessages()
    },
    
    async updateCourse() {
      try {
        await api.put(`/courses/${this.editCourseData.id}`, this.editCourseData)
        this.editing = false
        this.showSuccess('Course updated successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to update course. Please try again.')
      }
    },
    
    confirmDelete(course) {
      this.showDeleteConfirm = true
      this.courseToDelete = course
      this.clearMessages()
    },
    
    async deleteCourse() {
      try {
        await api.delete(`/courses/${this.courseToDelete.id}`)
        this.closeDeleteConfirm()
        this.showSuccess('Course deleted successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to delete course. Please try again.')
      }
    },
    
    cancelAdd() {
      this.showAddForm = false
      this.resetNewCourse()
      this.clearMessages()
    },
    
    closeEditModal() {
      this.editing = false
      this.clearMessages()
    },
    
    closeDeleteConfirm() {
      this.showDeleteConfirm = false
      this.courseToDelete = null
    },
    
    resetNewCourse() {
      this.newCourse = { 
        code: '', 
        name: '', 
        semester: '', 
        year: ''
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
      this.error = ''
      this.success = ''
    },

    getCourseEnrollmentCount(courseId) {
      return this.enrollments.filter(e => e.course_id == courseId).length
    },

    openEnrollModal(course) {
      this.selectedCourse = course
      this.showEnrollmentModal = true
    },

    handleEnrollmentSuccess(message) {
      this.showSuccess(message)
      this.fetchCourses()
    },

    handleEnrollmentError(message) {
      this.showError(message)
    },

    handleEnrollmentClosed() {
      this.selectedCourse = null
      this.showEnrollmentModal = false
    },

    viewCourseEnrollments(course) {
      this.$router.push(`/lecturer/view-enrolled/${course.id}`)
    }
  },
  
  mounted() {
    this.fetchCourses()
  }
}
</script>
<style scoped>
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-section h3 {
  margin: 0;
  color: #1D3557;
  font-size: 28px;
}

.add-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
}

.add-btn:hover {
  background: linear-gradient(135deg, #229954, #27ae60);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(46, 204, 113, 0.4);
}

.btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
}

.add-form {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.course-form {
  margin-top: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 4px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.form-group input,
.form-group select {
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.submit-btn {
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
}

.submit-btn:hover {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-1px);
}

.cancel-btn {
  background: #6c757d;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: #5a6268;
}

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
}

.course-count {
  background: #F1FAEE;
  color: #1D3557;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.table-container {
  overflow-x: auto;
}

.course-table {
  width: 100%;
  border-collapse: collapse;
}

.course-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 16px 12px;
  text-align: left;
  border-bottom: 2px solid #e9ecef;
}

.course-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.course-row:hover {
  background: #f8f9fa;
}

.course-code {
  font-weight: 700;
  color: #1D3557;
  padding: 0;
  border-radius: 0;
  background: none;
  font-size: 14px;
  letter-spacing: 0.5px;
}

.course-name {
  font-weight: 600;
  color: #1D3557;
  line-height: 1.4;
  font-size: 15px;
}

.semester-badge {
  background: none;
  color: #1D3557;
  border-radius: 0;
  font-size: 14px;
  font-weight: 600;
  border: none;
  padding: 0;
}

.year-cell {
  color: #6c757d;
  font-size: 14px;
  font-weight: 600;
}

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

.action-btn-small.secondary {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
  box-shadow: 0 2px 6px rgba(108, 117, 125, 0.3);
}

.action-btn-small.secondary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
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

.action-btn-small.danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
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

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #495057;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.error-message,
.success-message {
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 16px;
  font-weight: 500;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success-message {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
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
  align-items: center;
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

/* Enhanced Edit Modal */
.edit-modal {
  min-width: 500px;
  max-width: 600px;
  animation-duration: 0.3s;
}

.edit-modal .form-grid {
  /* Prevent layout shift during animation */
  min-height: 120px;
}

.edit-modal .form-group {
  /* Smooth focus transitions */
  transition: all 0.15s ease;
}

.edit-modal .form-group input,
.edit-modal .form-group select {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.edit-modal .form-group input:focus,
.edit-modal .form-group select:focus {
  transform: translateY(-1px);
  box-shadow: 
    0 0 0 3px rgba(69, 123, 157, 0.1),
    0 4px 12px rgba(69, 123, 157, 0.15);
}

/* Enhanced Delete Modal */
.delete-modal {
  min-width: 420px;
  animation-duration: 0.25s;
  text-align: center;
}

.delete-modal .delete-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #dc3545;
  /* Icon animation */
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

.delete-modal .delete-icon svg {
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

/* Modal Header Enhancements */
.modal-header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
  /* Smooth border transition */
  transition: border-color 0.2s ease;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
  /* Text animation */
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
  /* Smooth hover effect */
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

/* Modal Actions Enhancements */
.modal-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 32px;
  /* Staggered button animation */
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

.save-btn:hover {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.4);
}

.save-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
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

/* Loading States */
.save-btn:disabled,
.delete-confirm-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

.save-btn:disabled:hover,
.delete-confirm-btn:disabled:hover {
  transform: none;
  box-shadow: none;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
  .modal-content {
    min-width: 0;
    margin: 20px;
    padding: 24px 20px;
    max-height: calc(100vh - 40px);
    border-radius: 12px;
  }
  
  .edit-modal {
    min-width: 0;
  }
  
  .delete-modal {
    min-width: 0;
  }
  
  .modal-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .modal-actions button {
    width: 100%;
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
  .cancel-btn {
    animation: none !important;
    transition: none !important;
  }
  
  .modal-content {
    transform: none !important;
  }
}
</style>
