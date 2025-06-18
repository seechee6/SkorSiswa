<template>
  <!-- Only show the main enrollment page when not used as modal -->
  <div v-if="!isModalOnly" class="manage-enrollment">
    <!-- Header Section -->
    <div class="header-section">
      <h3>Manage Student Enrollment</h3>
    </div>

    <!-- Course Management Card -->
    <div class="card course-management-card">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          Course Management
        </div>
      </div>

      <!-- Filters Section -->
      <div class="filters-section">
        <div class="filter-group">
          <div class="filter-item">
            <label>Semester</label>
            <select v-model="semesterFilter" class="filter-select">
              <option value="">All Semesters</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
              <option value="3">Semester 3</option>
            </select>
          </div>
          
          <div class="filter-item">
            <label>Year</label>
            <select v-model="yearFilter" class="filter-select">
              <option value="">All Years</option>
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
          
          <div class="filter-item search-filter">
            <label>Search Course</label>
            <div class="search-container">
              <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                v-model="courseSearchTerm" 
                placeholder="Search by code or name..."
                class="search-input"
              />
            </div>
          </div>
          
          <div class="filter-actions">
            <button @click="clearFilters" class="action-btn-small secondary">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Courses Table -->
      <div class="table-container">
        <table class="courses-table" v-if="filteredCourses.length > 0">
          <thead>
            <tr>
              <th class="sortable" @click="sortCoursesBy('code')">
                Course Code
                <svg v-if="courseSortField === 'code'" class="sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </th>
              <th class="sortable" @click="sortCoursesBy('name')">
                Course Name
                <svg v-if="courseSortField === 'name'" class="sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </th>
              <th>Semester</th>
              <th>Year</th>
              <th>Enrolled Students</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="course in paginatedCourses" :key="course.id" class="course-row">
              <td>
                <span class="course-code-badge">{{ course.code }}</span>
              </td>
              <td>
                <div class="course-name">{{ course.name }}</div>
              </td>
              <td>
                <span class="semester-badge">Semester {{ course.semester }}</span>
              </td>
              <td>
                <span class="year-text">{{ course.year }}</span>
              </td>
              <td>
                <div class="enrollment-count">
                  <svg class="count-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  {{ getCourseEnrollmentCount(course.id) }} students
                </div>
              </td>
              <td>
                <span class="status-badge active">Active</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button 
                    @click="openEnrollModal(course)" 
                    class="action-btn-small primary"
                    title="Enroll Student"
                  >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="openBulkEnrollModal(course)" 
                    class="action-btn-small secondary"
                    title="Bulk Enroll"
                  >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="viewCourseEnrollments(course)" 
                    class="action-btn-small info"
                    title="View Enrolled Students"
                  >
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

        <!-- Empty State for Courses -->
        <div v-else class="empty-state-table">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <h4>No Courses Found</h4>
          <p>{{ hasActiveFilters ? 'No courses match your current filters.' : 'You have no courses assigned yet.' }}</p>
        </div>
      </div>

      <!-- Courses Pagination -->
      <div class="pagination-container" v-if="coursesTotalPages > 1">
        <div class="pagination-info">
          Showing {{ coursesStartIndex + 1 }}-{{ coursesEndIndex }} of {{ filteredCourses.length }} courses
        </div>
        <div class="pagination">
          <button 
            @click="coursesCurrentPage = 1" 
            :disabled="coursesCurrentPage === 1"
            class="pagination-btn"
          >
            First
          </button>
          <button 
            @click="coursesCurrentPage--" 
            :disabled="coursesCurrentPage === 1"
            class="pagination-btn"
          >
            Previous
          </button>
          <span class="page-numbers">
            <button 
              v-for="page in coursesVisiblePages"
              :key="page"
              @click="coursesCurrentPage = page"
              class="page-btn"
              :class="{ active: coursesCurrentPage === page }"
            >
              {{ page }}
            </button>
          </span>
          <button 
            @click="coursesCurrentPage++" 
            :disabled="coursesCurrentPage === coursesTotalPages"
            class="pagination-btn"
          >
            Next
          </button>
          <button 
            @click="coursesCurrentPage = coursesTotalPages" 
            :disabled="coursesCurrentPage === coursesTotalPages"
            class="pagination-btn"
          >
            Last
          </button>
        </div>
      </div>
    </div>

    <!-- Enrolled Students Management (shown when viewing a specific course) -->
    <div class="card enrollments-card" v-if="selectedCourseId && showEnrollmentView">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          Enrolled Students - {{ selectedCourseName }}
        </div>
        <div class="card-actions">
          <button @click="showEnrollmentView = false" class="action-btn-small secondary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Courses
          </button>
          
          <div class="search-container">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
              v-model="enrollmentSearchTerm" 
              placeholder="Search students..."
              class="search-input"
            />
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="enrollment-table" v-if="filteredEnrollments.length > 0">
          <thead>
            <tr>
              <th class="checkbox-column">
                <input 
                  type="checkbox" 
                  v-model="selectAll"
                  @change="toggleSelectAll"
                  class="checkbox-input"
                />
              </th>
              <th class="sortable" @click="sortBy('matric_no')">
                Matric No
                <svg v-if="sortField === 'matric_no'" class="sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </th>
              <th class="sortable" @click="sortBy('student_name')">
                Student Name
                <svg v-if="sortField === 'student_name'" class="sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </th>
              <th>Email</th>
              <th class="sortable" @click="sortBy('created_at')">
                Enrolled Date
                <svg v-if="sortField === 'created_at'" class="sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="enrollment in paginatedEnrollments" :key="enrollment.id" class="enrollment-row">
              <td class="checkbox-column">
                <input 
                  type="checkbox" 
                  v-model="selectedStudents"
                  :value="enrollment.id"
                  class="checkbox-input"
                />
              </td>
              <td>
                <span class="matric-badge">{{ enrollment.matric_no }}</span>
              </td>
              <td>
                <div class="student-name">{{ enrollment.student_name }}</div>
              </td>
              <td>
                <span class="email">{{ enrollment.email || 'N/A' }}</span>
              </td>
              <td>
                <span class="date">{{ formatDate(enrollment.created_at) }}</span>
              </td>
              <td>
                <span class="status-badge active">Active</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button 
                    @click="viewStudentDetails(enrollment)" 
                    class="action-btn-small info"
                    title="View Details"
                  >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="removeEnrollment(enrollment.id, enrollment.student_name)" 
                    class="action-btn-small danger"
                    title="Remove from course"
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

        <!-- Empty State for Enrollments -->
        <div v-else class="empty-state-table">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <h4>No Students Enrolled</h4>
          <p>{{ enrollmentSearchTerm ? 'No students match your search criteria.' : 'This course has no enrolled students yet.' }}</p>
        </div>
      </div>

      <!-- Enrollments Pagination -->
      <div class="pagination-container" v-if="totalPages > 1">
        <div class="pagination-info">
          Showing {{ startIndex + 1 }}-{{ endIndex }} of {{ filteredEnrollments.length }} students
        </div>
        <div class="pagination">
          <button 
            @click="currentPage = 1" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            First
          </button>
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            Previous
          </button>
          <span class="page-numbers">
            <button 
              v-for="page in visiblePages"
              :key="page"
              @click="currentPage = page"
              class="page-btn"
              :class="{ active: currentPage === page }"
            >
              {{ page }}
            </button>
          </span>
          <button 
            @click="currentPage++" 
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            Next
          </button>
          <button 
            @click="currentPage = totalPages" 
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            Last
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Enrollment Modal Component (always available) -->
  <div v-if="showEnrollmentModal" class="modal-overlay" @click.self="closeEnrollmentModal">
    <div class="modal-content enrollment-modal">
      <div class="modal-header">
        <h4>Enroll Students - {{ selectedCourseName }}</h4>
        <button @click="closeEnrollmentModal" class="close-btn">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Student Search Section -->
        <div class="search-section">
          <label class="search-label">Search and Select Students</label>
          <div class="student-search-container">
            <div class="search-input-wrapper">
              <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                v-model="modalStudentSearchTerm" 
                @input="onModalStudentSearch"
                placeholder="Search by student name or matric number..." 
                class="search-input-field"
                autocomplete="off"
              />
            </div>
            
            <!-- Student List (replaces dropdown) -->
            <div class="student-list-container">
              <div v-if="isModalSearching" class="loading-state">
                <div class="loading-spinner"></div>
                <span>Searching students...</span>
              </div>
              <div v-else-if="modalFilteredStudents.length > 0" class="students-grid">
                <div 
                  v-for="student in modalFilteredStudents" 
                  :key="student.id"
                  @click="selectModalStudent(student)"
                  class="student-card"
                  :class="{ selected: modalBulkSelectedStudents.includes(student.id) }"
                >
                  <div class="student-details">
                    <div class="student-name">{{ student.full_name }}</div>
                    <div class="student-matric">{{ student.matric_no }}</div>
                  </div>
                  <div class="selection-checkbox">
                    <input 
                      type="checkbox" 
                      :checked="modalBulkSelectedStudents.includes(student.id)"
                      @click.stop="selectModalStudent(student)"
                      class="checkbox-input"
                    />
                  </div>
                </div>
              </div>
              <div v-else class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span v-if="allStudents.length === 0">All students are already enrolled in this course</span>
                <span v-else>{{ modalStudentSearchTerm ? `No students found matching "${modalStudentSearchTerm}"` : 'Start typing to search for students' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Actions -->
      <div class="modal-footer">
        <button @click="closeEnrollmentModal" class="cancel-btn">
          Cancel
        </button>
        <button 
          @click="enrollModalStudents" 
          :disabled="modalBulkSelectedStudents.length === 0 || isModalEnrolling"
          class="enroll-btn"
        >
          <div v-if="isModalEnrolling" class="btn-spinner"></div>
          <svg v-else class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
          </svg>
          {{ isModalEnrolling ? 'Enrolling...' : modalBulkSelectedStudents.length > 0 ? `Enroll ${modalBulkSelectedStudents.length} Student${modalBulkSelectedStudents.length > 1 ? 's' : ''}` : 'Enroll Students' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  props: {
    isModalOnly: {
      type: Boolean,
      default: false
    },
    selectedCourse: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      lecturerCourses: [],
      enrollments: [],
      allStudents: [],
      selectedCourseId: '',
      enrollmentCourseId: '',
      selectedStudentId: '',
      selectedStudentName: '',
      selectedStudentMatric: '',
      studentSearchTerm: '',
      enrollmentSearchTerm: '',
      showStudentDropdown: false,
      successMessage: '',
      errorMessage: '',
      currentPage: 1,
      itemsPerPage: 10,
      showRemoveModal: false,
      showEnrollModal: false,
      showBulkEnrollModal: false,
      studentToRemove: { id: null, name: '' },
      selectedStudents: [],
      selectAll: false,
      viewMode: 'table',
      sortField: '',
      sortDirection: 'asc',
      isEnrolling: false,
      loading: false,
      
      // Course table filters and pagination
      semesterFilter: '',
      yearFilter: '',
      courseSearchTerm: '',
      coursesCurrentPage: 1,
      coursesItemsPerPage: 8,
      courseSortField: '',
      courseSortDirection: 'asc',
      showEnrollmentView: false,
      
      // Modal enrollment data
      showEnrollmentModal: false,
      modalSelectedCourseId: null,
      modalStudentSearchTerm: '',
      modalBulkSelectedStudents: [],
      isModalSearching: false,
      isModalEnrolling: false,
      showModalStudentDropdown: false,
      modalSearchTimeout: null,
      dataLoaded: false
    }
  },
  
  computed: {
    // Modal computed properties
    modalFilteredStudents() {
      if (!this.allStudents || this.allStudents.length === 0) {
        console.log('No students available for filtering')
        return []
      }
      
      let students = [...this.allStudents]
      console.log('Available unenrolled students for enrollment:', students.length)
      
      // Filter by search term if provided
      if (this.modalStudentSearchTerm && this.modalStudentSearchTerm.length >= 1) {
        const term = this.modalStudentSearchTerm.toLowerCase()
        students = students.filter(student => 
          (student.full_name && student.full_name.toLowerCase().includes(term)) ||
          (student.name && student.name.toLowerCase().includes(term)) ||
          (student.matric_no && student.matric_no.toLowerCase().includes(term))
        )
        console.log('Students after search filter:', students.length)
      }
      
      // Show all available students (no limit for scrollable list)
      console.log('Final filtered unenrolled students:', students.length)
      return students
    },

    availableYears() {
      const currentYear = new Date().getFullYear()
      return Array.from({length: 5}, (_, i) => currentYear + i - 2)
    },

    filteredCourses() {
      let courses = [...this.lecturerCourses]
      
      if (this.semesterFilter) {
        courses = courses.filter(c => c.semester == this.semesterFilter)
      }
      
      if (this.yearFilter) {
        courses = courses.filter(c => c.year == this.yearFilter)
      }
      
      if (this.courseSearchTerm) {
        const term = this.courseSearchTerm.toLowerCase()
        courses = courses.filter(c => 
          c.code.toLowerCase().includes(term) ||
          c.name.toLowerCase().includes(term)
        )
      }
      
      // Apply sorting
      if (this.courseSortField) {
        courses.sort((a, b) => {
          let valueA = a[this.courseSortField]
          let valueB = b[this.courseSortField]
          
          if (typeof valueA === 'string') {
            valueA = valueA.toLowerCase()
            valueB = b[this.courseSortField].toLowerCase()
          }
          
          const modifier = this.courseSortDirection === 'asc' ? 1 : -1
          return valueA > valueB ? modifier : -modifier
        })
      }
      
      return courses
    },

    paginatedCourses() {
      const start = (this.coursesCurrentPage - 1) * this.coursesItemsPerPage
      return this.filteredCourses.slice(start, start + this.coursesItemsPerPage)
    },

    coursesTotalPages() {
      return Math.ceil(this.filteredCourses.length / this.coursesItemsPerPage)
    },

    coursesStartIndex() {
      return (this.coursesCurrentPage - 1) * this.coursesItemsPerPage
    },

    coursesEndIndex() {
      return Math.min(this.coursesStartIndex + this.coursesItemsPerPage, this.filteredCourses.length)
    },

    coursesVisiblePages() {
      const totalPages = this.coursesTotalPages
      const current = this.coursesCurrentPage
      const visible = []
      
      const start = Math.max(1, current - 2)
      const end = Math.min(totalPages, current + 2)
      
      for (let i = start; i <= end; i++) {
        visible.push(i)
      }
      
      return visible
    },

    selectedCourseName() {
      if (this.selectedCourse) {
        return `${this.selectedCourse.code} - ${this.selectedCourse.name}`
      }
      const course = this.lecturerCourses.find(c => c.id == this.modalSelectedCourseId || c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    },

    filteredEnrollments() {
      if (!this.enrollments) return []
      
      let filtered = [...this.enrollments]
      
      if (this.enrollmentSearchTerm) {
        const term = this.enrollmentSearchTerm.toLowerCase()
        filtered = filtered.filter(e => 
          e.student_name.toLowerCase().includes(term) ||
          e.matric_no.toLowerCase().includes(term) ||
          (e.email && e.email.toLowerCase().includes(term))
        )
      }
      
      // Apply sorting
      if (this.sortField) {
        filtered.sort((a, b) => {
          let valueA = a[this.sortField]
          let valueB = b[this.sortField]
          
          if (typeof valueA === 'string') {
            valueA = valueA.toLowerCase()
            valueB = b[this.sortField].toLowerCase()
          }
          
          const modifier = this.sortDirection === 'asc' ? 1 : -1
          return valueA > valueB ? modifier : -modifier
        })
      }
      
      return filtered
    },

    paginatedEnrollments() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      return this.filteredEnrollments.slice(start, start + this.itemsPerPage)
    },

    totalPages() {
      return Math.ceil(this.filteredEnrollments.length / this.itemsPerPage)
    },

    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage
    },

    endIndex() {
      return Math.min(this.startIndex + this.itemsPerPage, this.filteredEnrollments.length)
    },

    visiblePages() {
      const totalPages = this.totalPages
      const current = this.currentPage
      const visible = []
      
      const start = Math.max(1, current - 2)
      const end = Math.min(totalPages, current + 2)
      
      for (let i = start; i <= end; i++) {
        visible.push(i)
      }
      
      return visible
    },

    hasActiveFilters() {
      return this.semesterFilter || this.yearFilter || this.courseSearchTerm
    }
  },
  
  methods: {
    async fetchData() {
      try {
        this.loading = true
        console.log('Fetching all data...')
        const user = JSON.parse(localStorage.getItem('user'))
        
        if (!user || !user.id) {
          this.errorMessage = 'User session expired. Please login again.'
          return
        }
        
        // Always load courses and enrollments for the main page
        const [coursesRes, enrollmentsRes] = await Promise.all([
          api.get('/courses'),
          api.get('/enrollments')
        ])
        
        // Filter courses for current lecturer
        this.lecturerCourses = coursesRes.data.filter(c => c.lecturer_id == user.id)
        this.enrollments = enrollmentsRes.data || []
        
        // Only load ALL students if we're NOT in modal mode or no specific course is selected
        if (!this.isModalOnly && !this.modalSelectedCourseId) {
          try {
            const studentsRes = await api.get('/students')
            this.allStudents = studentsRes.data || []
          } catch (e) {
            console.warn('Failed to fetch all students:', e)
            this.allStudents = []
          }
        }
        
        this.dataLoaded = true
        
        console.log('Data fetched - Courses:', this.lecturerCourses.length, 'Students:', this.allStudents.length, 'Enrollments:', this.enrollments.length)
        
        this.clearMessages()
      } catch (error) {
        console.error('Failed to fetch data:', error)
        this.errorMessage = error.response?.data?.error || 'Failed to load data. Please try again.'
      } finally {
        this.loading = false
      }
    },

    async fetchEnrollments() {
      if (!this.selectedCourseId) return
      
      try {
        this.loading = true
        const response = await api.get(`/courses/${this.selectedCourseId}/enrollments`)
        this.enrollments = response.data || []
        this.clearMessages()
      } catch (error) {
        console.error('Failed to fetch enrollments:', error)
        this.errorMessage = error.response?.data?.error || 'Failed to load enrollment data. Please try again.'
      } finally {
        this.loading = false
      }
    },

    async openEnrollmentModal(course) {
      console.log('Opening enrollment modal for course:', course)
      this.modalSelectedCourseId = course.id
      this.showEnrollmentModal = true
      this.resetModalData()
      
      // Load unenrolled students for this specific course AFTER setting the course ID
      await this.loadUnenrolledStudentsForCourse(course.id)
      
      console.log('Modal opened with unenrolled students:', this.allStudents.length)
    },

    closeEnrollmentModal() {
      this.showEnrollmentModal = false
      this.resetModalData()
      this.$emit('enrollment-closed')
    },

    resetModalData() {
      this.modalStudentSearchTerm = ''
      this.showModalStudentDropdown = false
      this.modalBulkSelectedStudents = []
      this.isModalSearching = false
      if (this.modalSearchTimeout) {
        clearTimeout(this.modalSearchTimeout)
        this.modalSearchTimeout = null
      }
    },

    onModalStudentSearch() {
      if (this.modalSearchTimeout) {
        clearTimeout(this.modalSearchTimeout)
      }
      
      this.showModalStudentDropdown = true
      
      if (this.modalStudentSearchTerm.length >= 2) {
        this.isModalSearching = true
        this.modalSearchTimeout = setTimeout(() => {
          this.isModalSearching = false
        }, 300)
      } else if (this.modalStudentSearchTerm.length === 0) {
        // Show all available students when search is empty
        this.isModalSearching = false
      }
    },

    selectModalStudent(student) {
      const index = this.modalBulkSelectedStudents.indexOf(student.id)
      if (index === -1) {
        this.modalBulkSelectedStudents.push(student.id)
      } else {
        this.modalBulkSelectedStudents.splice(index, 1)
      }
      console.log('Selected students:', this.modalBulkSelectedStudents)
    },

    onModalStudentInputBlur() {
      setTimeout(() => {
        this.showModalStudentDropdown = false
      }, 200)
    },

    clearSelectedModalStudents() {
      this.modalBulkSelectedStudents = []
    },

    removeSelectedStudent(studentId) {
      const index = this.modalBulkSelectedStudents.indexOf(studentId)
      if (index !== -1) {
        this.modalBulkSelectedStudents.splice(index, 1)
      }
    },

    async enrollModalStudents() {
      if (this.modalBulkSelectedStudents.length === 0) return
      
      try {
        this.isModalEnrolling = true
        const user = JSON.parse(localStorage.getItem('user'))
        
        if (!user || !user.id) {
          throw new Error('User session expired')
        }
        
        let response
        if (this.modalBulkSelectedStudents.length === 1) {
          // Single enrollment
          response = await api.post('/enrollments', {
            course_id: parseInt(this.modalSelectedCourseId),
            student_id: parseInt(this.modalBulkSelectedStudents[0]),
            lecturer_id: user.id
          })
        } else {
          // Bulk enrollment
          response = await api.post('/enrollments/bulk', {
            course_id: parseInt(this.modalSelectedCourseId),
            student_ids: this.modalBulkSelectedStudents.map(id => parseInt(id)),
            lecturer_id: user.id
          })
        }
        
        const enrolledCount = response.data.enrolled_count || this.modalBulkSelectedStudents.length
        const alreadyEnrolled = response.data.already_enrolled || []
        
        let message = `Successfully enrolled ${enrolledCount} student(s)!`
        if (alreadyEnrolled.length > 0) {
          message += ` (${alreadyEnrolled.length} students were already enrolled)`
        }
        
        this.successMessage = message
        
        // Close modal immediately
        this.showEnrollmentModal = false
        this.resetModalData()
        
        // Emit success event to parent with immediate close instruction
        this.$emit('enrollment-success', {
          message: message,
          enrolledCount: enrolledCount,
          courseId: this.modalSelectedCourseId,
          needsRefresh: true,
          closeModal: true
        })
        
        // Also emit modal closed event
        this.$emit('enrollment-closed')
        
        console.log('Enrollment successful, modal closed, events emitted')
        
        // Refresh local data for the modal
        await this.fetchData()
        
        // If viewing enrollments, refresh them too
        if (this.showEnrollmentView && this.selectedCourseId) {
          await this.fetchEnrollments()
        }
        
      } catch (error) {
        console.error('Failed to enroll students:', error)
        const errorMsg = error.response?.data?.error || 'Failed to enroll students. Please try again.'
        this.errorMessage = errorMsg
        this.$emit('enrollment-error', errorMsg)
      } finally {
        this.isModalEnrolling = false
      }
    },

    async enrollSingleStudent(student) {
      try {
        this.isModalEnrolling = true
        const user = JSON.parse(localStorage.getItem('user'))
        
        if (!user || !user.id) {
          throw new Error('User session expired')
        }
        
        await api.post('/enrollments', {
          course_id: parseInt(this.modalSelectedCourseId),
          student_id: parseInt(student.id),
          lecturer_id: user.id
        })
        
        this.successMessage = `Successfully enrolled ${student.full_name}!`
        await this.fetchData()
        
        // If viewing enrollments, refresh them too
        if (this.showEnrollmentView && this.selectedCourseId) {
          await this.fetchEnrollments()
        }
        
        this.$emit('enrollment-success', this.successMessage)
      } catch (error) {
        console.error('Failed to enroll student:', error)
        const errorMsg = error.response?.data?.error || 'Failed to enroll student. Please try again.'
        this.errorMessage = errorMsg
        this.$emit('enrollment-error', this.errorMessage)
      } finally {
        this.isModalEnrolling = false
      }
    },

    getStudentById(studentId) {
      return this.allStudents.find(student => student.id == studentId)
    },

    getCourseEnrollmentCount(courseId) {
      return this.enrollments.filter(e => e.course_id == courseId).length
    },

    clearFilters() {
      this.semesterFilter = ''
      this.yearFilter = ''
      this.courseSearchTerm = ''
      this.coursesCurrentPage = 1
    },

    sortCoursesBy(field) {
      if (this.courseSortField === field) {
        this.courseSortDirection = this.courseSortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.courseSortField = field
        this.courseSortDirection = 'asc'
      }
    },

    openEnrollModal(course) {
      this.openEnrollmentModal(course)
    },

    openBulkEnrollModal(course) {
      this.openEnrollmentModal(course)
    },

    viewCourseEnrollments(course) {
      this.selectedCourseId = course.id
      this.showEnrollmentView = true
      this.fetchEnrollments()
    },

    sortBy(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortField = field
        this.sortDirection = 'asc'
      }
    },

    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedStudents = this.paginatedEnrollments.map(e => e.enrollment_id || e.id)
      } else {
        this.selectedStudents = []
      }
    },

    viewStudentDetails(enrollment) {
      // Navigate to student details view or show modal
      console.log('View student details:', enrollment)
      // You could implement: this.$router.push(`/lecturer/students/${enrollment.student_id}`)
    },

    async removeEnrollment(enrollmentId, studentName) {
      if (!confirm(`Are you sure you want to remove ${studentName} from this course?`)) {
        return
      }
      
      try {
        await api.delete(`/enrollments/${enrollmentId}`)
        this.successMessage = `${studentName} has been removed from the course successfully!`
        
        // Refresh both enrollments and main data
        await Promise.all([
          this.fetchEnrollments(),
          this.fetchData()
        ])
      } catch (error) {
        console.error('Failed to remove enrollment:', error)
        this.errorMessage = error.response?.data?.error || 'Failed to remove student from course. Please try again.'
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      
      try {
        // Handle different date formats
        let date
        if (dateString.includes('T')) {
          date = new Date(dateString)
        } else {
          // Assume it's in YYYY-MM-DD format
          date = new Date(dateString + 'T00:00:00')
        }
        
        if (isNaN(date.getTime())) {
          return 'N/A'
        }
        
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      } catch (error) {
        console.error('Date formatting error:', error)
        return 'N/A'
      }
    },

    clearMessages() {
      this.successMessage = ''
      this.errorMessage = ''
    },

    // New method to load unenrolled students for a specific course
    async loadUnenrolledStudentsForCourse(courseId) {
      try {
        console.log('Loading unenrolled students for course:', courseId)
        const response = await api.get(`/courses/${courseId}/unenrolled-students`)
        this.allStudents = response.data || []
        this.dataLoaded = true
        console.log('Loaded unenrolled students:', this.allStudents.length)
      } catch (error) {
        console.error('Failed to load unenrolled students:', error)
        this.errorMessage = 'Failed to load available students for enrollment.'
        this.allStudents = [] // Set to empty array if API fails
        this.$emit('enrollment-error', this.errorMessage)
      }
    },

    // Improved method for loading students for modal
    async loadStudentsForModal() {
      try {
        console.log('Loading students for modal...')
        // If we have a selected course, load unenrolled students for that course
        if (this.modalSelectedCourseId) {
          await this.loadUnenrolledStudentsForCourse(this.modalSelectedCourseId)
        } else {
          // Otherwise load all students and enrollments for manual filtering
          const [enrollmentsRes, studentsRes] = await Promise.all([
            api.get('/enrollments'),
            api.get('/students')
          ])
          this.enrollments = enrollmentsRes.data || []
          this.allStudents = studentsRes.data || []
          this.dataLoaded = true
          console.log('Loaded for modal - Students:', this.allStudents.length, 'Enrollments:', this.enrollments.length)
        }
      } catch (error) {
        console.error('Failed to load students for modal:', error)
        this.errorMessage = 'Failed to load student data for enrollment.'
        this.$emit('enrollment-error', this.errorMessage)
      }
    }
  },

  watch: {
    selectedCourse: {
      handler(newCourse) {
        if (newCourse && this.isModalOnly) {
          console.log('Selected course changed:', newCourse)
          this.modalSelectedCourseId = newCourse.id
          this.openEnrollmentModal(newCourse)
        }
      },
      immediate: true
    },

    // Watch for when the modal opens to ensure data is loaded
    showEnrollmentModal: {
      handler(isOpen) {
        if (isOpen) {
          console.log('Modal opened, checking data...', {
            studentsLoaded: this.allStudents.length,
            dataLoaded: this.dataLoaded
          })
          
          if (!this.dataLoaded || this.allStudents.length === 0) {
            console.log('Data not loaded, loading now...')
            this.loadStudentsForModal()
          }
        }
      }
    },

    // Clear messages after some time
    successMessage(newVal) {
      if (newVal) {
        setTimeout(() => {
          this.successMessage = ''
        }, 5000)
      }
    },

    errorMessage(newVal) {
      if (newVal) {
        setTimeout(() => {
          this.errorMessage = ''
        }, 8000)
      }
    }
  },

  async mounted() {
    console.log('ManageEnrollment mounted', {
      isModalOnly: this.isModalOnly,
      selectedCourse: this.selectedCourse
    })
    
    if (!this.isModalOnly) {
      await this.fetchData()
    } else if (this.selectedCourse) {
      // Load necessary data for modal operation
      await this.fetchData()
    } else {
      // Always load students data for modal use
      await this.loadStudentsForModal()
    }
  }
}
</script>

