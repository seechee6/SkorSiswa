<template>
  <div>
    <div class="header-section">
      <h3>Feedback & Remarks</h3>
      <p class="subtitle">Add and manage feedback remarks for your students</p>
    </div>

    <!-- Filters and Controls Card -->
    <div class="card">
      <div class="table-header">
        <h4>Filter Student</h4>
        <span class="student-count">{{ filteredStudents.length }} students</span>
      </div>

      <div class="controls-section">
        <div class="filter-controls">
          <div class="course-filter">
            <label class="filter-label">Filter by Course:</label>
            <div class="select-container">
              <svg class="select-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
              <select v-model="selectedCourseFilter" @change="applyFilters" class="course-dropdown">
                <option value="">All Courses</option>
                <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
                  {{ course.code }} - {{ course.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="feedback-filter-group">
            <div class="feedback-filter">
              <label class="filter-label">Filter by Feedback:</label>
              <select v-model="feedbackFilter" @change="applyFilters" class="feedback-dropdown">
                <option value="">All Students</option>
                <option value="with-feedback">With Feedback</option>
                <option value="no-feedback">No Feedback</option>
              </select>
            </div>
            <button v-if="hasActiveFilters" @click="clearAllFilters" class="clear-filters-btn compact">
              <svg class="btn-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Clear Filter
            </button>
          </div>
        </div>

        <div class="search-controls">
          <div class="search-container">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>
            <input 
              v-model="searchTerm" 
              @input="applyFilters"
              placeholder="Search by student name or matric number..."
              class="search-input"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Students Table -->
    <div v-if="!loading && allStudents.length > 0 && filteredStudents.length > 0" class="card">
      <div class="table-header">
        <h4>All Students</h4>
        <div class="table-actions">
          <span class="results-info">
            Showing {{ paginatedStudents.length }} of {{ filteredStudents.length }} students
          </span>
        </div>
      </div>
      
      <div class="table-container">
        <table class="students-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Matric No</th>
              <th>Student Name</th>
              <th>Course</th>
              <th>Feedback Count</th>
              <th>Last Feedback</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, index) in paginatedStudents" :key="`${student.course_id}-${student.student_id}`" class="student-row">
              <td class="number-cell">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
              <td>
                <span class="matric-badge">{{ student.matric_no }}</span>
              </td>
              <td class="student-name-cell">{{ student.student_name }}</td>
              <td class="course-cell">
                <div class="course-info">
                  <span class="course-code">{{ student.course_code }}</span>
                  <span class="course-name">{{ student.course_name }}</span>
                </div>
              </td>
              <td class="feedback-count-cell">
                <span class="feedback-count" :class="{ 'no-count': student.feedbackCount === 0 }">
                  {{ student.feedbackCount || 0 }}
                </span>
              </td>
              <td class="last-feedback-cell">
                <span v-if="student.lastFeedback" class="last-feedback-date">
                  {{ formatDate(student.lastFeedback) }}
                </span>
                <span v-else class="no-feedback">No feedback yet</span>
              </td>
              <td class="actions-cell">
                <button @click="openFeedbackModal(student)" class="action-btn primary">
                  <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Manage Feedback
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

    <!-- No Filter Results -->
    <div v-else-if="!loading && allStudents.length > 0 && filteredStudents.length === 0" class="card">
      <div class="empty-state">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <h4>No Results Found</h4>
        <p>No students match your current filters. Try adjusting your search criteria.</p>
        <button @click="clearAllFilters" class="clear-filters-btn primary">
          Clear All Filters
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && allStudents.length === 0" class="card">
      <div class="empty-state">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 6 6 0 0112 0v1zm0 0h6v-1a6 6 6 6 0 00-9-5.197m13.5-9a2.5 2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <h4>No Students Found</h4>
        <p>No students are enrolled in your courses.</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="card">
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Loading students...</p>
      </div>
    </div>

    <!-- Feedback Modal -->
    <div v-if="showFeedbackModal" class="modal-overlay" @click.self="closeFeedbackModal">
      <div class="modal-content feedback-modal">
        <div class="modal-header">
          <h4>Feedback for {{ selectedStudent?.student_name }}</h4>
          <button @click="closeFeedbackModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Student Info -->
        <div class="student-info-section">
          <div class="student-avatar">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div class="student-details">
            <h5>{{ selectedStudent?.student_name }}</h5>
            <p>{{ selectedStudent?.matric_no }} • {{ selectedStudent?.course_code }} - {{ selectedStudent?.course_name }}</p>
          </div>
        </div>

        <!-- Add New Feedback -->
        <div class="add-feedback-section">
          <h5>Add New Feedback</h5>
          <form @submit.prevent="submitFeedback" class="feedback-form">
            <textarea 
              v-model="newFeedback"
              placeholder="Enter your feedback or remarks for this student..."
              class="feedback-textarea"
              rows="4"
              required
            ></textarea>
            <div class="form-actions">
              <button type="submit" class="submit-btn" :disabled="!newFeedback.trim()">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Submit Feedback
              </button>
            </div>
          </form>
        </div>

        <!-- Existing Feedback -->
        <div class="existing-feedback-section">
          <h5>Previous Feedback</h5>
          <div v-if="studentFeedbacks.length > 0" class="feedback-list">
            <div v-for="feedback in studentFeedbacks" :key="feedback.id" class="feedback-item">
              <div class="feedback-header">
                <span class="feedback-date">{{ formatDateTime(feedback.created_at) }}</span>
              </div>
              <div class="feedback-content">{{ feedback.remark }}</div>
            </div>
          </div>
          <div v-else class="no-feedback-state">
            <svg class="no-feedback-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p>No previous feedback available for this student.</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="success" class="toast success-toast">
      <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
import api from '../../api'

export default {
  name: 'FeedbackRemarks',
  data() {
    return {
      lecturerCourses: [],
      allStudents: [], // All students from all courses
      filteredStudents: [], // Students after applying filters
      selectedStudent: null,
      showFeedbackModal: false,
      newFeedback: '',
      studentFeedbacks: [],
      
      // Filter and search controls
      selectedCourseFilter: '',
      feedbackFilter: '', // '', 'with-feedback', 'no-feedback'
      searchTerm: '',
      
      // Pagination
      currentPage: 1,
      itemsPerPage: 15,
      
      // UI state
      loading: false,
      success: '',
      error: ''
    }
  },
  computed: {
    paginatedStudents() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredStudents.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.itemsPerPage);
    },
    hasActiveFilters() {
      return this.selectedCourseFilter || this.feedbackFilter || this.searchTerm;
    }
  },
  methods: {
    async fetchLecturerCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'));
        const res = await api.get('/courses');
        this.lecturerCourses = res.data.filter(c => c.lecturer_id === user.id);
      } catch (e) {
        this.showError('Failed to load courses.');
      }
    },

    async fetchAllStudents() {
      this.loading = true;
      try {
        const allStudentsData = [];
        
        // Fetch students from all lecturer's courses
        for (const course of this.lecturerCourses) {
          const enrollmentsRes = await api.get(`/courses/${course.id}/enrollments`);
          const enrollments = enrollmentsRes.data || [];
          
          // Add course information and fetch feedback data for each student
          const studentsWithFeedback = await Promise.all(enrollments.map(async (enrollment) => {
            try {
              const feedbackRes = await api.get(`/enrollments/${enrollment.id}/feedback`);
              const feedbacks = feedbackRes.data || [];
              return {
                ...enrollment,
                course_id: course.id,
                course_code: course.code,
                course_name: course.name,
                course_semester: course.semester,
                course_year: course.year,
                feedbackCount: feedbacks.length,
                lastFeedback: feedbacks.length > 0 ? feedbacks[0].created_at : null
              };
            } catch (e) {
              return {
                ...enrollment,
                course_id: course.id,
                course_code: course.code,
                course_name: course.name,
                course_semester: course.semester,
                course_year: course.year,
                feedbackCount: 0,
                lastFeedback: null
              };
            }
          }));
          
          allStudentsData.push(...studentsWithFeedback);
        }
        
        this.allStudents = allStudentsData;
        this.applyFilters();
      } catch (e) {
        this.showError('Failed to load students.');
        this.allStudents = [];
        this.filteredStudents = [];
      } finally {
        this.loading = false;
      }
    },

    applyFilters() {
      let filtered = [...this.allStudents];
      
      // Apply course filter
      if (this.selectedCourseFilter) {
        filtered = filtered.filter(student => student.course_id == this.selectedCourseFilter);
      }
      
      // Apply feedback filter
      if (this.feedbackFilter === 'with-feedback') {
        filtered = filtered.filter(student => student.feedbackCount > 0);
      } else if (this.feedbackFilter === 'no-feedback') {
        filtered = filtered.filter(student => student.feedbackCount === 0);
      }
      
      // Apply search filter
      if (this.searchTerm) {
        const term = this.searchTerm.toLowerCase();
        filtered = filtered.filter(student => 
          student.student_name.toLowerCase().includes(term) ||
          student.matric_no.toLowerCase().includes(term)
        );
      }
      
      this.filteredStudents = filtered;
      this.currentPage = 1; // Reset to first page when filters change
    },

    clearAllFilters() {
      this.selectedCourseFilter = '';
      this.feedbackFilter = '';
      this.searchTerm = '';
      this.applyFilters();
    },

    async openFeedbackModal(student) {
      this.selectedStudent = student;
      await this.fetchStudentFeedbacks(student.id);
      this.showFeedbackModal = true;
    },

    closeFeedbackModal() {
      this.showFeedbackModal = false;
      this.selectedStudent = null;
      this.studentFeedbacks = [];
      this.newFeedback = '';
    },

    async fetchStudentFeedbacks(enrollmentId) {
      try {
        const res = await api.get(`/enrollments/${enrollmentId}/feedback`);
        this.studentFeedbacks = res.data || [];
      } catch (e) {
        this.studentFeedbacks = [];
      }
    },

    async submitFeedback() {
      if (!this.newFeedback.trim()) return;

      try {
        await api.post(`/enrollments/${this.selectedStudent.id}/feedback`, {
          remark: this.newFeedback
        });
        
        this.newFeedback = '';
        await this.fetchStudentFeedbacks(this.selectedStudent.id);
        await this.fetchAllStudents(); // Refresh all students to update feedback counts
        this.showSuccess('Feedback submitted successfully!');
      } catch (e) {
        this.showError('Failed to submit feedback.');
      }
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },

    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString();
    },

    showSuccess(message) {
      this.success = message;
      setTimeout(() => {
        this.success = '';
      }, 5000);
    },

    showError(message) {
      this.error = message;
      setTimeout(() => {
        this.error = '';
      }, 5000);
    }
  },

  async mounted() {
    await this.fetchLecturerCourses();
    await this.fetchAllStudents();
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

.student-count {
  background: #F1FAEE;
  color: #1D3557;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Course Selection */
.select-container {
  position: relative;
  display: flex;
  align-items: center;
}

.select-icon {
  position: absolute;
  left: 12px;
  width: 20px;
  height: 20px;
  color: #6c757d;
  pointer-events: none;
  z-index: 1;
}

.course-dropdown {
  width: 100%;
  padding: 12px 12px 12px 44px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #495057;
  transition: all 0.2s ease;
  cursor: pointer;
}

.course-dropdown:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.course-dropdown:hover {
  border-color: #457B9D;
}

/* Search Input */
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #6c757d;
  pointer-events: none;
  z-index: 2;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #495057;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.search-input::placeholder {
  color: #adb5bd;
}

/* Students Table */
.table-container {
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
  transition: background-color 0.2s ease;
}

.number-cell {
  width: 60px;
  text-align: center;
  font-weight: 600;
  color: #6c757d;
}

.matric-badge {
  background: #e8f4f8;
  color: #457B9D;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.student-name-cell {
  font-weight: 600;
  color: #1D3557;
}

.feedback-count-cell {
  text-align: center;
}

.feedback-count {
  background: #E3F2FD;
  color: #1976D2;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.last-feedback-cell {
  color: #6c757d;
  font-size: 13px;
}

.last-feedback-date {
  color: #495057;
}

.no-feedback {
  color: #adb5bd;
  font-style: italic;
}

.actions-cell {
  white-space: nowrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s ease;
  border: none;
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

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
  padding: 16px;
  border-top: 1px solid #f1f3f4;
}

.pagination-btn {
  padding: 8px 16px;
  border: 1px solid #457B9D;
  background: white;
  color: #457B9D;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn:not(:disabled):hover {
  background: #457B9D;
  color: white;
  transform: translateY(-1px);
}

.page-info {
  color: #6c757d;
  font-size: 14px;
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
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 24px;
  max-width: 95vw;
  max-height: 95vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.feedback-modal {
  width: 600px;
  max-width: 95vw;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #495057;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

/* Student Info Section */
.student-info-section {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #E3F2FD 0%, #F1FAEE 100%);
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.student-avatar {
  width: 48px;
  height: 48px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.student-avatar svg {
  width: 24px;
  height: 24px;
  color: #457B9D;
}

.student-details h5 {
  margin: 0 0 4px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.student-details p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

/* Add Feedback Section */
.add-feedback-section {
  margin-bottom: 24px;
}

.add-feedback-section h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.feedback-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feedback-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s ease;
}

.feedback-textarea:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.feedback-textarea::placeholder {
  color: #adb5bd;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
}

.submit-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #27ae60, #219a52);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

/* Existing Feedback Section */
.existing-feedback-section h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.feedback-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feedback-item {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.2s ease;
}

.feedback-item:hover {
  border-color: #457B9D;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.1);
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.feedback-date {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

.feedback-content {
  color: #495057;
  line-height: 1.5;
  font-size: 14px;
}

/* No Feedback State */
.no-feedback-state {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.no-feedback-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

.no-feedback-state p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
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
  animation: toastSlideIn 0.3s ease;
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

/* Controls Section */
.controls-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.filter-controls {
  display: flex;
  gap: 20px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.course-filter, .feedback-filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 200px;
}

.feedback-filter-group {
  display: flex;
  flex-direction: row;
  align-items: flex-end;
  gap: 8px;
  min-width: 200px;
}

.feedback-filter {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.filter-label {
  font-size: 14px;
  font-weight: 600;
  color: #1D3557;
}

.feedback-dropdown {
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #495057;
  transition: all 0.2s ease;
  cursor: pointer;
}

.feedback-dropdown:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.feedback-dropdown:hover {
  border-color: #457B9D;
}

.search-controls {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
  min-width: 300px;
}

.search-container {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.clear-filters-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8f9fa;
  color: #6c757d;
  border: 1px solid #e1e5e9;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.clear-filters-btn.compact {
  padding: 8px 12px;
  min-width: auto;
  height: 40px;
  justify-content: center;
  flex-shrink: 0;
  gap: 6px;
}

.clear-filters-btn:hover {
  background: #e9ecef;
  border-color: #adb5bd;
  color: #495057;
  transform: translateY(-1px);
}

.clear-filters-btn.primary {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  border: none;
}

.clear-filters-btn.primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.4);
}

/* Table Actions */
.table-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.results-info {
  color: #6c757d;
  font-size: 14px;
}

/* Course Cell */
.course-cell {
  max-width: 200px;
}

.course-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.course-code {
  font-weight: 600;
  color: #1D3557;
  font-size: 13px;
}

.course-name {
  color: #6c757d;
  font-size: 12px;
  line-height: 1.3;
}

/* Enhanced Feedback Count */
.feedback-count.no-count {
  background: #f8f9fa;
  color: #adb5bd;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #457B9D;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-state p {
  margin: 0;
  font-size: 14px;
}

/* Responsive Design for Controls */
@media (max-width: 1024px) {
  .controls-section {
    gap: 16px;
  }
  
  .filter-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .course-filter, .feedback-filter-group {
    min-width: auto;
  }
  
  .search-controls {
    min-width: auto;
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .search-container {
    max-width: none;
  }
}

@media (max-width: 768px) {
  .filter-controls {
    gap: 12px;
  }
  
  .course-filter, .feedback-filter-group {
    min-width: auto;
  }
  
  .search-controls {
    flex-direction: column;
  }
  
  .clear-filters-btn {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .controls-section {
    gap: 12px;
  }
  
  .table-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .table-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .course-cell {
    max-width: none;
  }
}
</style>
