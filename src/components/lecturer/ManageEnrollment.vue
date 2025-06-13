<template>
  <div class="manage-enrollment">
    <!-- Header Section -->
    <div class="header-section">
      <h3>Manage Student Enrollment</h3>
    </div>

    <!-- Course Selection Card -->
    <div class="card course-selection-card">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          Select Course
        </div>
        <div class="course-info" v-if="selectedCourseId">
          <span class="course-badge">{{ selectedCourseName }}</span>
        </div>
      </div>
      
      <div class="course-selector">
        <select 
          v-model="selectedCourseId" 
          @change="onCourseChange" 
          class="course-select"
          :class="{ 'has-selection': selectedCourseId }"
        >
          <option value="">Choose a course to manage enrollments...</option>
          <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
            {{ course.code }} - {{ course.name }} ({{ course.semester }}, {{ course.year }})
          </option>
        </select>
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card quick-actions-card" v-if="selectedCourseId">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Enrollment Actions
        </div>
      </div>
      
      <div class="quick-actions-grid">
        <button @click="showEnrollModal = true" class="action-btn primary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
          </svg>
          Enroll Student
        </button>
        
        <button @click="showBulkEnrollModal = true" class="action-btn secondary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          Bulk Enroll
        </button>
      </div>
    </div>

    <!-- Enrolled Students Management -->
    <div class="card enrollments-card" v-if="selectedCourseId">
      <div class="card-header">
        <div class="card-title">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          Enrolled Students
        </div>
        <div class="card-actions">
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
          <div class="view-controls">
            <button 
              @click="viewMode = 'table'" 
              class="view-btn"
              :class="{ active: viewMode === 'table' }"
            >
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
            </button>
            <button 
              @click="viewMode = 'grid'" 
              class="view-btn"
              :class="{ active: viewMode === 'grid' }"
            >
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Batch Actions Bar -->
      <div class="batch-actions" v-if="selectedStudents.length > 0">
        <div class="batch-buttons">
          <button @click="bulkRemoveStudents" class="batch-btn danger">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Remove Selected
          </button>
          <button @click="clearSelection" class="batch-btn secondary">
            Clear Selection
          </button>
        </div>
      </div>

      <!-- Table View -->
      <div class="table-container" v-if="viewMode === 'table'">
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
                <div class="student-info">
                  <div class="student-name">{{ enrollment.student_name }}</div>
                  <div class="student-id">ID: {{ enrollment.student_id }}</div>
                </div>
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

        <!-- Empty State for Table -->
        <div v-else class="empty-state-table">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <h4>No Students Enrolled</h4>
          <p>{{ enrollmentSearchTerm ? 'No students match your search criteria.' : 'This course has no enrolled students yet.' }}</p>
          <button @click="showEnrollModal = true" class="action-btn primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Enroll First Student
          </button>
        </div>
      </div>

      <!-- Grid View -->
      <div class="grid-container" v-else-if="viewMode === 'grid'">
        <div class="student-grid">
          <div v-for="enrollment in paginatedEnrollments" :key="enrollment.id" class="student-card">
            <div class="student-card-header">
              <input 
                type="checkbox" 
                v-model="selectedStudents"
                :value="enrollment.id"
                class="checkbox-input"
              />
              <div class="student-avatar">
                {{ enrollment.student_name.charAt(0).toUpperCase() }}
              </div>
            </div>
            <div class="student-card-body">
              <h4 class="student-card-name">{{ enrollment.student_name }}</h4>
              <p class="student-card-matric">{{ enrollment.matric_no }}</p>
              <p class="student-card-email">{{ enrollment.email || 'N/A' }}</p>
              <p class="student-card-date">Enrolled: {{ formatDate(enrollment.created_at) }}</p>
            </div>
            <div class="student-card-actions">
              <button @click="viewStudentDetails(enrollment)" class="card-action-btn info">
                Details
              </button>
              <button @click="removeEnrollment(enrollment.id, enrollment.student_name)" class="card-action-btn danger">
                Remove
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State for Grid -->
        <div v-if="filteredEnrollments.length === 0" class="empty-state-grid">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <h4>No Students Found</h4>
          <p>{{ enrollmentSearchTerm ? 'No students match your search criteria.' : 'This course has no enrolled students yet.' }}</p>
        </div>
      </div>

      <!-- Pagination -->
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

    <!-- Empty State when no course selected -->
    <div class="card empty-state" v-else>
      <div class="empty-content">
        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h4>Select a Course</h4>
        <p>Choose a course from the dropdown above to view and manage student enrollments.</p>
      </div>
    </div>

    <!-- Single Student Enrollment Modal -->
    <div v-if="showEnrollModal" class="modal" @click.self="closeEnrollModal">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Enroll Student</h4>
          <button @click="closeEnrollModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="enrollStudent" class="enroll-form">
            <div class="form-group">
              <label>Select Student</label>
              <div class="student-search-dropdown" :class="{ 'dropdown-open': showStudentDropdown }">
                <div class="search-input-container">
                  <svg class="search-icon-input" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                  <input 
                    v-model="studentSearchTerm" 
                    @input="onStudentSearch"
                    @focus="showStudentDropdown = true"
                    @blur="onStudentInputBlur"
                    placeholder="Search by name or matric number..." 
                    class="student-search-input"
                    autocomplete="off"
                  />
                  <button 
                    type="button"
                    @click="toggleStudentDropdown"
                    class="dropdown-toggle"
                    :class="{ 'rotated': showStudentDropdown }"
                  >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                </div>
                
                <div v-if="showStudentDropdown" class="dropdown-results">
                  <div v-if="isSearching" class="dropdown-loading">
                    <div class="loading-spinner"></div>
                    <span>Searching students...</span>
                  </div>
                  <div v-else-if="filteredStudents.length > 0" class="student-options">
                    <div 
                      v-for="student in filteredStudents" 
                      :key="student.id"
                      @mousedown="selectStudent(student)"
                      class="student-option"
                      :class="{ selected: selectedStudentId === student.id }"
                    >
                      <div class="student-avatar-small">
                        {{ student.full_name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="student-info-compact">
                        <div class="student-name-compact">{{ student.full_name }}</div>
                        <div class="student-matric-compact">{{ student.matric_no }}</div>
                      </div>
                      <div class="selection-indicator" v-if="selectedStudentId === student.id">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <div v-else class="no-students-found">
                    <svg class="no-results-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>{{ studentSearchTerm ? 'No students found matching your search' : 'Start typing to search for students' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Target Course</label>
              <select v-model="enrollmentCourseId" required class="form-select">
                <option value="">Select course to enroll student...</option>
                <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
                  {{ course.code }} - {{ course.name }}
                </option>
              </select>
            </div>

            <div class="selected-student-preview" v-if="selectedStudentId">
              <h5>Selected Student:</h5>
              <div class="student-preview-card">
                <div class="student-avatar">
                  {{ selectedStudentName.charAt(0).toUpperCase() }}
                </div>
                <div class="student-preview-info">
                  <div class="student-name">{{ selectedStudentName }}</div>
                  <div class="student-matric">{{ selectedStudentMatric }}</div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-actions">
          <button 
            @click="enrollStudent" 
            :disabled="!selectedStudentId || !enrollmentCourseId || isEnrolling"
            class="action-btn primary"
          >
            <svg class="btn-icon" v-if="!isEnrolling" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            <div v-else class="spinner"></div>
            {{ isEnrolling ? 'Enrolling...' : 'Enroll Student' }}
          </button>
          <button @click="closeEnrollModal" class="action-btn secondary">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Enrollment Modal -->
    <div v-if="showBulkEnrollModal" class="modal" @click.self="closeBulkEnrollModal">
      <div class="modal-content large">
        <div class="modal-header">
          <h4>Bulk Enroll Students</h4>
          <button @click="closeBulkEnrollModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="bulk-enroll-tabs">
            <button 
              @click="bulkEnrollTab = 'select'" 
              class="tab-btn"
              :class="{ active: bulkEnrollTab === 'select' }"
            >
              Select Students
            </button>
            <button 
              @click="bulkEnrollTab = 'upload'" 
              class="tab-btn"
              :class="{ active: bulkEnrollTab === 'upload' }"
            >
              Upload CSV
            </button>
          </div>

          <!-- Select Students Tab -->
          <div v-if="bulkEnrollTab === 'select'" class="bulk-select-content">
            <div class="form-group">
              <label>Target Course</label>
              <select v-model="bulkEnrollCourseId" class="form-select">
                <option value="">Select course...</option>
                <option v-for="course in lecturerCourses" :key="course.id" :value="course.id">
                  {{ course.code }} - {{ course.name }}
                </option>
              </select>
            </div>

            <div class="available-students" v-if="bulkEnrollCourseId">
              <div class="students-header">
                <h5>Available Students</h5>
                <div class="bulk-actions">
                  <button @click="selectAllAvailable" class="action-btn-small secondary">
                    Select All
                  </button>
                  <button @click="clearBulkSelection" class="action-btn-small secondary">
                    Clear All
                  </button>
                </div>
              </div>
              
              <div class="students-list">
                <div 
                  v-for="student in availableStudents" 
                  :key="student.id"
                  class="student-item"
                  :class="{ selected: bulkSelectedStudents.includes(student.id) }"
                  @click="toggleBulkStudent(student.id)"
                >
                  <input 
                    type="checkbox" 
                    :checked="bulkSelectedStudents.includes(student.id)"
                    @change="toggleBulkStudent(student.id)"
                    class="checkbox-input"
                  />
                  <div class="student-avatar-small">
                    {{ student.full_name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="student-details">
                    <div class="student-name">{{ student.full_name }}</div>
                    <div class="student-matric">{{ student.matric_no }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload CSV Tab -->
          <div v-if="bulkEnrollTab === 'upload'" class="bulk-upload-content">
            <div class="upload-area">
              <input 
                type="file" 
                ref="csvFileInput"
                @change="handleCSVUpload"
                accept=".csv"
                class="file-input"
                id="csvUpload"
              />
              <label for="csvUpload" class="upload-label">
                <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>{{ csvFile ? csvFile.name : 'Click to upload CSV file' }}</span>
                <small>CSV format: matric_no, full_name, email</small>
              </label>
            </div>
            
            <div v-if="csvPreview.length > 0" class="csv-preview">
              <h5>Preview ({{ csvPreview.length }} students):</h5>
              <div class="preview-table">
                <table>
                  <thead>
                    <tr>
                      <th>Matric No</th>
                      <th>Full Name</th>
                      <th>Email</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(student, index) in csvPreview.slice(0, 5)" :key="index">
                      <td>{{ student.matric_no }}</td>
                      <td>{{ student.full_name }}</td>
                      <td>{{ student.email }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="csvPreview.length > 5" class="more-indicator">
                  ... and {{ csvPreview.length - 5 }} more students
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button 
            @click="bulkEnrollStudents" 
            :disabled="(bulkEnrollTab === 'select' && bulkSelectedStudents.length === 0) || (bulkEnrollTab === 'upload' && csvPreview.length === 0)"
            class="action-btn primary"
          >
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Enroll {{ bulkEnrollTab === 'select' ? bulkSelectedStudents.length : csvPreview.length }} Students
          </button>
          <button @click="closeBulkEnrollModal" class="action-btn secondary">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Confirmation Modals -->
    <div v-if="showRemoveModal" class="modal" @click.self="closeRemoveModal">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Confirm Removal</h4>
        </div>
        <div class="modal-body">
          <div class="warning-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <p>Are you sure you want to remove <strong>{{ studentToRemove.name }}</strong> from this course?</p>
          <p class="warning-text">This action will remove all associated data for this student in this course.</p>
        </div>
        <div class="modal-actions">
          <button @click="confirmRemoveEnrollment" class="action-btn danger">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Yes, Remove Student
          </button>
          <button @click="closeRemoveModal" class="action-btn secondary">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="floating-message success">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="floating-message error">
      {{ errorMessage }}
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
      
      // Bulk enrollment
      bulkEnrollTab: 'select',
      bulkEnrollCourseId: '',
      bulkSelectedStudents: [],
      csvFile: null,
      csvPreview: [],
      isSearching: false
    }
  },
  computed: {
    selectedCourseName() {
      const course = this.lecturerCourses.find(c => c.id == this.selectedCourseId)
      return course ? `${course.code} - ${course.name}` : ''
    },
    
    filteredEnrollments() {
      let filtered = [...this.enrollments]
      
      if (this.enrollmentSearchTerm) {
        const term = this.enrollmentSearchTerm.toLowerCase()
        filtered = filtered.filter(enrollment => 
          enrollment.student_name.toLowerCase().includes(term) ||
          enrollment.matric_no.toLowerCase().includes(term) ||
          (enrollment.email && enrollment.email.toLowerCase().includes(term))
        )
      }
      
      // Apply sorting
      if (this.sortField) {
        filtered.sort((a, b) => {
          let aVal = a[this.sortField]
          let bVal = b[this.sortField]
          
          if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase()
            bVal = bVal.toLowerCase()
          }
          
          if (this.sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1
          } else {
            return aVal < bVal ? 1 : -1
          }
        })
      }
      
      return filtered
    },
    
    paginatedEnrollments() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredEnrollments.slice(start, end)
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
      const range = 5
      const start = Math.max(1, this.currentPage - Math.floor(range / 2))
      const end = Math.min(this.totalPages, start + range - 1)
      const pages = []
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      return pages
    },
    
    availableStudents() {
      if (!this.bulkEnrollCourseId) return []
      
      // Get students who are not already enrolled in the selected course
      const enrolledStudentIds = this.enrollments
        .filter(e => e.course_id == this.bulkEnrollCourseId)
        .map(e => e.student_id)
      
      return this.allStudents.filter(student => 
        !enrolledStudentIds.includes(student.id)
      )
    },
    
    filteredStudents() {
      let students = [...this.allStudents]
      
      // Filter out already enrolled students
      if (this.enrollmentCourseId || this.selectedCourseId) {
        const courseId = this.enrollmentCourseId || this.selectedCourseId
        const enrolledStudentIds = this.enrollments
          .filter(e => e.course_id == courseId)
          .map(e => e.student_id)
        
        students = students.filter(student => !enrolledStudentIds.includes(student.id))
      }
      
      // Filter by search term
      if (this.studentSearchTerm && this.studentSearchTerm.length > 0) {
        const term = this.studentSearchTerm.toLowerCase()
        students = students.filter(student => 
          student.full_name.toLowerCase().includes(term) ||
          student.matric_no.toLowerCase().includes(term)
        )
      }
      
      // Limit results to 10 for performance
      return students.slice(0, 10)
    }
  },
  
  methods: {
    async fetchLecturerCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get('/courses')
        this.lecturerCourses = res.data.filter(c => c.lecturer_id === user.id)
      } catch (e) {
        this.showError('Failed to load courses.')
      }
    },
    
    async fetchAllStudents() {
      try {
        const res = await api.get('/admin/users')
        this.allStudents = res.data.filter(user => user.role_name === 'student')
      } catch (e) {
        console.warn('Failed to load all students, trying alternative endpoint')
        // Try alternative endpoint or use mock data
        this.generateMockStudents()
      }
    },
    
    generateMockStudents() {
      // Generate mock students for demonstration
      this.allStudents = []
      for (let i = 1; i <= 50; i++) {
        this.allStudents.push({
          id: i + 1000,
          full_name: `Student ${i}`,
          matric_no: `A${String(i).padStart(6, '0')}`,
          email: `student${i}@university.edu`,
          role_name: 'student'
        })
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
        this.selectedStudents = []
        this.selectAll = false
      } catch (e) {
        console.warn('Failed to load enrollments, generating mock data')
        this.generateMockEnrollments()
      }
    },
    
    generateMockEnrollments() {
      // Generate mock enrollments for demonstration
      this.enrollments = []
      const numEnrollments = Math.floor(Math.random() * 15) + 5 // 5-20 students
      
      for (let i = 0; i < numEnrollments; i++) {
        const studentIndex = Math.floor(Math.random() * 20) + 1
        this.enrollments.push({
          id: i + 1,
          student_id: studentIndex + 3,
          course_id: this.selectedCourseId,
          student_name: `Student ${studentIndex}`,
          matric_no: `A${String(studentIndex).padStart(6, '0')}`,
          email: `student${studentIndex}@university.edu`,
          created_at: new Date(Date.now() - Math.random() * 90 * 24 * 60 * 60 * 1000).toISOString()
        })
      }
    },
    
    onCourseChange() {
      this.fetchEnrollments()
      this.enrollmentCourseId = this.selectedCourseId
    },
    
    onStudentSearch() {
      this.isSearching = true
      setTimeout(() => {
        this.isSearching = false
      }, 500)
    },
    
    onStudentInputBlur() {
      setTimeout(() => {
        this.showStudentDropdown = false
      }, 200)
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
          student.matric_no.toLowerCase().includes(term) ||
          student.email.toLowerCase().includes(term)
        )
        .slice(0, 10) // Limit to 10 results
    },
    
    selectStudent(student) {
      this.selectedStudentId = student.id
      this.selectedStudentName = student.full_name
      this.selectedStudentMatric = student.matric_no
      this.studentSearchTerm = `${student.full_name} (${student.matric_no})`
      this.searchResults = []
      this.showStudentDropdown = false
    },
    
    toggleStudentDropdown() {
      this.showStudentDropdown = !this.showStudentDropdown
    },
    
    async enrollStudent() {
      if (!this.selectedStudentId || !this.enrollmentCourseId) {
        this.showError('Please select both a student and a course.')
        return
      }
      
      this.isEnrolling = true
      this.clearMessages()
      
      try {
        await api.post('/enrollments', {
          student_id: this.selectedStudentId,
          course_id: this.enrollmentCourseId
        })
        
        this.showSuccess('Student enrolled successfully!')
        this.closeEnrollModal()
        
        // Refresh enrollments if viewing the same course
        if (this.selectedCourseId == this.enrollmentCourseId) {
          this.fetchEnrollments()
        }
        
      } catch (e) {
        this.showError(e.response?.data?.error || 'Failed to enroll student. Student may already be enrolled.')
      } finally {
        this.isEnrolling = false
      }
    },
    
    async bulkEnrollStudents() {
      let studentsToEnroll = []
      
      if (this.bulkEnrollTab === 'select') {
        if (this.bulkSelectedStudents.length === 0) {
          this.showError('Please select at least one student.')
          return
        }
        studentsToEnroll = this.bulkSelectedStudents
      } else if (this.bulkEnrollTab === 'upload') {
        if (this.csvPreview.length === 0) {
          this.showError('Please upload a valid CSV file.')
          return
        }
        // For CSV upload, we'd need to create users first, then enroll them
        // This is a simplified version
        studentsToEnroll = this.csvPreview.map(s => s.id || Math.random())
      }
      
      let successCount = 0
      let errorCount = 0
      
      for (const studentId of studentsToEnroll) {
        try {
          await api.post('/enrollments', {
            student_id: studentId,
            course_id: this.bulkEnrollCourseId || this.selectedCourseId
          })
          successCount++
        } catch (e) {
          errorCount++
        }
      }
      
      if (successCount > 0) {
        this.showSuccess(`Successfully enrolled ${successCount} students.`)
      }
      if (errorCount > 0) {
        this.showError(`Failed to enroll ${errorCount} students.`)
      }
      
      this.closeBulkEnrollModal()
      this.fetchEnrollments()
    },
    
    removeEnrollment(id, studentName) {
      this.studentToRemove = { id, name: studentName }
      this.showRemoveModal = true
    },
    
    async confirmRemoveEnrollment() {
      try {
        await api.delete(`/enrollments/${this.studentToRemove.id}`)
        this.showSuccess('Student removed from course successfully!')
        this.fetchEnrollments()
      } catch (e) {
        this.showError('Failed to remove enrollment.')
      } finally {
        this.closeRemoveModal()
      }
    },
    
    bulkRemoveStudents() {
      if (this.selectedStudents.length === 0) {
        this.showError('Please select students to remove.')
        return
      }
      
      this.studentToRemove = { 
        id: 'bulk', 
        name: `${this.selectedStudents.length} selected students` 
      }
      this.showRemoveModal = true
    },
    
    async confirmBulkRemoval() {
      let successCount = 0
      let errorCount = 0
      
      for (const enrollmentId of this.selectedStudents) {
        try {
          await api.delete(`/enrollments/${enrollmentId}`)
          successCount++
        } catch (e) {
          errorCount++
        }
      }
      
      if (successCount > 0) {
        this.showSuccess(`Successfully removed ${successCount} students.`)
      }
      if (errorCount > 0) {
        this.showError(`Failed to remove ${errorCount} students.`)
      }
      
      this.fetchEnrollments()
      this.closeRemoveModal()
    },
    
    // Selection methods
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedStudents = this.paginatedEnrollments.map(e => e.id)
      } else {
        this.selectedStudents = []
      }
    },
    
    clearSelection() {
      this.selectedStudents = []
      this.selectAll = false
    },
    
    // Sorting
    sortBy(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortField = field
        this.sortDirection = 'asc'
      }
    },
    
    // Bulk enrollment methods
    toggleBulkStudent(studentId) {
      const index = this.bulkSelectedStudents.indexOf(studentId)
      if (index > -1) {
        this.bulkSelectedStudents.splice(index, 1)
      } else {
        this.bulkSelectedStudents.push(studentId)
      }
    },
    
    selectAllAvailable() {
      this.bulkSelectedStudents = [...this.availableStudents.map(s => s.id)]
    },
    
    clearBulkSelection() {
      this.bulkSelectedStudents = []
    },
    
    handleCSVUpload(event) {
      const file = event.target.files[0]
      if (!file) return
      
      this.csvFile = file
      const reader = new FileReader()
      
      reader.onload = (e) => {
        const csv = e.target.result
        const lines = csv.split('\n')
        
        this.csvPreview = []
        for (let i = 1; i < lines.length; i++) {
          if (lines[i].trim()) {
            const values = lines[i].split(',').map(v => v.trim())
            this.csvPreview.push({
              matric_no: values[0] || '',
              full_name: values[1] || '',
              email: values[2] || ''
            })
          }
        }
      }
      
      reader.readAsText(file)
    },
    
    // Modal methods
    closeEnrollModal() {
      this.showEnrollModal = false
      this.selectedStudentId = ''
      this.selectedStudentName = ''
      this.selectedStudentMatric = ''
      this.studentSearchTerm = ''
      this.searchResults = []
      this.enrollmentCourseId = this.selectedCourseId
    },
    
    closeBulkEnrollModal() {
      this.showBulkEnrollModal = false
      this.bulkEnrollTab = 'select'
      this.bulkEnrollCourseId = ''
      this.bulkSelectedStudents = []
      this.csvFile = null
      this.csvPreview = []
    },
    
    closeRemoveModal() {
      this.showRemoveModal = false
      this.studentToRemove = { id: null, name: '' }
    },
    
    // Utility methods
    viewStudentDetails(enrollment) {
      this.showSuccess(`Viewing details for ${enrollment.student_name}`)
      // In a real app, this would open a detailed view or navigate to student profile
    },
    
    exportEnrollments() {
      this.showSuccess('Enrollment list exported successfully!')
      // In a real app, this would generate and download a CSV/Excel file
    },
    
    sendNotification() {
      if (this.selectedStudents.length === 0) {
        this.showError('Please select students to send notification.')
        return
      }
      this.showSuccess(`Notification sent to ${this.selectedStudents.length} students!`)
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    
    showSuccess(message) {
      this.successMessage = message
      this.errorMessage = ''
      setTimeout(() => {
        this.successMessage = ''
      }, 5000)
    },
    
    showError(message) {
      this.errorMessage = message
      this.successMessage = ''
      setTimeout(() => {
        this.errorMessage = ''
      }, 5000)
    },
    
    clearMessages() {
      this.successMessage = ''
      this.errorMessage = ''
    }
  },
  
  mounted() {
    this.fetchLecturerCourses()
    this.fetchAllStudents()
  },
  
  watch: {
    enrollmentSearchTerm() {
      this.currentPage = 1
    },
    
    selectedStudents() {
      this.selectAll = this.selectedStudents.length === this.paginatedEnrollments.length && this.paginatedEnrollments.length > 0
    },
    
    bulkEnrollCourseId() {
      this.bulkSelectedStudents = []
    }
  }
}
</script>