<style scoped>
/* Main container styles */
.manage-enrollment {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Header styles */
.header-section {
  margin-bottom: 32px;
}

.header-section h3 {
  margin: 0;
  color: #1D3557;
  font-size: 28px;
  font-weight: 700;
}

/* Card styles */
.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
}

.card-icon {
  width: 24px;
  height: 24px;
}

/* Filter styles */
.filters-section {
  margin-bottom: 24px;
}

.filter-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  align-items: end;
}

.filter-item {
  display: flex;
  flex-direction: column;
}

.filter-item label {
  margin-bottom: 4px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.filter-select {
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.search-filter {
  grid-column: span 2;
}

.search-container {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
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

/* Table styles */
.table-container {
  overflow-x: auto;
}

.courses-table, .enrollment-table {
  width: 100%;
  border-collapse: collapse;
}

.courses-table th, .enrollment-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 16px 12px;
  text-align: left;
  border-bottom: 2px solid #e9ecef;
}

.courses-table td, .enrollment-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.course-row:hover, .enrollment-row:hover {
  background: #f8f9fa;
}

/* Button styles */
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

.action-btn-small.danger {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
}

.action-btn-small.danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.action-buttons {
  display: flex;
  gap: 6px;
}

/* Badge styles */
.course-code-badge {
  background: #e8f4f8;
  color: #457B9D;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.semester-badge {
  background: #f8f9fa;
  color: #1D3557;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.matric-badge {
  background: #e8f4f8;
  color: #457B9D;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* **CRITICAL MODAL STYLES** */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 0;
  min-width: 500px;
  max-width: 90vw;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 10000;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.enrollment-modal {
  min-width: 600px;
  max-width: 700px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 28px;
  border-bottom: 1px solid #f1f3f4;
  background: #f8f9fa;
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
  border-radius: 8px;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #e9ecef;
  color: #495057;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: 24px 28px;
  max-height: calc(90vh - 140px);
  overflow-y: auto;
}

/* Search Section */
.search-section {
  margin-bottom: 24px;
}

.search-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.student-search-container {
  position: relative;
}

.search-input-wrapper {
  position: relative;
  max-width: 400px; /* Shorter search box */
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #6c757d;
  z-index: 2;
}

.search-input-field {
  width: 100%;
  padding: 12px 16px 12px 44px; /* Reduced padding */
  border: 2px solid #e1e5e9;
  border-radius: 8px; /* Smaller border radius */
  font-size: 14px;
  transition: all 0.2s ease;
  background: white;
}

.search-input-field:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

/* Student List Container - replaces dropdown */
.student-list-container {
  margin-top: 16px;
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  background: white;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 32px;
  color: #6c757d;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e1e5e9;
  border-top-color: #457B9D;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.students-grid {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.student-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.student-card:hover {
  background: #e9ecef;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.student-card.selected {
  background: rgba(69, 123, 157, 0.1);
  border-color: #457B9D;
  box-shadow: 0 0 0 2px rgba(69, 123, 157, 0.2);
}

.student-details {
  flex: 1;
}

.student-name {
  font-weight: 600;
  font-size: 14px;
  color: #1D3557;
  margin-bottom: 2px;
}

.student-matric {
  font-size: 13px;
  color: #6c757d;
}

.selection-checkbox {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
}

.checkbox-input {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #457B9D;
  border-radius: 4px;
}

.checkbox-input:checked {
  background-color: #457B9D;
  border-color: #457B9D;
}

/* Table checkbox styles */
.checkbox-column {
  width: 50px;
  text-align: center;
}

.checkbox-column .checkbox-input {
  width: 16px;
  height: 16px;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 40px 20px;
  text-align: center;
  color: #6c757d;
}

.empty-icon {
  width: 48px;
  height: 48px;
  opacity: 0.5;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 20px 28px;
  border-top: 1px solid #f1f3f4;
  background: #f8f9fa;
}

.cancel-btn {
  background: #6c757d;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: #5a6268;
}

.enroll-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.enroll-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.4);
}

.enroll-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    margin: 20px;
    min-width: 0;
    max-width: calc(100vw - 40px);
  }
  
  .enrollment-modal {
    min-width: 0;
    max-width: calc(100vw - 40px);
  }
  
  .modal-header, .modal-body, .modal-footer {
    padding-left: 20px;
    padding-right: 20px;
  }
  
  .modal-footer {
    flex-direction: column-reverse;
  }
  
  .enroll-btn, .cancel-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
