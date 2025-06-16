<template>
  <div>
    <div class="header-section">
      <h3>Manage Assessment Marks</h3>
      <p class="subtitle">Enter assessment marks for your courses</p>
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
              <th>Course Details</th>
              <th>Enrolled Students</th>
              <th>Graded Assessment Progress</th>
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
              <td class="enrollment-cell">
                <div class="enrollment-info">
                  <span class="enrollment-count">{{ course.enrolledCount || 0 }}</span>
                  <span class="enrollment-label">students</span>
                </div>
              </td>
              <td class="progress-cell">
                <div class="progress-display">
                  <div class="progress-stats">
                    <span class="progress-text">{{ course.gradedCount || 0 }} / {{ course.enrolledCount || 0 }} graded</span>
                    <span class="progress-percentage">{{ getProgressPercentage(course) }}%</span>
                  </div>
                  <div class="progress-bar-small">
                    <div 
                      class="progress-fill-small" 
                      :style="{ width: getProgressPercentage(course) + '%' }"
                      :class="getProgressClass(course)"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="actions-cell">
                <div class="action-buttons-group">
                  <button @click="openAssessmentsModal(course)" class="action-btn primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 2 002 2h8a2 2 2 002-2V7a2 2 2 002-2h-2M9 5a2 2 0 002 2h2a2 2 2 002-2M9 5a2 2 0 012-2h2a2 2 2 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    View Assessments
                  </button>
                  
                  <div class="action-buttons-secondary">
                    <button @click="openUploadModal(course)" class="action-btn-small upload">
                      <svg class="btn-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      Upload CSV
                    </button>
                    
                    <button @click="openExportModal(course)" class="action-btn-small download">
                      <svg class="btn-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 2 01-2-2V5a2 2 2 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 2 01-2 2z"></path>
                      </svg>
                      Export CSV
                    </button>
                  </div>
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

    <!-- Assessments Modal -->
    <div v-if="showAssessmentsModal" class="modal-overlay" @click.self="closeAssessmentsModal">
      <div class="modal-content assessments-modal">
        <div class="modal-header">
          <h4>{{ selectedCourse?.code }} - Assessments & Final Exam</h4>
          <button @click="closeAssessmentsModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Assessment Instructions -->
        <div class="instructions-section">
          <div class="instruction-header">
            <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h5>Assessment Overview</h5>
          </div>
          <ul class="instruction-list">
            <li>View grading progress for all assessments and final exam</li>
            <li>Click "Enter Marks" to grade individual assessments</li>
            <li>Final exam carries <strong>30%</strong> of the total course grade</li>
            <li>All assessments combined carry <strong>70%</strong> of the total grade</li>
          </ul>
        </div>

        <!-- Assessments Table -->
        <div class="table-wrapper">
          <table class="assessments-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Weight (%)</th>
                <th>Grading Progress</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="assessment in courseAssessments" :key="assessment.id" class="assessment-row">
                <td>
                  <div class="assessment-title">{{ assessment.title }}</div>
                </td>
                <td>
                  <span class="weight-text">{{ assessment.weight }}%</span>
                </td>
                <td>
                  <div class="progress-display">
                    <div class="progress-stats">
                      <span class="progress-text">{{ assessment.gradedCount || 0 }} / {{ selectedCourse?.enrolledCount || 0 }} graded</span>
                      <span class="progress-percentage">{{ getAssessmentProgressPercentage(assessment) }}%</span>
                    </div>
                    <div class="progress-bar-small">
                      <div 
                        class="progress-fill-small" 
                        :style="{ width: getAssessmentProgressPercentage(assessment) + '%' }"
                        :class="getProgressClass({ gradedCount: assessment.gradedCount, enrolledCount: selectedCourse?.enrolledCount })"
                      ></div>
                    </div>
                  </div>
                </td>
                <td>
                  <button @click="openStudentsModal(selectedCourse, assessment)" class="action-btn primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 2 00-2 2v11a2 2 2 002 2h11a2 2 2 002-2v-5m-1.414-9.414a2 2 2 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Enter Marks
                  </button>
                </td>
              </tr>
              <!-- Final Exam Row -->
              <tr class="final-exam-row">
                <td>
                  <div class="assessment-title">Final Examination</div>
                </td>
                <td>
                  <span class="weight-text">30%</span>
                </td>
                <td>
                  <div class="progress-display">
                    <div class="progress-stats">
                      <span class="progress-text">{{ finalExamProgress.gradedCount || 0 }} / {{ selectedCourse?.enrolledCount || 0 }} graded</span>
                      <span class="progress-percentage">{{ getAssessmentProgressPercentage(finalExamProgress) }}%</span>
                    </div>
                    <div class="progress-bar-small">
                      <div 
                        class="progress-fill-small" 
                        :style="{ width: getAssessmentProgressPercentage(finalExamProgress) + '%' }"
                        :class="getProgressClass({ gradedCount: finalExamProgress.gradedCount, enrolledCount: selectedCourse?.enrolledCount })"
                      ></div>
                    </div>
                  </div>
                </td>
                <td>
                  <button @click="openStudentsModal(selectedCourse, { type: 'Final Exam', id: 'final' })" class="action-btn primary">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 2 00-2 2v11a2 2 2 002 2h11a2 2 2 002-2v-5m-1.414-9.414a2 2 2 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Enter Marks
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Students Modal -->
    <div v-if="showStudentsModal" class="modal-overlay" @click.self="closeStudentsModal">
      <div class="modal-content students-modal">
        <div class="modal-header">
          <h4 v-if="selectedAssessment && selectedAssessment.id !== 'final'">
            {{ selectedCourse?.code }} - {{ selectedAssessment.title || selectedAssessment.name }} Marks
          </h4>
          <h4 v-else>
            {{ selectedCourse?.code }} - Final Exam Marks
          </h4>
          <button @click="closeStudentsModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Assessment/Final Exam Instructions -->
        <div class="instructions-section">
          <div class="instruction-header">
            <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h5 v-if="selectedAssessment && selectedAssessment.id !== 'final'">Assessment Guidelines</h5>
            <h5 v-else>Final Exam Guidelines</h5>
          </div>
          <ul class="instruction-list" v-if="selectedAssessment && selectedAssessment.id !== 'final'">
            <li>Assessment: <strong>{{ selectedAssessment.title || selectedAssessment.name }}</strong></li>
            <li>Weight: <strong>{{ selectedAssessment.weight }}%</strong> of the total course grade</li>
            <li>Enter marks out of <strong>{{ selectedAssessment.max_mark || 100 }}</strong></li>
            <li>Students will be notified automatically when marks are entered</li>
          </ul>
          <ul class="instruction-list" v-else>
            <li>Final exam carries <strong>30%</strong> of the total course grade</li>
            <li>Enter marks out of <strong>100</strong> (will be automatically weighted)</li>
            <li>Use inline editing or batch upload for efficiency</li>
            <li>Students will be notified automatically when marks are entered</li>
          </ul>
        </div>

        <div class="modal-controls">
          <div class="search-filter-container">
            <div class="search-container">
              <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
              </svg>
              <input 
                v-model="searchTerm" 
                placeholder="Search students..."
                class="search-input"
              />
            </div>
            <div class="filter-container">
              <select v-model="filterStatus" class="filter-dropdown">
                <option value="all">All</option>
                <option value="graded">Graded</option>
                <option value="ungraded">Ungraded</option>
              </select>
            </div>
          </div>
          <div class="action-controls">
            <button 
              @click="showBatchModal = true" 
              class="control-btn secondary"
              :disabled="filteredStudents.length === 0"
            >
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 2 002-2V5a2 2 2 002-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 1 01.293.707V19a2 2 2 002-2z"></path>
              </svg>
              Batch Entry
            </button>
            <button 
              @click="submitAllMarks" 
              class="control-btn primary"
              :disabled="!hasUnsavedChanges"
            >
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
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
                <th v-if="selectedAssessment && selectedAssessment.id !== 'final'">
                  {{ selectedAssessment.title || selectedAssessment.name }} Mark
                </th>
                <th v-else>Final Exam Mark</th>
                <th>Weighted</th>
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
                <td>
                  <span class="matric-badge">{{ student.matric_no }}</span>
                </td>
                <td>{{ student.student_name }}</td>
                <td>
                  <div class="mark-input-wrapper">
                    <input 
                      v-model.number="student.assessment_mark"
                      @input="onMarkChange(student)"
                      type="number" 
                      step="0.1" 
                      min="0" 
                      :max="selectedAssessment && selectedAssessment.id !== 'final' ? selectedAssessment.max_mark || 100 : 100"
                      class="mark-input"
                      :class="{ 
                        'has-error': getMarkError(student.assessment_mark),
                        'has-changes': pendingMarks[student.enrollment_id] !== undefined
                      }"
                      placeholder="0.0"
                    />
                    <span class="mark-suffix">/ {{ selectedAssessment && selectedAssessment.id !== 'final' ? selectedAssessment.max_mark || 100 : 100 }}</span>
                    <div v-if="getMarkError(student.assessment_mark)" class="input-error">
                      {{ getMarkError(student.assessment_mark) }}
                    </div>
                  </div>
                </td>
                <td>
                  <span class="weighted-mark">
                    {{ student.weighted_mark }}
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
                      <svg class="btn-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </button>
                    <button 
                      v-if="student.original_mark !== null"
                      @click="resetMark(student)"
                      class="reset-btn"
                      title="Reset to original"
                    >
                      <svg class="btn-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                      </svg>
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
        <div v-else-if="students.length === 0" class="empty-state-modal">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 6 6 0 0112 0v1zm0 0h6v-1a6 6 6 6 0 00-9-5.197m13.5-9a2.5 2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <h4>No Students Enrolled</h4>
          <p>No students are enrolled in this course.</p>
        </div>

        <!-- No Search Results -->
        <div v-else-if="filteredStudents.length === 0" class="no-results">
          <svg class="search-icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <h4>No Students Found</h4>
          <p>No students match your search criteria.</p>
        </div>
      </div>
    </div>

    <!-- Batch Entry Modal -->
    <div v-if="showBatchModal" class="modal-overlay" @click.self="closeBatchModal">
      <div class="modal-content batch-modal">
        <div class="modal-header">
          <h4>Batch Mark Entry</h4>
          <button @click="closeBatchModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <p class="batch-description">Enter marks for all students separated by commas in the same order as displayed in the table.</p>
        
        <div class="batch-students">
          <h5>Student Order:</h5>
          <div class="student-list-container">
            <ol class="student-list">
              <li v-for="student in filteredStudents" :key="student.id">
                {{ student.matric_no }} - {{ student.student_name }}
              </li>
            </ol>
          </div>
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
          <button @click="applyBatchMarks" :disabled="!batchMarks.trim()" class="apply-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Apply Marks
          </button>
          <button @click="closeBatchModal" type="button" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Bulk Upload CSV Modal -->
    <BulkUploadCSVModal 
      :isVisible="showBulkUploadModal"
      :courseInfo="selectedCourseForModal"
      @close="closeBulkUploadModal"
      @upload-complete="onUploadComplete"
    />

    <!-- Export CSV Modal -->
    <ExportCSVModal 
      :isVisible="showExportModal"
      :courseInfo="selectedCourseForModal"
      @close="closeExportModal"
      @export-complete="onExportComplete"
    />

    <!-- Success/Error Messages -->
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
import api from '../../api';
import BulkUploadCSVModal from './BulkUploadCSV.vue';
import ExportCSVModal from './ExportCSV.vue';