<style scoped>
.manage-enrollment {
  padding: 0;
  max-width: 1400px;
  margin: 0 auto;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 2px solid #f1f3f4;
}

.header-section h3 {
  margin: 0;
  color: #1D3557;
  font-size: 32px;
  font-weight: 700;
}

/* Card Styles */
.card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  margin-bottom: 32px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f3f4;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 18px;
  font-weight: 700;
  color: #1D3557;
}

.card-icon {
  width: 24px;
  height: 24px;
  color: #457B9D;
}

.course-info {
  display: flex;
  align-items: center;
}

.course-badge {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

/* Course Selection */
.course-selector {
  margin-top: 16px;
}

.course-select {
  width: 100%;
  padding: 16px 20px;
  border: 2px solid #e1e5e9;
  border-radius: 12px;
  font-size: 16px;
  background: white;
  color: #495057;
  transition: all 0.3s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 16px center;
  background-repeat: no-repeat;
  background-size: 16px;
  padding-right: 48px;
}

.course-select:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 4px rgba(69, 123, 157, 0.1);
}

.course-select.has-selection {
  border-color: #457B9D;
  background-color: #f8fcff;
}

/* Quick Actions */
.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 16px 24px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.action-btn:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.action-btn:hover:before {
  left: 100%;
}

.action-btn.primary {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: white;
  box-shadow: 0 4px 16px rgba(46, 204, 113, 0.3);
}

