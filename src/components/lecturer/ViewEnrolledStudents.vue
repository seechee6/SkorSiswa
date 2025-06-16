<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner-large"></div>
      <p>Loading course and student data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error && !loading && enrolledStudents.length === 0" class="error-container">
      <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <h3>Error Loading Data</h3>
      <p>{{ error }}</p>
      <button @click="fetchData" class="retry-btn">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Retry
      </button>
    </div>

    <!-- Main Content -->
    <div v-else>
      <div class="header-section">
        <div class="header-content">
          <button @click="$router.go(-1)" class="back-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
          </button>
          <div class="course-info">
            <h3>{{ courseInfo.code }} - {{ courseInfo.name }}</h3>
            <p class="course-details">Semester {{ courseInfo.semester }} • Academic Year {{ courseInfo.year }}</p>
          </div>
        </div>
        <div class="header-actions">
          <button @click="exportStudentList" class="action-btn secondary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            </svg>
            Export List
          </button>
          <button @click="openEnrollModal" class="action-btn primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Enroll Students
          </button>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="card filters-section">
        <div class="search-container">
          <div class="search-input-wrapper">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
              v-model="searchTerm" 
              placeholder="Search by name or matric number..." 
              class="search-input"
            />
          </div>
          <div class="enrollment-stats">
            <span class="stat-item">
              <strong>{{ filteredStudents.length }}</strong> of {{ enrolledStudents.length }} students
            </span>
          </div>
        </div>
      </div>

      <!-- Students List -->
      <div class="card">
        <!-- Success Message -->
        <div v-if="success" class="success-message">
          <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ success }}
        </div>

        <!-- Error Message -->
        <div v-if="error && !success" class="error-message">
          <svg class="error-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          {{ error }}
        </div>

        <div class="students-header">
          <h4>Enrolled Students</h4>
        </div>

        <!-- List View -->
        <div class="students-table-container">
          <table class="students-table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Student</th>
                <th>Matric Number</th>
                <th>Enrollment Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(student, index) in filteredStudents" :key="student.id" class="student-row">
                <td class="number-cell">
                  {{ index + 1 }}
                </td>
                <td class="student-cell">
                  <div class="student-info-row">
                    <div class="student-details">
                      <div class="student-name">{{ student.full_name }}</div>
                    </div>
                  </div>
                </td>
                <td class="matric-cell">
                  <span class="matric-badge">{{ student.matric_no }}</span>
                </td>
                <td class="date-cell">
                  {{ formatDate(student.enrollment_date) }}
                </td>
                <td class="actions-cell">
                  <div class="action-buttons">
                    <button @click="confirmUnenroll(student)" class="action-btn-small danger" title="Remove from Course">
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

        <!-- Empty State -->
        <div v-if="enrolledStudents.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <h4>No Students Enrolled</h4>
          <p>This course doesn't have any enrolled students yet.</p>
          <button @click="openEnrollModal" class="action-btn primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Enroll Students
          </button>
        </div>

        <!-- No Results -->
        <div v-else-if="filteredStudents.length === 0" class="no-results">
          <svg class="search-icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <h4>No Students Found</h4>
          <p>No students match your search criteria.</p>
        </div>
      </div>

      <!-- Unenroll Confirmation Modal -->
      <div v-if="showUnenrollConfirm" class="modal-overlay" @click.self="closeUnenrollConfirm">
        <div class="modal-content delete-modal">
          <div class="delete-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h4>Remove Student from Course</h4>
          <p>Are you sure you want to remove <strong>{{ studentToUnenroll?.full_name }}</strong> from this course?</p>
          <p class="warning-text">This will remove all their progress and grades for this course.</p>
          <div class="modal-actions">
            <button @click="unenrollStudent" class="delete-confirm-btn">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Remove Student
            </button>
            <button @click="closeUnenrollConfirm" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Enrollment Modal -->
      <ManageEnrollment 
        v-if="showEnrollmentModal"
        :isModalOnly="true"
        :selectedCourse="courseInfo"
        @enrollment-success="handleEnrollmentSuccess"
        @enrollment-error="handleEnrollmentError"
        @enrollment-closed="handleEnrollmentClosed"
      />
    </div>
  </div>
</template>

<script>
import api from '../../api'
import ManageEnrollment from './ManageEnrollment.vue'

