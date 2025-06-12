<template>
  <div>
    <h3>Manage Student Enrollment</h3>
    
    <!-- Course Selection -->
    <div class="card course-selection">
      <h4>Select Course</h4>
      <select v-model="selectedCourseId" @change="fetchEnrollments" class="course-select">
        <option value="">Select a course...</option>
        <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <!-- Enroll Student Form -->
    <div class="card add-form">
      <h4>Enroll Student</h4>
      <form @submit.prevent="enrollStudent">
        <div class="form-row">
          <div class="student-search">
            <input 
              v-model="studentSearchTerm" 
              @input="searchStudents"
              placeholder="Search student by name or matric number" 
              required 
            />
            <div v-if="searchResults.length > 0" class="search-dropdown">
              <div 
                v-for="student in searchResults" 
                :key="student.id"
                @click="selectStudent(student)"
                class="search-result-item"
              >
                <div class="student-info">
                  <div class="student-name">{{ student.full_name }}</div>
                  <div class="student-matric">{{ student.matric_no }}</div>
                </div>
              </div>
            </div>
          </div>
          <select v-model="enrollmentCourseId" required class="course-select-small">
            <option value="">Select course</option>
            <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
              {{ course.code }}
            </option>
          </select>
          <button type="submit" :disabled="!selectedStudentId || !enrollmentCourseId">
            Enroll Student
          </button>
        </div>
      </form>
      <div v-if="enrollmentSuccess" class="success">Student enrolled successfully!</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>

    <!-- Enrolled Students Table -->
    <div class="card" v-if="selectedCourseId">
      <div class="table-header">
        <h4>Enrolled Students - {{ selectedCourseName }}</h4>
        <div class="search-controls">
          <input 
            v-model="enrollmentSearchTerm" 
            placeholder="Search enrolled students..."
            class="search-input"
          />
          <span class="student-count">{{ filteredEnrollments.length }} students</span>
        </div>
      </div>
      
      <div class="table-wrapper">
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Matric No</th>
              <th>Student Name</th>
              <th>Email</th>
              <th>Enrollment Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="enrollment in paginatedEnrollments" :key="enrollment.id">
              <td>{{ enrollment.matric_no }}</td>
              <td>{{ enrollment.student_name }}</td>
              <td>{{ enrollment.email || 'N/A' }}</td>
              <td>{{ formatDate(enrollment.created_at) }}</td>
              <td>
                <button 
                  @click="removeEnrollment(enrollment.id, enrollment.student_name)" 
                  class="danger"
                  title="Remove student from course"
                >
                  Remove
                </button>
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
    </div>

    <!-- No Course Selected Message -->
    <div class="card empty-state" v-else>
      <div class="empty-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h4>Select a Course</h4>
        <p>Choose a course from the dropdown above to view and manage enrollments.</p>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showRemoveModal" class="modal">
      <div class="modal-content">
        <h4>Confirm Removal</h4>
        <p>Are you sure you want to remove <strong>{{ studentToRemove.name }}</strong> from this course?</p>
        <div class="modal-actions">
          <button @click="confirmRemoveEnrollment" class="danger">Yes, Remove</button>
          <button @click="showRemoveModal = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'ManageEnrollment',
  data() {
    return {
      lecturerCourses: [],
      enrollments: [],
      allStudents: [],
      searchResults: [],
      selectedCourseId: '',
      enrollmentCourseId: '',
      selectedStudentId: '',
      studentSearchTerm: '',
      enrollmentSearchTerm: '',
      enrollmentSuccess: false,
      error: '',
      currentPage: 1,
      itemsPerPage: 10,
      showRemoveModal: false,
      studentToRemove: { id: null, name: '' }
    }
  },
  computed: {
    selectedCourseName() {
      const course = this.lecturerCourses.find(c => c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    },
    filteredEnrollments() {
      if (!this.enrollmentSearchTerm) return this.enrollments
      
      const term = this.enrollmentSearchTerm.toLowerCase()
      return this.enrollments.filter(enrollment => 
        enrollment.student_name.toLowerCase().includes(term) ||
        enrollment.matric_no.toLowerCase().includes(term)
      )
    },
    paginatedEnrollments() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredEnrollments.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredEnrollments.length / this.itemsPerPage)
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
    async fetchAllStudents() {
      try {
        const res = await api.get('/admin/users')
        this.allStudents = res.data.filter(user => user.role_name === 'student')
      } catch (e) {
        console.error('Failed to load students:', e)
      }
    },
    async fetchEnrollments() {
      if (!this.selectedCourseId) {
        this.enrollments = []
        return
      }
      
      try {
        const res = await api.get(`/courses/${this.selectedCourseId}/enrollments`)
        this.enrollments = res.data
        this.currentPage = 1
      } catch (e) {
        this.error = 'Failed to load enrollments.'
      }
    },
    searchStudents() {
      if (this.studentSearchTerm.length < 2) {
        this.searchResults = []
        return
      }
      
      const term = this.studentSearchTerm.toLowerCase()
      this.searchResults = this.allStudents
        .filter(student => 
          student.full_name.toLowerCase().includes(term) ||
          student.matric_no.toLowerCase().includes(term)
        )
        .slice(0, 5) // Limit to 5 results
    },
    selectStudent(student) {
      this.selectedStudentId = student.id
      this.studentSearchTerm = `${student.full_name} (${student.matric_no})`
      this.searchResults = []
    },
    async enrollStudent() {
      this.enrollmentSuccess = false
      this.error = ''
      
      try {
        await api.post('/enrollments', {
          student_id: this.selectedStudentId,
          course_id: this.enrollmentCourseId
        })
        
        this.enrollmentSuccess = true
        this.selectedStudentId = ''
        this.studentSearchTerm = ''
        this.enrollmentCourseId = ''
        
        // Refresh enrollments if viewing the same course
        if (this.selectedCourseId == this.enrollmentCourseId) {
          this.fetchEnrollments()
        }
        
        // Clear success message after 3 seconds
        setTimeout(() => {
          this.enrollmentSuccess = false
        }, 3000)
        
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to enroll student. Student may already be enrolled.'
      }
    },
    removeEnrollment(id, studentName) {
      this.studentToRemove = { id, name: studentName }
      this.showRemoveModal = true
    },
    async confirmRemoveEnrollment() {
      try {
        await api.delete(`/enrollments/${this.studentToRemove.id}`)
        this.fetchEnrollments()
        this.showRemoveModal = false
        this.studentToRemove = { id: null, name: '' }
      } catch (e) {
        this.error = 'Failed to remove enrollment.'
        this.showRemoveModal = false
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString()
    }
  },
  mounted() {
    this.fetchLecturerCourses()
    this.fetchAllStudents()
  },
  watch: {
    enrollmentSearchTerm() {
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
.enrollment-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 16px;
}
.enrollment-table th, .enrollment-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.enrollment-table th {
  background: #f0f3f6;
}
button {
  margin-right: 8px;
  padding: 6px 14px;
  border: none;
  border-radius: 4px;
  background: #3498db;
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
}
button.danger {
  background: #e74c3c;
}
button:hover {
  background: #2980b9;
}
button.danger:hover {
  background: #c0392b;
}
.add-form {
  max-width: 400px;
}
input {
  margin: 6px 0;
  padding: 8px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}

.course-selection {
  margin-bottom: 24px;
}

.course-select, .course-select-small {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.course-select-small {
  width: 200px;
}

.form-row {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.student-search {
  position: relative;
  flex: 1;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 100;
  max-height: 200px;
  overflow-y: auto;
}

.search-result-item {
  padding: 10px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.search-result-item:hover {
  background: #f5f5f5;
}

.student-name {
  font-weight: 600;
  color: #1D3557;
}

.student-matric {
  font-size: 12px;
  color: #6c757d;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.search-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-input {
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 250px;
}

.student-count {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
}

.table-wrapper {
  overflow-x: auto;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 16px;
  padding: 16px 0;
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

.page-info {
  font-size: 14px;
  color: #6c757d;
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

.success {
  color: #27ae60;
  margin-top: 8px;
  font-weight: 500;
}
</style>