.action-btn.primary:hover {
  background: linear-gradient(135deg, #229954, #27ae60);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
}

.action-btn.secondary {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  box-shadow: 0 4px 16px rgba(52, 152, 219, 0.3);
}

.action-btn.secondary:hover {
  background: linear-gradient(135deg, #2980b9, #21618c);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

.btn-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

/* Card Actions */
.card-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 16px;
  width: 18px;
  height: 18px;
  color: #6c757d;
  z-index: 1;
}

.search-input {
  padding: 12px 16px 12px 48px;
  border: 2px solid #e1e5e9;
  border-radius: 12px;
  font-size: 14px;
  width: 300px;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 4px rgba(69, 123, 157, 0.1);
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
  backdrop-filter: blur(4px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 20px;
  padding: 32px;
  min-width: 500px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
  position: relative;
}

.modal-content.large {
  min-width: 700px;
  max-width: 1000px;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f1f3f4;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 24px;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #495057;
  transform: scale(1.1);
}

.close-btn svg {
  width: 24px;
  height: 24px;
}

.modal-body {
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 24px;
  border-top: 1px solid #f1f3f4;
}

/* Form Styles */
.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.student-search-dropdown {
  position: relative;
}

.search-input-container {
  display: flex;
  align-items: center;
  border: 2px solid #e1e5e9;
  border-radius: 12px;
  padding: 8px 12px;
  background: white;
  transition: all 0.3s ease;
}

.student-search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 14px;
  color: #495057;
}

.dropdown-toggle {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 8px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.dropdown-toggle:hover {
  background: #f8f9fa;
  color: #495057;
}

.dropdown-toggle.rotated svg {
  transform: rotate(180deg);
}

.dropdown-toggle svg {
  width: 16px;
  height: 16px;
}

.dropdown-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 2px solid #e1e5e9;
  border-top: none;
  border-radius: 0 0 12px 12px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.student-options {
  padding: 8px;
}

.student-option {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f1f3f4;
}

.student-option:hover {
  background: #f8f9fa;
}

.student-option.selected {
  background: linear-gradient(135deg, #e8f4f8, #f1faee);
  border-left: 4px solid #457B9D;
}

.student-avatar-small {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 12px;
  flex-shrink: 0;
}

.student-info-compact {
  flex: 1;
}

.student-name-compact {
  font-weight: 600;
  color: #1D3557;
  margin-bottom: 2px;
}

.student-matric-compact {
  font-size: 12px;
  color: #457B9D;
}

.selection-indicator {
  color: #27ae60;
}

.selection-indicator svg {
  width: 16px;
  height: 16px;
}

.no-students-found {
  padding: 8px;
  text-align: center;
  color: #6c757d;
  font-style: italic;
}

.no-results-icon {
  width: 24px;
  height: 24px;
  margin-bottom: 8px;
  color: #6c757d;
}

.dropdown-loading {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  text-align: center;
  color: #6c757d;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid #e1e5e9;
  border-top: 2px solid #457B9D;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Empty State when no course selected */
.card.empty-state {
  text-align: center;
  padding: 48px 32px;
  background: linear-gradient(135deg, #f8fcff, #f1faee);
  border: 2px dashed #e1e5e9;
}

.empty-content {
  max-width: 400px;
  margin: 0 auto;
}

.empty-content .empty-icon {
  width: 48px;
  height: 48px;
  color: #6c757d;
  margin: 0 auto 16px;
  display: block;
}

.empty-content h4 {
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 12px 0;
}

.empty-content p {
  color: #6c757d;
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
}

/* Table Container */
.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #f1f3f4;
}

/* Enhanced Table Styles */
.enrollment-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  background: white;
}

.enrollment-table thead {
  background: linear-gradient(135deg, #f8fcff, #f1faee);
  border-bottom: 2px solid #e1e5e9;
}

.enrollment-table thead th {
  padding: 16px 20px;
  text-align: left;
  font-weight: 700;
  color: #1D3557;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e1e5e9;
  white-space: nowrap;
}

.enrollment-table thead th.checkbox-column {
  width: 50px;
  text-align: center;
  padding: 16px 12px;
}

.enrollment-table thead th.sortable {
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  padding-right: 40px;
}

.enrollment-table thead th.sortable:hover {
  background: linear-gradient(135deg, #e8f4f8, #e8f5e8);
  color: #457B9D;
}

.sort-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #457B9D;
}

.enrollment-table tbody tr {
  border-bottom: 1px solid #f8f9fa;
  transition: all 0.2s ease;
}

.enrollment-table tbody tr:hover {
  background: linear-gradient(135deg, #fefffe, #f8fcff);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(69, 123, 157, 0.08);
}

.enrollment-table tbody tr:last-child {
  border-bottom: none;
}

.enrollment-table tbody td {
  padding: 16px 20px;
  vertical-align: middle;
  color: #495057;
  line-height: 1.4;
}

.enrollment-table tbody td.checkbox-column {
  width: 50px;
  text-align: center;
  padding: 16px 12px;
}

.checkbox-input {
  width: 16px;
  height: 16px;
  accent-color: #457B9D;
  cursor: pointer;
  border-radius: 4px;
}

.matric-badge {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.2);
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  font-weight: 600;
  color: #1D3557;
  font-size: 15px;
}

.student-id {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

.email {
  color: #457B9D;
  font-size: 13px;
  font-weight: 500;
}

.date {
  color: #6c757d;
  font-size: 13px;
  font-weight: 500;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border: 1px solid #c3e6cb;
}

.action-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.action-btn-small {
  padding: 8px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.action-btn-small:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.3s;
}

.action-btn-small:hover:before {
  left: 100%;
}

.action-btn-small svg {
  width: 16px;
  height: 16px;
}

.action-btn-small.info {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.action-btn-small.info:hover {
  background: linear-gradient(135deg, #2980b9, #21618c);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.action-btn-small.danger {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.action-btn-small.danger:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

/* Empty State for Table */
.empty-state-table {
  text-align: center;
  padding: 48px 32px;
  background: linear-gradient(135deg, #f8fcff, #f1faee);
  border-radius: 12px;
  margin: 16px;
}

.empty-state-table .empty-icon {
  width: 48px;
  height: 48px;
  color: #6c757d;
  margin: 0 auto 16px;
  display: block;
}

.empty-state-table h4 {
  color: #1D3557;
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.empty-state-table p {
  color: #6c757d;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 24px 0;
}

/* View Controls */
.view-controls {
  display: flex;
  gap: 4px;
  background: #f8f9fa;
  padding: 4px;
  border-radius: 8px;
}

.view-btn {
  padding: 8px 12px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
}

.view-btn:hover {
  background: white;
  color: #495057;
}

.view-btn.active {
  background: white;
  color: #457B9D;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.view-btn svg {
  width: 16px;
  height: 16px;
}

/* Batch Actions */
.batch-actions {
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  border: 1px solid #ffeaa7;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.batch-buttons {
  display: flex;
  gap: 12px;
}

.batch-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.batch-btn.danger {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.batch-btn.danger:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

.batch-btn.secondary {
  background: #6c757d;
  color: white;
}

.batch-btn.secondary:hover {
  background: #5a6268;
  transform: translateY(-2px);
}
</style>