export default {
  name: 'ViewEnrolledStudents',
  components: {
    ManageEnrollment
  },
  data() {
    return {
      courseInfo: {},
      enrolledStudents: [],
      searchTerm: '',
      showUnenrollConfirm: false,
      studentToUnenroll: null,
      loading: true,
      error: '',
      success: '', // Add success state
      showEnrollmentModal: false
    }
  },
  
  computed: {
    filteredStudents() {
      if (!this.searchTerm) return this.enrolledStudents
      
      const term = this.searchTerm.toLowerCase()
      return this.enrolledStudents.filter(student => 
        student.full_name.toLowerCase().includes(term) ||
        student.matric_no.toLowerCase().includes(term)
      )
    }
  },
  
  methods: {
    async fetchData() {
      try {
        this.loading = true
        this.error = ''
        const courseId = this.$route.params.courseId
        
        if (!courseId) {
          this.error = 'Course ID not provided'
          this.loading = false
          return
        }
        
        console.log('Loading data for course ID:', courseId)
        
        const coursesRes = await api.get('/courses')
        console.log('Courses response:', coursesRes.data)
        
        const course = coursesRes.data.find(c => c.id == courseId)
        if (!course) {
          this.error = `Course with ID ${courseId} not found`
          this.loading = false
          return
        }
        
        this.courseInfo = course
        console.log('Course loaded:', this.courseInfo)
        
        try {
          console.log('Fetching enrollments for course:', courseId)
          const enrollmentsRes = await api.get(`/courses/${courseId}/enrollments`)
          console.log('Enrollments API response:', enrollmentsRes)
          console.log('Enrollments data:', enrollmentsRes.data)
          
          if (Array.isArray(enrollmentsRes.data)) {
            this.enrolledStudents = enrollmentsRes.data.map(enrollment => ({
              id: enrollment.student_id,
              full_name: enrollment.student_name,
              matric_no: enrollment.matric_no,
              email: enrollment.email,
              enrollment_id: enrollment.enrollment_id || enrollment.id,
              enrollment_date: enrollment.enrollment_date || enrollment.created_at || new Date().toISOString()
            }))
            
            console.log('Enrolled students processed:', this.enrolledStudents)
          } else {
            console.error('Enrollments response is not an array:', enrollmentsRes.data)
            this.enrolledStudents = []
          }
          
        } catch (enrollmentError) {
          console.error('Error fetching enrollments:', enrollmentError)
          console.error('Error response:', enrollmentError.response?.data)
          
          try {
            console.log('Trying fallback method...')
            const allEnrollmentsRes = await api.get('/enrollments')
            console.log('All enrollments response:', allEnrollmentsRes.data)
            
            const courseEnrollments = allEnrollmentsRes.data.filter(e => e.course_id == courseId)
            console.log('Filtered course enrollments:', courseEnrollments)
            
            this.enrolledStudents = courseEnrollments.map(enrollment => ({
              id: enrollment.student_id,
              full_name: enrollment.student_name,
              matric_no: enrollment.matric_no,
              email: enrollment.email,
              enrollment_id: enrollment.enrollment_id || enrollment.id,
              enrollment_date: enrollment.enrollment_date || enrollment.created_at || new Date().toISOString()
            }))
            
            console.log('Enrolled students loaded (fallback):', this.enrolledStudents)
          } catch (fallbackError) {
            console.error('Fallback enrollment fetch failed:', fallbackError)
            console.error('Fallback error response:', fallbackError.response?.data)
            this.enrolledStudents = []
            
            if (enrollmentError.response?.status === 404) {
              this.error = 'Enrollment endpoint not found. Please check if the backend server is running properly.'
            } else {
              this.error = `Failed to load enrolled students: ${enrollmentError.message}`
            }
          }
        }
        
        this.loading = false
        
      } catch (error) {
        console.error('Error loading data:', error)
        console.error('Error details:', error.response?.data)
        
        if (error.response?.status === 404) {
          this.error = 'Course not found or you do not have access to this course'
        } else if (error.response?.status === 500) {
          this.error = 'Server error. Please check the database connection.'
        } else if (error.code === 'ERR_NETWORK') {
          this.error = 'Network error. Please check if the backend server is running on localhost:8000'
        } else {
          this.error = `Failed to load course and student data: ${error.message}`
        }
        
        this.loading = false
      }
    },
    
    openEnrollModal() {
      this.showEnrollmentModal = true
    },

    handleEnrollmentSuccess(eventData) {
      console.log('Received enrollment success event:', eventData)
      
      // Handle both old string format and new object format
      let message, needsRefresh, closeModal
      if (typeof eventData === 'string') {
        message = eventData
        needsRefresh = true
        closeModal = false
      } else if (eventData && typeof eventData === 'object') {
        message = eventData.message || 'Students enrolled successfully!'
        needsRefresh = eventData.needsRefresh !== false
        closeModal = eventData.closeModal === true
      } else {
        message = 'Students enrolled successfully!'
        needsRefresh = true
        closeModal = false
      }
      
      // Show success message immediately
      this.success = message
      this.error = '' // Clear any existing errors
      
      // Force close modal if instructed
      if (closeModal) {
        this.showEnrollmentModal = false
        console.log('Modal force closed due to closeModal flag')
      }
      
      // Auto-clear success message after 5 seconds
      setTimeout(() => {
        this.success = ''
      }, 5000)
      
      // Refresh the enrolled students data immediately if needed
      if (needsRefresh) {
        console.log('Refreshing enrolled students data immediately...')
        // Use nextTick to ensure the modal has properly closed
        this.$nextTick(() => {
          this.fetchData()
        })
      }
    },

    handleEnrollmentError(message) {
      this.error = message
      setTimeout(() => {
        this.error = ''
      }, 5000)
    },

    handleEnrollmentClosed() {
      console.log('Enrollment modal closed')
      this.showEnrollmentModal = false
      
      // Don't automatically refresh data if we already got a success event
      // Only refresh if there was no success event (i.e., user cancelled)
      if (!this.success) {
        console.log('Modal closed without enrollment success, checking for updates...')
        setTimeout(() => {
          this.fetchData()
        }, 300)
      } else {
        console.log('Modal closed after successful enrollment, skipping data refresh')
      }
    },
    
    confirmUnenroll(student) {
      this.studentToUnenroll = student
      this.showUnenrollConfirm = true
    },
    
    async unenrollStudent() {
      try {
        console.log('Attempting to remove student:', this.studentToUnenroll)
        console.log('Enrollment ID:', this.studentToUnenroll.enrollment_id)
        
        // Clear any existing messages first
        this.error = ''
        this.success = ''
        
        // Show loading state
        const deleteBtn = document.querySelector('.delete-confirm-btn')
        if (deleteBtn) {
          deleteBtn.disabled = true
          deleteBtn.innerHTML = '<div class="btn-spinner"></div> Removing...'
        }
        
        const response = await api.delete(`/enrollments/${this.studentToUnenroll.enrollment_id}`)
        console.log('Remove enrollment response:', response.data)
        
        // Check if the response indicates success
        if (response.data && response.data.success) {
          // Remove student from local array
          this.enrolledStudents = this.enrolledStudents.filter(
            s => s.id !== this.studentToUnenroll.id
          )
          
          // Show success message using the backend response
          const studentName = response.data.student_name || this.studentToUnenroll.full_name
          this.success = `${studentName} has been successfully removed from the course.`
          
          // Close modal immediately on success
          this.closeUnenrollConfirm()
          
          // Auto-clear success message after 5 seconds
          setTimeout(() => {
            this.success = ''
          }, 5000)
          
          console.log('Student removed successfully')
        } else {
          throw new Error('Unexpected response format')
        }
        
      } catch (error) {
        console.error('Error removing student:', error)
        console.error('Error response:', error.response?.data)
        
        // Reset button state
        const deleteBtn = document.querySelector('.delete-confirm-btn')
        if (deleteBtn) {
          deleteBtn.disabled = false
          deleteBtn.innerHTML = `
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Remove Student
          `
        }
        
        // Clear success and show error
        this.success = ''
        
        let errorMessage = 'Failed to remove student from course'
        
        if (error.response) {
          if (error.response.status === 404) {
            errorMessage = 'Student has already been removed from this course.'
            // If 404, still remove from UI as the student is no longer enrolled
            this.enrolledStudents = this.enrolledStudents.filter(
              s => s.id !== this.studentToUnenroll.id
            )
            this.closeUnenrollConfirm()
            this.success = `${this.studentToUnenroll.full_name} is no longer enrolled in this course.`
            setTimeout(() => {
              this.success = ''
            }, 5000)
            return
          } else if (error.response.status === 500) {
            errorMessage = 'Server error occurred while removing the student. Please try again.'
          } else if (error.response.data?.error) {
            errorMessage = error.response.data.error
          }
        } else if (error.code === 'ERR_NETWORK') {
          errorMessage = 'Network error. Please check your connection and try again.'
        }
        
        this.error = errorMessage
        
        // Clear error after 8 seconds
        setTimeout(() => {
          this.error = ''
        }, 8000)
      }
    },
    
    closeUnenrollConfirm() {
      this.showUnenrollConfirm = false
      this.studentToUnenroll = null
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },

    exportStudentList() {
      if (this.enrolledStudents.length === 0) {
        alert('No students to export')
        return
      }

      // Create CSV content
      const headers = ['No.', 'Student Name', 'Matric Number', 'Enrollment Date']
      const csvContent = [
        headers.join(','),
        ...this.enrolledStudents.map((student, index) => [
          index + 1,
          `"${student.full_name}"`,
          student.matric_no,
          this.formatDate(student.enrollment_date)
        ].join(','))
      ].join('\n')

      // Create and download file
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `${this.courseInfo.code}_enrolled_students.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }
  },
  
  mounted() {
    this.fetchData()
  }
}
</script>

<style scoped>
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  gap: 20px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  border: 1px solid #e1e5e9;
  color: #6c757d;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.back-btn:hover {
  background: #f8f9fa;
  border-color: #457B9D;
  color: #457B9D;
}

.back-btn svg {
  width: 16px;
  height: 16px;
}

.course-info h3 {
  margin: 0 0 4px 0;
  color: #1D3557;
  font-size: 24px;
}

.course-details {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s ease;
}

.action-btn.primary {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.action-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.4);
}

.action-btn.secondary {
  background: white;
  color: #6c757d;
  border: 1px solid #e1e5e9;
}

.action-btn.secondary:hover {
  background: #f8f9fa;
  border-color: #6c757d;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
}

.filters-section {
  padding: 16px 24px;
}

.search-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.search-input-wrapper {
  position: relative;
  flex: 1;
  max-width: 400px;
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
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.enrollment-stats {
  color: #6c757d;
  font-size: 14px;
}

.students-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.students-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 18px;
}

.students-table-container {
  overflow-x: auto;
}

.students-table {
  width: 100%;
  border-collapse: collapse;
}

.students-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 16px 12px;
  text-align: left;
  border-bottom: 2px solid #e9ecef;
}

.students-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.student-row:hover {
  background: #f8f9fa;
}

.student-info-row {
  display: flex;
  align-items: center;
}

.matric-badge {
  background: #e8f4f8;
  color: #457B9D;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 6px;
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
}

.action-btn-small svg {
  width: 14px;
  height: 14px;
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

.empty-state, .no-results {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.empty-icon, .search-icon-large {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

.empty-state h4, .no-results h4 {
  margin: 0 0 8px 0;
  color: #495057;
  font-size: 18px;
}

.empty-state p, .no-results p {
  margin: 0 0 20px 0;
  color: #6c757d;
}

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
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.delete-modal {
  text-align: center;
}

.delete-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 16px;
  background: #fee;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #dc3545;
}

.delete-icon svg {
  width: 32px;
  height: 32px;
}

.modal-content h4 {
  margin: 0 0 12px 0;
  color: #1D3557;
  font-size: 18px;
}

.modal-content p {
  margin: 0 0 8px 0;
  color: #6c757d;
  line-height: 1.5;
}

.warning-text {
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 20px;
}

.delete-confirm-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.delete-confirm-btn:hover {
  background: linear-gradient(135deg, #c82333, #a71e2a);
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

.loading-container {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.loading-spinner-large {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  border: 8px solid #f3f3f3;
  border-top: 8px solid #457B9D;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.error-container {
  text-align: center;
  padding: 40px 20px;
  color: #dc3545;
}

.error-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #dc3545;
}

.retry-btn {
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

.retry-btn:hover {
  background: linear-gradient(135deg, #1D3557, #457B9D);
  transform: translateY(-1px);
}

.number-cell {
  width: 60px;
  text-align: center;
  font-weight: 600;
  color: #6c757d;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  display: inline-block;
}

.success-message {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #d4edda;
  color: #155724;
  padding: 12px 20px;
  border-radius: 8px;
  border: 1px solid #c3e6cb;
  margin-bottom: 20px;
}

.success-icon {
  width: 24px;
  height: 24px;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8d7da;
  color: #721c24;
  padding: 12px 20px;
  border-radius: 8px;
  border: 1px solid #f5c6cb;
  margin-bottom: 20px;
}

.error-icon-small {
  width: 24px;
  height: 24px;
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .header-actions {
    justify-content: flex-end;
  }
  
  .search-container {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
}
</style>