export default {
  name: 'EnterFinalExam',
  components: {
    BulkUploadCSVModal,
    ExportCSVModal
  },
  data() {
    return {
      lecturerCourses: [],
      students: [],
      selectedCourse: null,
      showStudentsModal: false,
      showAssessmentsModal: false, // Add showAssessmentsModal
      courseAssessments: [], // Add courseAssessments
      finalExamProgress: {}, // Add finalExamProgress
      searchTerm: '',
      pendingMarks: {},
      showBatchModal: false,
      batchMarks: '',
      currentPage: 1,
      itemsPerPage: 15,
      success: '',
      error: '',
      filterStatus: 'all', // Add filter status
      selectedAssessment: null, // Add selectedAssessment
      showBulkUploadModal: false,
      showExportModal: false,
      selectedCourseForModal: null
    }
  },
  computed: {
    filteredStudents() {
      let filtered = this.students;

      // Filter by grading status - use assessment_mark instead of final_mark
      if (this.filterStatus === 'graded') {
        filtered = filtered.filter(student => 
          student.assessment_mark !== null && student.assessment_mark !== ''
        );
      } else if (this.filterStatus === 'ungraded') {
        filtered = filtered.filter(student => 
          student.assessment_mark === null || student.assessment_mark === ''
        );
      }

      // Filter by search term
      if (this.searchTerm) {
        const term = this.searchTerm.toLowerCase();
        filtered = filtered.filter(student => 
          student.student_name.toLowerCase().includes(term) ||
          student.matric_no.toLowerCase().includes(term)
        );
      }

      return filtered;
    },
    paginatedStudents() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredStudents.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.itemsPerPage);
    },
    gradedCount() {
      return this.students.filter(s => s.assessment_mark !== null && s.assessment_mark !== '').length;
    },
    hasUnsavedChanges() {
      return Object.keys(this.pendingMarks).length > 0;
    }
  },
  methods: {
    async fetchLecturerCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'));
        const res = await api.get('/courses');
        this.lecturerCourses = res.data.filter(c => c.lecturer_id === user.id);
        
        // Fetch enrollment and grading stats for each course
        for (let course of this.lecturerCourses) {
          await this.fetchCourseStats(course);
        }
      } catch (e) {
        this.showError('Failed to load courses.');
      }
    },

    async fetchCourseStats(course) {
      try {
        const enrollmentsRes = await api.get(`/courses/${course.id}/enrollments`);
        const enrollments = enrollmentsRes.data || [];
        
        course.enrolledCount = enrollments.length;
        course.gradedCount = enrollments.filter(enrollment => 
          enrollment.final_mark !== null && enrollment.final_mark !== ''
        ).length;
      } catch (e) {
        course.enrolledCount = 0;
        course.gradedCount = 0;
      }
    },

    async fetchAssessments(courseId) {
      try {
        // Fetch assessments from the actual API endpoint
        const assessmentsRes = await api.get(`/courses/${courseId}/assessments`);
        const assessments = assessmentsRes.data || [];
        
        // Fetch enrollments for the course
        const enrollmentsRes = await api.get(`/courses/${courseId}/enrollments`);
        const enrollments = enrollmentsRes.data || [];
        
        // Calculate grading progress for each assessment using the marks API
        const assessmentsWithProgress = await Promise.all(assessments.map(async (assessment) => {
          let gradedCount = 0;
          
          // Get all marks for this course to count graded assessments
          try {
            const marksRes = await api.get(`/courses/${courseId}/marks`);
            const assessmentMarks = marksRes.data.assessment_marks || [];
            
            // Count how many students have marks for this specific assessment
            gradedCount = assessmentMarks.filter(mark => 
              mark.assessment_id === assessment.id && 
              mark.mark !== null && 
              mark.mark !== undefined
            ).length;
          } catch (e) {
            console.warn(`Failed to fetch marks for assessment ${assessment.id}:`, e);
            gradedCount = 0;
          }
          
          return {
            ...assessment,
            title: assessment.name,
            weight: assessment.weight,
            gradedCount: gradedCount
          };
        }));

        this.courseAssessments = assessmentsWithProgress;

        // Calculate final exam progress
        let finalExamGradedCount = 0;
        for (const enrollment of enrollments) {
          try {
            const finalMarkRes = await api.get(`/enrollments/${enrollment.id}/final-mark`);
            if (finalMarkRes.data && finalMarkRes.data.mark !== null) {
              finalExamGradedCount++;
            }
          } catch (e) {
            // Student doesn't have final exam mark yet
          }
        }
        
        this.finalExamProgress = { gradedCount: finalExamGradedCount };

      } catch (e) {
        console.error('Error fetching assessments:', e);
        this.courseAssessments = [];
        this.finalExamProgress = { gradedCount: 0 };
        this.showError('Failed to load assessments.');
      }
    },

    getProgressPercentage(course) {
      if (!course.enrolledCount) return 0;
      return Math.round((course.gradedCount / course.enrolledCount) * 100);
    },

    getAssessmentProgressPercentage(assessment) {
      if (!this.selectedCourse?.enrolledCount) return 0;
      return Math.round((assessment.gradedCount / this.selectedCourse.enrolledCount) * 100);
    },

    getProgressClass(course) {
      const percentage = this.getProgressPercentage(course);
      if (percentage === 100) return 'complete';
      if (percentage >= 50) return 'good';
      if (percentage > 0) return 'started';
      return 'pending';
    },

    async openAssessmentsModal(course) {
      this.selectedCourse = course;
      await this.fetchAssessments(course.id);
      this.showAssessmentsModal = true;
    },

    closeAssessmentsModal() {
      this.showAssessmentsModal = false;
      this.selectedCourse = null;
      this.courseAssessments = [];
      this.finalExamProgress = {};
    },

    async openStudentsModal(course, assessment = null) {
      this.selectedCourse = course;
      // Store the selected assessment for future use (assessment-specific marking)
      this.selectedAssessment = assessment;
      await this.fetchStudents(course.id);
      this.showStudentsModal = true;
    },

    closeStudentsModal() {
      this.showStudentsModal = false;
      this.selectedAssessment = null;
      this.students = [];
      this.pendingMarks = {};
      this.searchTerm = '';
      this.filterStatus = 'all'; // Reset filter
      this.currentPage = 1;
      
      // Refresh assessments data when returning to assessments modal
      if (this.showAssessmentsModal && this.selectedCourse) {
        this.fetchAssessments(this.selectedCourse.id);
      }
    },

    closeBatchModal() {
      this.showBatchModal = false;
      this.batchMarks = '';
    },

    async fetchStudents(courseId) {
      try {
        const enrollmentsRes = await api.get(`/courses/${courseId}/enrollments`);
        const enrollments = enrollmentsRes.data || [];
        
        // Check if we're handling assessment marks or final exam marks
        if (this.selectedAssessment && this.selectedAssessment.id !== 'final') {
          // Fetch assessment marks for each enrollment
          this.students = await Promise.all(enrollments.map(async (enrollment) => {
            try {
              // Get marks for this course to find the specific assessment mark
              const marksRes = await api.get(`/courses/${courseId}/students/${enrollment.student_id}/marks`);
              const assessmentMarks = marksRes.data.assessment_marks || [];
              const assessmentMark = assessmentMarks.find(mark => mark.assessment_id === this.selectedAssessment.id);
              const mark = assessmentMark ? parseFloat(assessmentMark.mark) : null;
              
              // Calculate initial weighted mark display
              let weightedDisplay;
              if (mark !== null && mark !== '') {
                const weightedMark = (mark * this.selectedAssessment.weight / 100).toFixed(1);
                weightedDisplay = `${weightedMark}% (${this.selectedAssessment.weight}%)`;
              } else {
                weightedDisplay = `0.0% (${this.selectedAssessment.weight}%)`;
              }
              
              return {
                ...enrollment,
                assessment_mark: mark,
                original_mark: mark,
                weighted_mark: weightedDisplay
              };
            } catch (e) {
              return {
                ...enrollment,
                assessment_mark: null,
                original_mark: null,
                weighted_mark: `0.0% (${this.selectedAssessment.weight}%)`
              };
            }
          }));
        } else {
          // Fetch final exam marks for each enrollment
          this.students = await Promise.all(enrollments.map(async (enrollment) => {
            try {
              const finalMarkRes = await api.get(`/enrollments/${enrollment.id}/final-mark`);
              const finalMarkData = finalMarkRes.data;
              const finalMark = finalMarkData ? parseFloat(finalMarkData.mark) : null;
              
              // Calculate initial weighted mark display for final exam
              let weightedDisplay;
              if (finalMark !== null && finalMark !== '') {
                const weightedMark = (finalMark * 0.3).toFixed(1);
                weightedDisplay = `${weightedMark}% (30%)`;
              } else {
                weightedDisplay = '0.0% (30%)';
              }
              
              return {
                ...enrollment,
                assessment_mark: finalMark,
                original_mark: finalMark,
                weighted_mark: weightedDisplay
              };
            } catch (e) {
              return {
                ...enrollment,
                assessment_mark: null,
                original_mark: null,
                weighted_mark: '0.0% (30%)'
              };
            }
          }));
        }
        
        this.pendingMarks = {};
        this.currentPage = 1;
      } catch (e) {
        this.showError('Failed to load students.');
        this.students = [];
      }
    },
    
    onMarkChange(student) {
      // Update weighted mark based on assessment type
      if (this.selectedAssessment && this.selectedAssessment.id !== 'final') {
        // For regular assessments, calculate weighted mark based on assessment weight
        if (student.assessment_mark !== null && student.assessment_mark !== '') {
          const weightedMark = (student.assessment_mark * this.selectedAssessment.weight / 100).toFixed(1);
          student.weighted_mark = `${weightedMark}% (${this.selectedAssessment.weight}%)`;
        } else {
          student.weighted_mark = `0.0% (${this.selectedAssessment.weight}%)`;
        }
      } else {
        // For final exam, apply 30% weighting
        if (student.assessment_mark !== null && student.assessment_mark !== '') {
          const weightedMark = (student.assessment_mark * 0.3).toFixed(1);
          student.weighted_mark = `${weightedMark}% (30%)`;
        } else {
          student.weighted_mark = '0.0% (30%)';
        }
      }
      
      if (student.assessment_mark !== student.original_mark) {
        this.pendingMarks[student.enrollment_id] = student.assessment_mark;
      } else {
        delete this.pendingMarks[student.enrollment_id];
      }
    },

    getMarkError(mark) {
      if (mark === null || mark === '' || mark === undefined) return '';
      if (isNaN(mark)) return 'Invalid number';
      if (mark < 0) return 'Mark cannot be negative';
      if (mark > 100) return 'Mark cannot exceed 100';
      return '';
    },

    getStatus(student) {
      if (this.pendingMarks[student.enrollment_id] !== undefined) {
        return 'Unsaved';
      }
      if (student.original_mark !== null) {
        return 'Graded';
      }
      return 'Pending';
    },

    getStatusClass(student) {
      const status = this.getStatus(student);
      return {
        'status-unsaved': status === 'Unsaved',
        'status-graded': status === 'Graded',
        'status-pending': status === 'Pending'
      };
    },

    async saveIndividualMark(student) {
      try {
        if (this.selectedAssessment && this.selectedAssessment.id !== 'final') {
          // Save assessment mark
          await api.post(`/enrollments/${student.enrollment_id}/assessment-marks`, {
            assessment_id: this.selectedAssessment.id,
            mark: student.assessment_mark
          });
        } else {
          // Save final exam mark
          await api.post(`/enrollments/${student.enrollment_id}/final-mark`, {
            mark: student.assessment_mark
          });
        }
        
        student.original_mark = student.assessment_mark;
        delete this.pendingMarks[student.enrollment_id];
        this.showSuccess('Mark saved successfully!');
        
        // Update course stats and assessments progress
        await this.fetchCourseStats(this.selectedCourse);
        if (this.showAssessmentsModal) {
          await this.fetchAssessments(this.selectedCourse.id);
        }
        
      } catch (e) {
        this.showError('Failed to save mark.');
      }
    },

    async submitAllMarks() {
      if (Object.keys(this.pendingMarks).length === 0) return;

      try {
        const promises = Object.entries(this.pendingMarks).map(([enrollmentId, mark]) => {
          if (this.selectedAssessment && this.selectedAssessment.id !== 'final') {
            // Submit assessment marks
            return api.post(`/enrollments/${enrollmentId}/assessment-marks`, {
              assessment_id: this.selectedAssessment.id,
              mark: mark
            });
          } else {
            // Submit final exam marks
            return api.post(`/enrollments/${enrollmentId}/final-mark`, { mark });
          }
        });

        await Promise.all(promises);

        // Update original marks
        this.students.forEach(student => {
          if (this.pendingMarks[student.enrollment_id] !== undefined) {
            student.original_mark = student.assessment_mark;
          }
        });

        this.pendingMarks = {};
        this.showSuccess(`${promises.length} marks submitted successfully!`);

        // Update course stats and assessments progress
        await this.fetchCourseStats(this.selectedCourse);
        if (this.showAssessmentsModal) {
          await this.fetchAssessments(this.selectedCourse.id);
        }

      } catch (e) {
        this.showError('Failed to submit some marks. Please try again.');
      }
    },

    resetMark(student) {
      student.assessment_mark = student.original_mark;
      delete this.pendingMarks[student.enrollment_id];
    },

    applyBatchMarks() {
      try {
        const marks = this.batchMarks.split(',').map(mark => {
          const trimmed = mark.trim();
          return trimmed === '' ? null : parseFloat(trimmed);
        });

        if (marks.length !== this.filteredStudents.length) {
          this.showError(`Expected ${this.filteredStudents.length} marks, got ${marks.length}`);
          return;
        }

        // Validate marks based on assessment type
        const maxMark = this.selectedAssessment && this.selectedAssessment.id !== 'final' 
          ? this.selectedAssessment.max_mark || 100 
          : 100;

        marks.forEach((mark, index) => {
          if (mark !== null && (isNaN(mark) || mark < 0 || mark > maxMark)) {
            throw new Error(`Invalid mark at position ${index + 1}: ${mark}. Must be between 0 and ${maxMark}`);
          }
        });

        this.filteredStudents.forEach((student, index) => {
          student.assessment_mark = marks[index];
          this.onMarkChange(student);
        });

        this.closeBatchModal();
        this.showSuccess('Batch marks applied successfully!');

      } catch (e) {
        this.showError(e.message || 'Failed to apply batch marks.');
      }
    },

    // Modal methods
    openUploadModal(course) {
      this.selectedCourseForModal = course;
      this.showBulkUploadModal = true;
    },

    closeBulkUploadModal() {
      this.showBulkUploadModal = false;
      this.selectedCourseForModal = null;
    },

    onUploadComplete(results) {
      this.showSuccess(`Upload completed! ${results.successful} marks uploaded successfully.`);
      
      // Refresh course stats after upload
      if (this.selectedCourseForModal) {
        this.fetchCourseStats(this.selectedCourseForModal);
      }
      
      // If assessments modal is open, refresh that too
      if (this.showAssessmentsModal && this.selectedCourse) {
        this.fetchAssessments(this.selectedCourse.id);
      }
      
      this.closeBulkUploadModal();
    },

    openExportModal(course) {
      this.selectedCourseForModal = course;
      this.showExportModal = true;
    },

    closeExportModal() {
      this.showExportModal = false;
      this.selectedCourseForModal = null;
    },

    onExportComplete(results) {
      this.showSuccess(`Export completed! ${results.recordCount} records exported as ${results.format.toUpperCase()}.`);
      this.closeExportModal();
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
  
  mounted() {
    this.fetchLecturerCourses();
  },
  watch: {
    searchTerm() {
      this.currentPage = 1;
    },
    filterStatus() {
      this.currentPage = 1;
    }
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

.enrollment-cell .enrollment-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.enrollment-count {
  font-size: 20px;
  font-weight: 700;
  color: #1D3557;
}

.enrollment-label {
  font-size: 12px;
  color: #6c757d;
}

.progress-cell .progress-display {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.progress-stats {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.progress-text {
  font-size: 12px;
  color: #6c757d;
}

.progress-percentage {
  font-size: 12px;
  font-weight: 600;
  color: #1D3557;
}

.progress-bar-small {
  height: 6px;
  background: #E5E5E5;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill-small {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 3px;
}

.progress-fill-small.pending {
  background: #dee2e6;
}

.progress-fill-small.started {
  background: #F4A261;
}

.progress-fill-small.good {
  background: #457B9D;
}

.progress-fill-small.complete {
  background: #27ae60;
}

/* Action Button */
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

/* Secondary Action Buttons */
.action-buttons-secondary {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.action-btn-small {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 12px;
  transition: all 0.2s ease;
  border: none;
}

.action-btn-small.upload {
  background: #E8F4FD;
  color: #1976D2;
}

.action-btn-small.upload:hover {
  background: #D1E9F9;
}

.action-btn-small.download {
  background: #E8F5E8;
  color: #388E3C;
}

.action-btn-small.download:hover {
  background: #D1F0D1;
}

.btn-icon-small {
  width: 14px;
  height: 14px;
}

.save-btn {
  background: #27ae60;
  color: white;
}

.save-btn:hover {
  background: #219a52;
  transform: translateY(-1px);
}

.reset-btn {
  background: #6c757d;
  color: white;
}

.reset-btn:hover {
  background: #5a6268;
  transform: translateY(-1px);
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

.students-modal {
  width: 1000px;
  max-width: 95vw;
}

.assessments-modal {
  width: 800px;
  max-width: 95vw;
}

.batch-modal {
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

/* Instructions Section */
.instructions-section {
  background: linear-gradient(135deg, #E3F2FD 0%, #F1FAEE 100%);
  border-left: 4px solid #457B9D;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
}

.instruction-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.instruction-header h5 {
  margin: 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.info-icon {
  width: 20px;
  height: 20px;
  color: #457B9D;
}

.instruction-list {
  margin: 0;
  padding-left: 20px;
}

.instruction-list li {
  margin: 6px 0;
  color: #1D3557;
  font-size: 14px;
}

/* Modal Controls */
.modal-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-filter-container {
  display: flex;
  gap: 12px;
  align-items: center;
  flex: 1;
  min-width: 0;
}

.search-container {
  position: relative;
  flex: 1;
  min-width: 200px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #6c757d;
  pointer-events: none;
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
  height: 38px; /* Set explicit height */
  box-sizing: border-box;
  line-height: 1.2;
}

.search-input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.search-input::placeholder {
  color: #adb5bd;
}

.filter-container {
  flex-shrink: 0;
}

.filter-dropdown {
  padding: 10px 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #495057;
  transition: all 0.2s ease;
  min-width: 120px;
  cursor: pointer;
  height: 38px; /* Match search input height exactly */
  box-sizing: border-box;
  line-height: 1.2;
}

.filter-dropdown:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.filter-dropdown:hover {
  border-color: #457B9D;
}

.action-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.control-btn {
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
  white-space: nowrap;
}

.control-btn.primary {
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.control-btn.primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.4);
}

.control-btn.secondary {
  background: #f8f9fa;
  color: #495057;
  border: 1px solid #e1e5e9;
}

.control-btn.secondary:hover:not(:disabled) {
  background: #e9ecef;
  border-color: #adb5bd;
  transform: translateY(-1px);
}

.control-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .modal-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .search-filter-container {
    width: 100%;
    flex-wrap: nowrap; /* Keep items inline on tablet */
  }
  
  .search-container {
    flex: 1;
    min-width: 0;
  }
  
  .action-controls {
    justify-content: flex-end;
  }
}

@media (max-width: 768px) {
  .search-filter-container {
    flex-direction: row; /* Keep inline on mobile too */
    gap: 12px;
    align-items: center;
  }
  
  .search-container {
    flex: 1;
    min-width: 0;
  }
  
  .filter-container {
    flex-shrink: 0;
  }
  
  .action-controls {
    justify-content: space-between;
  }
  
  .control-btn {
    flex: 1;
  }
}

@media (max-width: 480px) {
  .search-filter-container {
    flex-direction: column; /* Stack only on very small screens */
    gap: 12px;
    align-items: stretch;
  }
  
  .search-container {
    width: 100%;
    min-width: auto;
  }
  
  .filter-container {
    width: 100%;
  }
  
  .filter-dropdown {
    width: 100%;
  }
}

/* Progress Section */
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
  font-size: 14px;
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

/* Marks Table */
.table-wrapper {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #f1f3f4;
}

.marks-table {
  width: 100%;
  border-collapse: collapse;
}

.marks-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.marks-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.marks-table tr.has-changes {
  background: rgba(52, 152, 219, 0.05);
}

.marks-table tbody tr:hover {
  background: #f8f9fa;
}

.matric-badge {
  background: #e8f4f8;
  color: #457B9D;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.mark-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
}

.mark-input {
  width: 80px;
  padding: 8px;
  border: 1px solid #e1e5e9;
  border-radius: 6px;
  text-align: center;
  font-size: 14px;
  transition: all 0.2s ease;
}

.mark-input:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
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
  white-space: nowrap;
}

.weighted-mark {
  font-weight: 600;
  color: #1D3557;
}

/* Status Badges */
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

/* Action Buttons in Table */
.action-buttons {
  display: flex;
  gap: 6px;
}

.save-btn, .reset-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

/* Empty States in Modal */
.empty-state-modal, .no-results {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.empty-state-modal .empty-icon, .no-results .search-icon-large {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #dee2e6;
}

.empty-state-modal h4, .no-results h4 {
  margin: 0 0 8px 0;
  color: #495057;
  font-size: 16px;
}

.empty-state-modal p, .no-results p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

/* Batch Modal Styles */
.batch-description {
  color: #6c757d;
  margin-bottom: 20px;
  line-height: 1.5;
}

.batch-students {
  margin: 20px 0;
}

.batch-students h5 {
  margin: 0 0 12px 0;
  color: #1D3557;
  font-size: 14px;
  font-weight: 600;
}

.student-list-container {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  padding: 12px;
  background: #f8f9fa;
}

.student-list {
  margin: 0;
  padding-left: 20px;
  font-size: 12px;
  color: #495057;
}

.student-list li {
  margin: 4px 0;
  line-height: 1.4;
}

.batch-input {
  margin: 20px 0;
}

.batch-input label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.batch-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-family: 'Courier New', monospace;
  font-size: 14px;
  resize: vertical;
  transition: all 0.2s ease;
}

.batch-textarea:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.batch-input small {
  display: block;
  margin-top: 6px;
  color: #6c757d;
  font-size: 12px;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f3f4;
}

.apply-btn {
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

.apply-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

.apply-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

.cancel-btn {
  background: #f8f9fa;
  color: #6c757d;
  border: 1px solid #e1e5e9;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: #e9ecef;
  border-color: #adb5bd;
  color: #495057;
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

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    margin: 20px;
    padding: 20px;
    max-height: calc(100vh - 40px);
  }
  
  .students-modal {
    width: auto;
  }
  
  .modal-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-filter-container {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-container {
    max-width: none;
  }
  
  .action-controls {
    justify-content: space-between;
  }
  
  .control-btn {
    flex: 1;
  }
  
  .courses-table {
    font-size: 14px;
  }
  
  .courses-table th,
  .courses-table td {
    padding: 12px 8px;
  }
  
  .action-btn {
    padding: 8px 12px;
    font-size: 12px;
  }
  
  .toast {
    bottom: 16px;
    right: 16px;
    left: 16px;
    max-width: none;
  }
}

@media (max-width: 480px) {
  .modal-content {
    margin: 10px;
    padding: 16px;
    max-height: calc(100vh - 20px);
  }
  
  .modal-header h4 {
    font-size: 18px;
  }
  
  .batch-modal {
    width: auto;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .apply-btn,
  .cancel-btn {
    width: 100%;
    justify-content: center;
  }
}

/* Assessments Table */
.assessments-table {
  width: 100%;
  border-collapse: collapse;
}

.assessments-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.assessments-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.assessment-row:hover {
  background: #f8f9fa;
}

.final-exam-row {
  background: rgba(29, 53, 87, 0.05);
}

.final-exam-row:hover {
  background: rgba(29, 53, 87, 0.1);
}

.assessment-type-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.assessment-type-badge.type-assignment {
  background: #E8F4FD;
  color: #1976D2;
}

.assessment-type-badge.type-quiz {
  background: #FFF3E0;
  color: #F57C00;
}

.assessment-type-badge.type-project {
  background: #E8F5E8;
  color: #388E3C;
}

.assessment-type-badge.final-exam {
  background: #F3E5F5;
  color: #7B1FA2;
}

.assessment-title {
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.weight-text {
  font-weight: 600;
  color: #457B9D;
  font-size: 14px;
}

.due-date {
  color: #6c757d;
  font-size: 13px;
}
</style>
