<template>
  <div class="advisee-overall-performance">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading student performance data...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-container">
      <div class="error-message">
        <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ error }}
      </div>
      <button @click="fetchPerformanceData" class="retry-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Retry
      </button>
    </div>

    <!-- Main Content -->
    <div v-if="!loading && !error && performanceData" class="main-content">
      <!-- Header Section -->
      <div class="performance-header">
        <div class="back-navigation">
          <button @click="goBack" class="back-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Advisee List
          </button>
        </div>
        
        <div class="student-header">
          <div class="student-info">
            <div class="student-avatar">
              <span>{{ getInitials(safeStudent.name) }}</span>
            </div>
            <div class="student-details">              <h1>{{ safeStudent.name }}</h1>
              <p class="student-id">{{ safeStudent.studentId }}</p>
              <p class="student-program">{{ safeStudent.program }} - Year {{ safeStudent.year }}</p>
            </div>
          </div>
          
          <div class="quick-actions">
            <button @click="scheduleMeeting" class="action-btn primary">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              Schedule Meeting
            </button>
            <button @click="exportReport" class="action-btn secondary">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export PDF Report
            </button>
          </div>
        </div>
      </div>

      <!-- Performance Overview Cards -->
      <div class="overview-section">
        <div class="overview-grid">
          <div class="overview-card gpa-card">
            <div class="card-header">
              <h3>Overall CGPA</h3>
              <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div class="card-value">{{ (parseFloat(performanceData.gpa) || 0).toFixed(2) }}</div>
            <div class="card-status" :class="getGpaStatusClass(performanceData.gpa)">
              {{ getGpaStatus(performanceData.gpa) }}
            </div>
          </div>

          <div class="overview-card">
            <div class="card-header">
              <h3>Total Courses</h3>
              <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            <div class="card-value">{{ safeStatistics.total_courses || 0 }}</div>
            <div class="card-subtitle">Enrolled Courses</div>
          </div>

          <div class="overview-card">
            <div class="card-header">
              <h3>Credit Hours</h3>
              <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="card-value">{{ safeStatistics.total_credit_hours || 0 }}</div>
            <div class="card-subtitle">Total Credits</div>
          </div>

          <div class="overview-card">
            <div class="card-header">
              <h3>Average Score</h3>
              <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
              </svg>
            </div>
            <div class="card-value">{{ (parseFloat(safeStatistics.average_marks) || 0).toFixed(1) }}%</div>
            <div class="card-subtitle">Across All Courses</div>
          </div>
        </div>      </div>

      <!-- Course Selection for Comparison -->
      <div class="comparison-section" v-if="safeCourses.length > 0">
        <div class="comparison-header">
          <h3>Course Performance Analysis</h3>
          <div class="course-selector">
            <div class="custom-dropdown">
              <select v-model="selectedCourseForComparison" @change="fetchComparisonData" class="course-select">
                <option value="">Select Course for Detailed Analysis</option>
                <option v-for="course in safeCourses" :key="course.course_code" :value="course.course_code">
                  {{ course.course_code }} - {{ course.course_name }}
                </option>
              </select>
              <div class="dropdown-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="arrow-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Ranking Section -->
        <div v-if="selectedCourseForComparison && rankingData" class="ranking-section">
          <div class="analysis-card">
            <div class="card-header">
              <h4>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="header-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Class Ranking - {{ rankingData.course_name }}
              </h4>
            </div>
            <div class="ranking-content">
              <div class="ranking-summary">
                <div class="rank-card current-student">
                  <div class="rank-number">{{ rankingData.current_rank || 'N/A' }}</div>
                  <div class="rank-label">Student Rank</div>
                  <div class="rank-total">out of {{ rankingData.total_students || 0 }} students</div>
                </div>
                <div class="rank-stats">
                  <div class="stat-item">
                    <span class="stat-label">Status:</span>
                    <span class="stat-value" :class="getRankStatusClass(rankingData.current_rank, rankingData.total_students)">
                      {{ getRankStatus(rankingData.current_rank, rankingData.total_students) }}
                    </span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-label">Score:</span>
                    <span class="stat-value">{{ (rankingData.your_score || 0).toFixed(1) }}%</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-label">Grade:</span>
                    <span class="stat-value grade-badge" :class="getGradeClass(rankingData.your_grade)">
                      {{ rankingData.your_grade || 'N/A' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Score Distribution Chart -->
              <div v-if="rankingData.distribution && rankingData.distribution.length > 0" class="distribution-section">
                <h5>Score Distribution</h5>
                <div class="distribution-chart">
                  <div v-for="(count, index) in rankingData.distribution" :key="index" class="distribution-bar">
                    <div class="bar-container">
                      <div class="bar" :style="{ height: getDistributionHeight(count, rankingData.distribution) + '%' }"></div>
                    </div>
                    <div class="bar-label">{{ getDistributionLabel(index) }}</div>
                    <div class="bar-count">{{ count }}</div>
                  </div>
                </div>
              </div>

              <!-- Top Students (if available and not empty) -->
              <div v-if="rankingData.top_students && rankingData.top_students.length > 0" class="top-students-section">
                <h5>Class Leaderboard (Top 10)</h5>
                <div class="students-list">
                  <div 
                    v-for="student in rankingData.top_students.slice(0, 10)" 
                    :key="student.rank" 
                    class="student-rank-item"
                    :class="{ 'current-student': student.isYou }"
                  >
                    <div class="rank-position">
                      <span class="rank-badge" :class="getRankBadgeClass(student.rank)">
                        {{ student.rank }}
                      </span>
                    </div>
                    <div class="student-info">
                      <div class="student-name">
                        {{ student.isYou ? safeStudent.name + ' (You)' : 'Student ' + student.anonymousId }}
                      </div>
                      <div class="student-details">
                        Score: {{ (student.score || 0).toFixed(1) }}% | Grade: 
                        <span class="grade-badge" :class="getGradeClass(student.grade)">{{ student.grade }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Comparison Section -->
        <div v-if="selectedCourseForComparison && comparisonData" class="comparison-analysis">
          <div class="analysis-card">
            <div class="card-header">
              <h4>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="header-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Class Average Comparison
              </h4>
            </div>
            <div class="comparison-content">
              <div class="comparison-grid">
                <div v-for="component in comparisonData.components" :key="component.component_name" class="comparison-item">
                  <div class="component-header">
                    <h5>{{ component.component_name }}</h5>
                    <div class="weight-badge">{{ component.weight }}%</div>
                  </div>
                  <div class="comparison-bars">
                    <div class="bar-container">
                      <div class="bar-label">Student</div>
                      <div class="progress-bar">
                        <div 
                          class="progress-fill student-bar" 
                          :style="{ width: getPercentageWidth(component.student_score, component.max_marks) + '%' }"
                        ></div>
                      </div>
                      <div class="bar-value">{{ (parseFloat(component.student_score) || 0).toFixed(1) }}/{{ component.max_marks }}</div>
                    </div>
                    <div class="bar-container">
                      <div class="bar-label">Class Avg</div>
                      <div class="progress-bar">
                        <div 
                          class="progress-fill class-avg-bar" 
                          :style="{ width: getPercentageWidth(component.class_average, component.max_marks) + '%' }"
                        ></div>
                      </div>
                      <div class="bar-value">{{ (parseFloat(component.class_average) || 0).toFixed(1) }}/{{ component.max_marks }}</div>
                    </div>
                  </div>
                  <div class="performance-indicator" :class="getComparisonClass(component.student_score, component.class_average)">
                    {{ getComparisonText(component.student_score, component.class_average) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading State for Comparison -->
        <div v-if="selectedCourseForComparison && comparisonLoading" class="comparison-loading">
          <div class="loading-spinner"></div>
          <p>Loading comparison data...</p>
        </div>

        <!-- Error State for Comparison -->
        <div v-if="selectedCourseForComparison && error && !comparisonLoading" class="comparison-error">
          <div class="error-message">
            <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ error }}
          </div>
          <button @click="fetchComparisonData" class="retry-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Retry
          </button>
        </div>

        <!-- No Data State -->
        <div v-if="selectedCourseForComparison && !comparisonLoading && !error && !comparisonData && !rankingData" class="no-comparison-data">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h4>No comparison data available</h4>
          <p>Comparison data is not available for this course yet.</p>
        </div>
      </div>

      <!-- Detailed Course Performance -->
      <div class="courses-section">
        <div class="section-header">
          <h3>Course-by-Course Performance</h3>
          <div class="view-controls">
            <button 
              @click="viewMode = 'cards'" 
              :class="{ active: viewMode === 'cards' }"
              class="view-btn"
            >
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
              Cards
            </button>
            <button 
              @click="viewMode = 'table'" 
              :class="{ active: viewMode === 'table' }"
              class="view-btn"
            >
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
              Table
            </button>
          </div>
        </div>

        <!-- Cards View -->
        <div v-if="viewMode === 'cards'" class="courses-grid">
          <div 
            v-for="course in safeCourses" 
            :key="course.course_code"
            class="course-card"
            @click="viewCourseDetails(course)"
          >
            <div class="course-header">
              <div class="course-info">
                <h4>{{ course.course_code }}</h4>
                <p>{{ course.course_name }}</p>
              </div>
              <div class="course-grade" :class="getGradeClass(course.grade)">
                {{ course.grade }}
              </div>
            </div>
            
            <div class="course-metrics">
              <div class="metric">
                <span class="metric-label">Total Score</span>
                <span class="metric-value">{{ (parseFloat(course.total_marks) || 0).toFixed(1) }}%</span>
              </div>
              <div class="metric">
                <span class="metric-label">Final Exam</span>
                <span class="metric-value">{{ (parseFloat(course.final_exam_marks) || 0).toFixed(1) }}%</span>
              </div>
              <div class="metric">
                <span class="metric-label">Credits</span>
                <span class="metric-value">{{ course.credit_hours }}</span>
              </div>
            </div>

            <div class="course-progress">
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: (course.total_marks || 0) + '%' }"
                  :class="getPerformanceClass(course.total_marks)"
                ></div>
              </div>
              <span class="progress-text">{{ getPerformanceLabel(course.total_marks) }}</span>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-if="viewMode === 'table'" class="courses-table-container">
          <table class="courses-table">
            <thead>
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Final Exam</th>
                <th>Total Score</th>
                <th>Grade</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="course in safeCourses" :key="course.course_code" class="course-row">
                <td class="course-code">{{ course.course_code }}</td>
                <td class="course-name">{{ course.course_name }}</td>
                <td class="credits">{{ course.credit_hours }}</td>
                <td class="final-exam">{{ (parseFloat(course.final_exam_marks) || 0).toFixed(1) }}%</td>
                <td class="total-score">
                  <span :class="getPerformanceClass(course.total_marks)">
                    {{ (parseFloat(course.total_marks) || 0).toFixed(1) }}%
                  </span>
                </td>
                <td class="grade">
                  <span :class="getGradeClass(course.grade)">{{ course.grade }}</span>
                </td>
                <td class="status">
                  <span class="status-badge" :class="getStatusClass(course.total_marks)">
                    {{ getStatusLabel(course.total_marks) }}
                  </span>
                </td>
                <td class="actions">
                  <button @click="viewCourseDetails(course)" class="action-btn-small">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Assessment Breakdown Section -->
      <div v-if="selectedCourse" class="assessment-section">
        <div class="section-header">
          <h3>Assessment Breakdown - {{ selectedCourse.course_code }}</h3>
          <button @click="selectedCourse = null" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="assessment-grid" v-if="getAssessmentsForCourse(selectedCourse.course_code).length > 0">
          <div 
            v-for="assessment in getAssessmentsForCourse(selectedCourse.course_code)" 
            :key="assessment.assessment_name"
            class="assessment-card"
          >
            <div class="assessment-header">
              <h4>{{ assessment.assessment_name }}</h4>
              <span class="assessment-type">{{ assessment.assessment_type }}</span>
            </div>
            <div class="assessment-details">
              <div class="score">
                <span class="score-value">{{ assessment.marks_obtained || 0 }}</span>
                <span class="score-max">/ {{ assessment.max_marks }}</span>
              </div>
              <div class="percentage">{{ getAssessmentPercentage(assessment) }}%</div>
              <div class="weight">Weight: {{ assessment.weightage }}%</div>
            </div>
          </div>
        </div>

        <div v-else class="no-assessments">
          <p>No detailed assessment breakdown available for this course.</p>
        </div>
      </div>

      <!-- Recent Meetings Section -->
      <div v-if="performanceData.recent_meetings?.length > 0" class="meetings-section">
        <div class="section-header">
          <h3>Recent Meetings & Notes</h3>
          <button @click="goToMeetingNotes" class="view-all-btn">
            View All Meetings
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
          </button>
        </div>

        <div class="meetings-list">
          <div 
            v-for="meeting in performanceData.recent_meetings.slice(0, 3)" 
            :key="meeting.id || meeting.meeting_date"
            class="meeting-item"
          >
            <div class="meeting-date">
              <div class="date">{{ formatDate(meeting.meeting_date) }}</div>
              <div class="time">{{ meeting.meeting_time }}</div>
            </div>
            <div class="meeting-content">
              <div class="meeting-type">{{ meeting.meeting_type || 'Academic' }}</div>
              <div class="meeting-notes">{{ meeting.notes || 'No notes available' }}</div>
            </div>
            <div class="meeting-status" :class="meeting.status">
              {{ meeting.status }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { advisorService } from '../../services/advisor.js';
import { auth } from '../../utils/auth.js';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import api from '../../api.js';

export default {
  name: 'AdviseeOverallPerformance',
  props: {
    studentId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      currentUser: null,
      performanceData: null,
      loading: false,      error: null,
      viewMode: 'cards', // 'cards' or 'table'
      selectedCourse: null,
      selectedCourseForComparison: '',
      comparisonData: null,
      rankingData: null,
      comparisonLoading: false
    };  },
  computed: {
    safeCourses() {
      return (this.performanceData && this.performanceData.courses) 
        ? this.performanceData.courses 
        : [];
    },
    safeStudent() {
      return (this.performanceData && this.performanceData.student) 
        ? this.performanceData.student 
        : { name: 'Unknown', studentId: 'N/A', program: 'N/A', year: 'N/A' };
    },
    safeStatistics() {
      return (this.performanceData && this.performanceData.statistics) 
        ? this.performanceData.statistics 
        : { average_marks: 0, total_credit_hours: 0 };
    }
  },
  async created() {
    this.currentUser = auth.getCurrentUser();
    
    if (!this.currentUser || !auth.isAdvisor()) {
      this.error = 'Access denied. Advisor privileges required.';
      return;
    }

    await this.fetchPerformanceData();
  },
  methods: {
    async fetchPerformanceData() {
      if (!this.currentUser || !this.currentUser.id) {
        this.error = 'User not authenticated';
        return;
      }

      this.loading = true;
      this.error = null;
      
      try {
        const response = await advisorService.getAdviseePerformance(
          this.currentUser.id, 
          this.studentId
        );
        
        if (response.success) {
          this.performanceData = response.data;
          console.log('Performance data received:', this.performanceData);
        } else {
          this.error = response.message || 'Failed to fetch performance data';
        }
      } catch (error) {
        console.error('Error fetching performance data:', error);
        this.error = 'Failed to load performance data. Please try again.';
      } finally {
        this.loading = false;
      }
    },

    getInitials(name) {
      if (!name) return 'N/A';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },

    getGpaStatus(gpa) {
      if (gpa >= 3.5) return 'Excellent';
      if (gpa >= 3.0) return 'Good';
      if (gpa >= 2.0) return 'Satisfactory';
      return 'Needs Improvement';
    },

    getGpaStatusClass(gpa) {
      if (gpa >= 3.5) return 'excellent';
      if (gpa >= 3.0) return 'good';
      if (gpa >= 2.0) return 'satisfactory';
      return 'poor';
    },    getPerformanceClass(score) {
      const numScore = parseFloat(score) || 0;
      if (numScore >= 80) return 'excellent';
      if (numScore >= 70) return 'good';
      if (numScore >= 60) return 'satisfactory';
      return 'poor';
    },    getPerformanceLabel(score) {
      const numScore = parseFloat(score) || 0;
      if (numScore >= 80) return 'Excellent';
      if (numScore >= 70) return 'Good';
      if (numScore >= 60) return 'Satisfactory';
      if (numScore > 0) return 'Needs Improvement';
      return 'No Data';
    },

    getGradeClass(grade) {
      if (!grade) return 'grade-f';
      const gradeUpper = grade.toUpperCase();
      if (['A+', 'A'].includes(gradeUpper)) return 'grade-a';
      if (['A-', 'B+'].includes(gradeUpper)) return 'grade-b-plus';
      if (['B', 'B-'].includes(gradeUpper)) return 'grade-b';
      if (['C+', 'C'].includes(gradeUpper)) return 'grade-c';
      return 'grade-f';
    },

    getStatusClass(score) {
      if (!score) return 'status-missing';
      if (score >= 80) return 'status-excellent';
      if (score >= 70) return 'status-good';
      if (score >= 60) return 'status-satisfactory';
      return 'status-poor';
    },

    getStatusLabel(score) {
      if (!score) return 'Missing';
      if (score >= 80) return 'Excellent';
      if (score >= 70) return 'Good';
      if (score >= 60) return 'Pass';
      return 'At Risk';
    },

    getAssessmentsForCourse(courseCode) {
      if (!this.performanceData?.assessments) return [];
      return this.performanceData.assessments[courseCode] || [];
    },

    getAssessmentPercentage(assessment) {
      if (!assessment.max_marks || assessment.max_marks === 0) return 0;
      const marksPercentage = (parseFloat(assessment.marks_obtained) || 0) / parseFloat(assessment.max_marks);
      const weightedPercentage = marksPercentage * (parseFloat(assessment.weightage) || 0);
      return weightedPercentage.toFixed(1);
    },

    viewCourseDetails(course) {
      this.selectedCourse = course;
      // Scroll to assessment section
      this.$nextTick(() => {
        const element = document.querySelector('.assessment-section');
        if (element) {
          element.scrollIntoView({ behavior: 'smooth' });
        }
      });
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        return new Date(dateString).toLocaleDateString();
      } catch (e) {
        return dateString;
      }
    },

    goBack() {
      this.$router.push('/advisor/advisee-list');
    },

    goToMeetingNotes() {
      this.$router.push('/advisor/meeting-notes');
    },

    scheduleMeeting() {
      // Navigate to meeting notes with scheduling modal
      this.$router.push({
        path: '/advisor/meeting-notes',
        query: { schedule: this.studentId }
      });
    },

    exportReport() {
      // Generate and download performance report
      this.generatePerformanceReport();
    },    generatePerformanceReport() {
      if (!this.performanceData) return;

      const student = this.safeStudent;
      const courses = this.safeCourses;
      
      // Create new PDF document
      const doc = new jsPDF();
      
      // Add title
      doc.setFontSize(20);
      doc.setTextColor(29, 53, 87); // #1D3557
      doc.text('Student Performance Report', 20, 25);
      
      // Add student information
      doc.setFontSize(12);
      doc.setTextColor(0, 0, 0);
      doc.text(`Student Name: ${student.name}`, 20, 45);
      doc.text(`Student ID: ${student.studentId}`, 20, 55);
      doc.text(`Program: ${student.program}`, 20, 65);
      doc.text(`Overall CGPA: ${(parseFloat(this.performanceData.gpa) || 0).toFixed(2)}`, 20, 75);
      
      // Add generation date
      const currentDate = new Date().toLocaleDateString();
      doc.text(`Report Generated: ${currentDate}`, 20, 85);
      
      // Prepare table data
      const tableData = courses.map(course => [
        course.course_code || 'N/A',
        course.course_name || 'N/A',
        course.credit_hours || 0,
        `${(parseFloat(course.total_marks) || 0).toFixed(1)}%`,
        `${(parseFloat(course.final_exam_marks) || 0).toFixed(1)}%`,
        course.grade || 'N/A'
      ]);
      
      // Add course performance table using autoTable
      autoTable(doc, {
        head: [['Course Code', 'Course Name', 'Credits', 'Total Score', 'Final Exam', 'Grade']],
        body: tableData,
        startY: 100,
        theme: 'grid',
        headStyles: {
          fillColor: [69, 123, 157], // #457B9D
          textColor: 255,
          fontStyle: 'bold'
        },
        bodyStyles: {
          textColor: 50
        },
        alternateRowStyles: {
          fillColor: [248, 250, 252] // #f8fafc
        },
        margin: { top: 100, left: 20, right: 20 },
        columnStyles: {
          0: { cellWidth: 25 },
          1: { cellWidth: 60 },
          2: { cellWidth: 20 },
          3: { cellWidth: 25 },
          4: { cellWidth: 25 },
          5: { cellWidth: 20 }
        }
      });
      
      // Add summary statistics if available
      if (this.safeStatistics) {
        const finalY = doc.lastAutoTable.finalY + 20;
        doc.setFontSize(14);
        doc.setTextColor(29, 53, 87);
        doc.text('Summary Statistics', 20, finalY);
        
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text(`Total Courses: ${this.safeStatistics.total_courses || courses.length}`, 20, finalY + 15);
        doc.text(`Total Credit Hours: ${this.safeStatistics.total_credit_hours || 0}`, 20, finalY + 25);
        doc.text(`Average Score: ${(parseFloat(this.safeStatistics.average_marks) || 0).toFixed(1)}%`, 20, finalY + 35);
      }
      
      // Add footer
      const pageCount = doc.internal.getNumberOfPages();
      doc.setFontSize(8);
      doc.setTextColor(128, 128, 128);
      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.text(`Page ${i} of ${pageCount}`, doc.internal.pageSize.width - 40, doc.internal.pageSize.height - 10);
        doc.text('Generated by Course Mark Management System', 20, doc.internal.pageSize.height - 10);
      }
        // Save the PDF
      doc.save(`${student.name}_Performance_Report.pdf`);
    },

    async fetchComparisonData() {
      if (!this.selectedCourseForComparison || !this.currentUser) return;

      this.comparisonLoading = true;
      this.comparisonData = null;
      this.rankingData = null;
      this.error = null;

      try {
        // Get the course ID for the selected course
        const selectedCourse = this.safeCourses.find(course => course.course_code === this.selectedCourseForComparison);
        if (!selectedCourse) {
          console.error('Selected course not found:', this.selectedCourseForComparison);
          return;
        }

        console.log('Selected course object:', selectedCourse);
        console.log('Available course fields:', Object.keys(selectedCourse));

        let courseId = this.getCourseId(selectedCourse);
        
        // If no direct course ID found, try to get it from the student courses endpoint
        if (!courseId) {
          console.warn('No course ID found, attempting to fetch student courses to get proper IDs');
          
          try {
            // Use the existing student courses endpoint with student ID
            const studentCoursesResponse = await api.get(`/student/courses/${this.studentId}`);
            if (studentCoursesResponse.data && studentCoursesResponse.data.success && studentCoursesResponse.data.courses) {
              const courseWithId = studentCoursesResponse.data.courses.find(course => 
                course.code === selectedCourse.course_code || course.course_code === selectedCourse.course_code
              );
              
              if (courseWithId) {
                courseId = courseWithId.id || courseWithId.course_id;
                console.log('Found course ID from student courses:', courseId);
              }
            }
          } catch (apiError) {
            console.error('Could not fetch student courses:', apiError);
            console.error('API Error details:', {
              status: apiError.response?.status,
              statusText: apiError.response?.statusText,
              data: apiError.response?.data,
              url: apiError.config?.url
            });
            
            // Try the general courses endpoint as fallback
            try {
              console.log('Trying fallback: fetching all courses');
              const allCoursesResponse = await api.get('/student/courses');
              if (allCoursesResponse.data && allCoursesResponse.data.success && allCoursesResponse.data.courses) {
                const courseWithId = allCoursesResponse.data.courses.find(course => 
                  course.code === selectedCourse.course_code || course.course_code === selectedCourse.course_code
                );
                
                if (courseWithId) {
                  courseId = courseWithId.id;
                  console.log('Found course ID from all courses:', courseId);
                }
              }
            } catch (fallbackError) {
              console.error('Fallback also failed:', fallbackError);
              this.error = `Failed to load course data. Server returned: ${apiError.response?.status || 'Network Error'}`;
              return;
            }
          }
        }

        if (!courseId) {
          console.error('Cannot proceed without course ID. Available course data:', selectedCourse);
          this.error = `Unable to load ranking data for ${selectedCourse.course_code}. Course ID not found.`;
          return;
        }

        await this.fetchComparisonWithCourseId(courseId, selectedCourse);
        
      } catch (error) {
        console.error('Error in fetchComparisonData:', error);
        this.error = 'Failed to load course ranking data. Please try again.';
      } finally {
        this.comparisonLoading = false;
      }
    },

    async fetchComparisonWithCourseId(courseId, selectedCourse) {
      console.log('Fetching comparison data for:', { studentId: this.studentId, courseId, selectedCourse });

      // Clear any previous errors
      this.error = null;

      // Fetch comparison and ranking data using the student API endpoints
      const comparisonPromise = api.student.getComparison(this.studentId, courseId);
      const rankingPromise = api.student.getRanking(this.studentId, courseId);

      const [comparisonResponse, rankingResponse] = await Promise.all([
        comparisonPromise.catch((error) => {
          console.error('Error fetching comparison data:', error);
          return null;
        }),
        rankingPromise.catch((error) => {
          console.error('Error fetching ranking data:', error);
          return null;
        })
      ]);

      if (comparisonResponse && comparisonResponse.data && comparisonResponse.data.success) {
        // Map the comparison response data to match the expected structure
        const rawData = comparisonResponse.data;
        this.comparisonData = {
          course_name: rawData.courseName,
          percentile: rawData.percentile,
          class_average: rawData.classAverage,
          your_score: rawData.yourScore,
          class_size: rawData.classSize,
          distribution: rawData.distribution || [0, 0, 0, 0, 0],
          // Map components to match template expectations
          components: rawData.components.map(component => ({
            component_name: component.name,
            weight: component.weight,
            student_score: component.yourScore,
            class_average: component.average,
            max_marks: 100, // Since scores are already in percentage
            difference: component.difference,
            position: component.position
          })),
          // Calculate totals for overall comparison
          student_total: rawData.yourScore,
          class_average_total: rawData.classAverage
        };
        console.log('Comparison data processed:', this.comparisonData);
      }

      if (rankingResponse && rankingResponse.data && rankingResponse.data.success) {
        // Map the ranking response data to match the expected structure
        const rankingData = rankingResponse.data;
        this.rankingData = {
          current_rank: rankingData.yourRank,
          total_students: rankingData.totalStudents,
          your_score: rankingData.yourScore,
          your_grade: rankingData.yourGrade,
          percentile: rankingData.percentile,
          course_name: rankingData.courseName,
          top_students: rankingData.topStudents || [],
          distribution: rankingData.distribution || [0, 0, 0, 0, 0]
        };
        console.log('Ranking data processed:', this.rankingData);
      }
    },

    calculatePercentile(rank, total) {
      if (!rank || !total) return 0;
      return Math.round(((total - rank + 1) / total) * 100);
    },

    getRankStatus(rank, total) {
      const percentile = this.calculatePercentile(rank, total);
      if (percentile >= 75) return 'Excellent';
      if (percentile >= 50) return 'Above Average';
      if (percentile >= 25) return 'Average';
      return 'Below Average';
    },

    getRankStatusClass(rank, total) {
      const percentile = this.calculatePercentile(rank, total);
      if (percentile >= 75) return 'excellent';
      if (percentile >= 50) return 'good';
      if (percentile >= 25) return 'average';
      return 'poor';
    },

    getPercentageWidth(score, maxMarks) {
      if (!score || !maxMarks) return 0;
      return Math.min((parseFloat(score) / parseFloat(maxMarks)) * 100, 100);
    },

    getComparisonClass(studentScore, classAverage) {
      const student = parseFloat(studentScore) || 0;
      const average = parseFloat(classAverage) || 0;
      const difference = student - average;
      
      if (difference > 5) return 'above-average';
      if (difference > 0) return 'slightly-above';
      if (difference > -5) return 'slightly-below';
      return 'below-average';
    },

    getComparisonText(studentScore, classAverage) {
      const student = parseFloat(studentScore) || 0;
      const average = parseFloat(classAverage) || 0;
      const difference = student - average;
      
      if (difference > 5) return `+${difference.toFixed(1)} above class average`;
      if (difference > 0) return `+${difference.toFixed(1)} above average`;
      if (difference > -5) return `${difference.toFixed(1)} below average`;
      return `${difference.toFixed(1)} below class average`;
    },

    getOverallComparisonClass(studentTotal, classTotal) {
      const student = parseFloat(studentTotal) || 0;
      const classAvg = parseFloat(classTotal) || 0;
      
      if (student > classAvg) return 'positive';
      if (student === classAvg) return 'neutral';
      return 'negative';
    },

    getOverallDifference(studentTotal, classTotal) {
      const student = parseFloat(studentTotal) || 0;
      const classAvg = parseFloat(classTotal) || 0;
      const difference = student - classAvg;
      
      if (difference > 0) return `+${difference.toFixed(1)}%`;
      return `${difference.toFixed(1)}%`;
    },

    // Helper methods for ranking display
    getDistributionHeight(count, distribution) {
      const maxCount = Math.max(...distribution);
      return maxCount > 0 ? (count / maxCount) * 100 : 0;
    },

    getDistributionLabel(index) {
      const ranges = ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'];
      return ranges[index] || '';
    },

    getRankBadgeClass(rank) {
      if (rank === 1) return 'gold';
      if (rank === 2) return 'silver';
      if (rank === 3) return 'bronze';
      if (rank <= 10) return 'top-ten';
      return 'regular';
    },

    // Helper method to get course ID from the selected course
    getCourseId(course) {
      // Try different possible field names for course ID
      const possibleIds = [
        course.course_id,
        course.id,
        course.courseId,
        course.course_ID,
        course.ID,
        course.cid,
        course.c_id
      ];
      
      // Find the first valid ID
      const courseId = possibleIds.find(id => id !== undefined && id !== null && id !== '');
      
      if (!courseId) {
        console.error('Course ID not found. Available fields:', Object.keys(course));
        console.error('Course object:', course);
        // Try to use course_code as a fallback - we might need to modify the API
        return null;
      }
      
      return courseId;
    },

    // Debug method to log current state
    async logDebugInfo() {
      console.log('Debug Info:', {
        studentId: this.studentId,
        selectedCourse: this.selectedCourseForComparison,
        safeCourses: this.safeCourses,
        rankingData: this.rankingData,
        comparisonData: this.comparisonData,
        comparisonLoading: this.comparisonLoading
      });

      // Test API endpoints
      console.log('Testing API endpoints...');
      
      try {
        // Test general courses endpoint
        console.log('Testing /student/courses...');
        const coursesResponse = await api.get('/student/courses');
        console.log('General courses response:', coursesResponse.data);
        
        // Test student-specific courses endpoint
        console.log(`Testing /student/courses/${this.studentId}...`);
        const studentCoursesResponse = await api.get(`/student/courses/${this.studentId}`);
        console.log('Student courses response:', studentCoursesResponse.data);
        
      } catch (error) {
        console.error('API Test Error:', error);
        console.error('Error details:', {
          status: error.response?.status,
          statusText: error.response?.statusText,
          data: error.response?.data,
          url: error.config?.url
        });
      }
    }
  }
};
</script>

<style scoped>
.advisee-overall-performance {
  min-height: 100vh;
  background: #f8fafc;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Loading and Error States */
.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
  padding: 2rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  margin-bottom: 1rem;
}

.error-icon {
  width: 20px;
  height: 20px;
}

.retry-btn, .back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.retry-btn:hover, .back-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

/* Header Section */
.performance-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1.5rem 2rem;
}

.back-navigation {
  margin-bottom: 1rem;
}

.back-btn svg {
  width: 16px;
  height: 16px;
}

.student-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.student-avatar {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
}

.student-details h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.student-id {
  margin: 0.25rem 0;
  color: #6b7280;
  font-weight: 500;
}

.student-program {
  margin: 0;
  color: #9ca3af;
  font-size: 0.875rem;
}

.quick-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  border: none;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.action-btn.secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.action-btn.secondary:hover {
  background: #e5e7eb;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

/* Overview Cards */
.overview-section {
  padding: 2rem;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.overview-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: transform 0.2s, box-shadow 0.2s;
}

.overview-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-header h3 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-icon {
  width: 20px;
  height: 20px;
  color: #9ca3af;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.gpa-card .card-value {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.card-status {
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  display: inline-block;
}

.card-status.excellent {
  background: #d1fae5;
  color: #065f46;
}

.card-status.good {
  background: #dbeafe;
  color: #1e40af;
}

.card-status.satisfactory {
  background: #fef3c7;
  color: #92400e;
}

.card-status.poor {
  background: #fee2e2;
  color: #dc2626;
}

.card-subtitle {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;  letter-spacing: 0.05em;
}

/* Courses Section */
.courses-section {
  padding: 0 2rem 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.view-controls {
  display: flex;
  gap: 0.5rem;
}

.view-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.view-btn:hover {
  background: #f9fafb;
}

.view-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.view-btn svg {
  width: 16px;
  height: 16px;
}

.view-all-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #457B9D 0%, #1D3557 100%);
  color: white;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(69, 123, 157, 0.3);
  position: relative;
  overflow: hidden;
}

.view-all-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.view-all-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 123, 157, 0.4);
  background: linear-gradient(135deg, #3d6e8d 0%, #0f2942 100%);
}

.view-all-btn:hover::before {
  left: 100%;
}

.view-all-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(69, 123, 157, 0.3);
}

.view-all-btn svg {
  width: 18px;
  height: 18px;
  transition: transform 0.3s ease;
}

.view-all-btn:hover svg {
  transform: translateX(3px);
}

/* Courses Grid View */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.course-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s;
}

.course-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1rem;
}

.course-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.course-info p {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.4;
}

.course-grade {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.875rem;
}

.course-metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1rem;
}

.metric {
  text-align: center;
}

.metric-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.metric-value {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.course-progress {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.3s;
}

.progress-fill.excellent {
  background: #10b981;
}

.progress-fill.good {
  background: #3b82f6;
}

.progress-fill.satisfactory {
  background: #f59e0b;
}

.progress-fill.poor {
  background: #ef4444;
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  white-space: nowrap;
}

/* Grade Classes */
.grade-a {
  background: #d1fae5;
  color: #065f46;
}

.grade-b-plus {
  background: #dbeafe;
  color: #1e40af;
}

.grade-b {
  background: #e0e7ff;
  color: #3730a3;
}

.grade-c {
  background: #fef3c7;
  color: #92400e;
}

.grade-f {
  background: #fee2e2;
  color: #dc2626;
}

/* Table View */
.courses-table-container {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.courses-table {
  width: 100%;
  border-collapse: collapse;
}

.courses-table th {
  background: #f9fafb;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.courses-table td {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.course-row:hover {
  background: #f9fafb;
}

.course-code {
  font-weight: 600;
  color: #3b82f6;
}

.course-name {
  max-width: 200px;
  line-height: 1.4;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-excellent {
  background: #d1fae5;
  color: #065f46;
}

.status-good {
  background: #dbeafe;
  color: #1e40af;
}

.status-satisfactory {
  background: #fef3c7;
  color: #92400e;
}

.status-poor, .status-missing {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn-small {
  padding: 0.375rem;
  background: #f3f4f6;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background 0.2s;
}

.action-btn-small:hover {
  background: #e5e7eb;
}

.action-btn-small svg {
  width: 16px;
  height: 16px;
  color: #6b7280;
}

/* Assessment Section */
.assessment-section {
  padding: 0 2rem 2rem;
}

.close-btn {
  padding: 0.5rem;
  background: #f3f4f6;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background 0.2s;
}

.close-btn:hover {
  background: #e5e7eb;
}

.close-btn svg {
  width: 16px;
  height: 16px;
  color: #6b7280;
}

.assessment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.assessment-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
}

.assessment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.assessment-header h4 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.assessment-type {
  padding: 0.125rem 0.5rem;
  background: #f3f4f6;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #6b7280;
}

.assessment-details {
  text-align: center;
}

.score {
  margin-bottom: 0.5rem;
}

.score-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
}

.score-max {
  color: #6b7280;
}

.percentage {
  font-size: 0.875rem;
  font-weight: 600;
  color: #3b82f6;
  margin-bottom: 0.25rem;
}

.weight {
  font-size: 0.75rem;
  color: #9ca3af;
}

.no-assessments {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

/* Meetings Section */
.meetings-section {
  padding: 0 2rem 2rem;
}

.meetings-list {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.meeting-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.meeting-item:last-child {
  border-bottom: none;
}

.meeting-date {
  text-align: center;
  min-width: 80px;
}

.date {
  font-weight: 600;
  color: #111827;
  font-size: 0.875rem;
}

.time {
  font-size: 0.75rem;
  color: #6b7280;
}

.meeting-content {
  flex: 1;
}

.meeting-type {
  font-weight: 600;
  color: #3b82f6;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.meeting-notes {
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.4;
}

.meeting-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.meeting-status.completed {
  background: #d1fae5;
  color: #065f46;
}

.meeting-status.scheduled {
  background: #dbeafe;
  color: #1e40af;
}

.meeting-status.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

/* Responsive Design */
@media (max-width: 768px) {
  .performance-header {
    padding: 1rem;
  }

  .student-header {
    flex-direction: column;
    align-items: stretch;
  }

  .quick-actions {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    justify-content: center;
  }

  .overview-section, .courses-section, .assessment-section, .meetings-section {
    padding: 1rem;
  }

  .overview-grid {
    grid-template-columns: 1fr;
  }

  .courses-grid {
    grid-template-columns: 1fr;
  }

  .course-metrics {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .view-controls {
    flex-direction: column;
  }

  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .courses-table-container {
    overflow-x: auto;
  }

  .meeting-item {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .meeting-date {
    text-align: left;
    min-width: auto;
  }
}

/* Ranking Section Styles */
.ranking-section {
  margin-bottom: 2rem;
}

.analysis-card {
  background: white;

  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.card-header h4 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-icon {
  width: 20px;
  height: 20px;
  color: #3b82f6;
}

.ranking-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.ranking-summary {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 2rem;
  align-items: start;
}

.rank-card {
  text-align: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 0.75rem;
  color: white;
  min-width: 150px;
}

.rank-number {
  font-size: 3rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.rank-label {
  font-size: 0.875rem;
  font-weight: 500;
  opacity: 0.9;
  margin-bottom: 0.25rem;
}

.rank-total {
  font-size: 0.75rem;
  opacity: 0.8;
}

.rank-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.stat-value.excellent {
  color: #059669;
}

.stat-value.good {
  color: #3b82f6;
}

.stat-value.average {
  color: #f59e0b;
}

.stat-value.poor {
  color: #dc2626;
}

.grade-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Distribution Chart */
.distribution-section {
  margin-top: 1.5rem;
}

.distribution-section h5 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.distribution-chart {
  display: flex;
  align-items: end;
  gap: 1rem;
  height: 150px;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.5rem;
}

.distribution-bar {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  gap: 0.5rem;
}

.bar-container {
  display: flex;
  align-items: end;
  height: 100px;
  width: 100%;
}

.bar {
  width: 100%;
  background: linear-gradient(to top, #3b82f6, #60a5fa);
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: all 0.3s ease;
}

.bar-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.bar-count {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* Top Students Section */
.top-students-section {
  margin-top: 1.5rem;
}

.top-students-section h5 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.students-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
}

.student-rank-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
}

.student-rank-item:hover {
  background: #f3f4f6;
}

.student-rank-item.current-student {
  background: #dbeafe;
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

.rank-position {
  min-width: 40px;
}

.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-weight: 700;
  font-size: 0.875rem;
}

.rank-badge.gold {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: white;
}

.rank-badge.silver {
  background: linear-gradient(135deg, #d1d5db, #9ca3af);
  color: white;
}

.rank-badge.bronze {
  background: linear-gradient(135deg, #f97316, #ea580c);
  color: white;
}

.rank-badge.top-ten {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.rank-badge.regular {
  background: #f3f4f6;
  color: #6b7280;
}

.student-info {
  flex: 1;
}

.student-name {
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
}

.student-details {
  font-size: 0.875rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Responsive Design for Ranking */
@media (max-width: 768px) {
  .ranking-summary {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .rank-stats {
    grid-template-columns: repeat(2, 1fr);
  }

  .distribution-chart {
    height: 120px;
    gap: 0.5rem;
  }

  .students-list {
    max-height: 250px;
  }

  .student-rank-item {
    padding: 0.5rem;
  }

  .student-details {
    flex-direction: column;
    align-items: start;
    gap: 0.25rem;
  }
}

/* Comparison Analysis Styles */
.comparison-analysis {
  margin-bottom: 2rem;
}

.comparison-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comparison-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.comparison-item {
  background: #f9fafb;
  border-radius: 0.5rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
}

.component-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.component-header h5 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.weight-badge {
  background: #3b82f6;
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.comparison-bars {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.bar-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.bar-label {
  min-width: 80px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.3s;
}

.student-bar {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.class-avg-bar {
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.bar-value {
  min-width: 60px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #374151;
  text-align: right;
}

.performance-indicator {
  text-align: center;
  padding: 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.performance-indicator.above-average {
  background: #d1fae5;
  color: #065f46;
}

.performance-indicator.slightly-above {
  background: #dbeafe;
  color: #1e40af;
}

.performance-indicator.slightly-below {
  background: #fef3c7;
  color: #92400e;
}

.performance-indicator.below-average {
  background: #fee2e2;
  color: #dc2626;
}

/* Overall Comparison Styles */
.overall-comparison {
  margin-bottom: 2rem;
}

.overall-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.overall-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.student-icon {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.class-icon {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

/* Comparison Loading and Error States */
.comparison-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
}

.comparison-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.comparison-error .error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  margin-bottom: 1rem;
  font-weight: 500;
}

.comparison-error .error-icon {
  width: 20px;
  height: 20px;
}

.no-comparison-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.no-comparison-data svg {
  width: 48px;
  height: 48px;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.no-comparison-data h4 {
  margin: 0 0 0.5rem 0;
  color: #6b7280;
  font-weight: 600;
}

.no-comparison-data p {
  margin: 0;
  color: #9ca3af;
}

.debug-btn {
  transition: all 0.2s;
}

.debug-btn:hover {
  background: #d97706 !important;
  transform: translateY(-1px);
}

/* Enhanced Dropdown Styling */
.comparison-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
}

.comparison-header h3 {
  margin: 0;
  text-align: center;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
}

.course-selector {
  display: flex;
  justify-content: center;
  width: 100%;
  max-width: 400px;
}

.custom-dropdown {
  position: relative;
  display: inline-block;
  width: 100%;
}

.course-select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 100%;
  padding: 12px 48px 12px 16px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
  outline: none;
}

.course-select:hover {
  border-color: #457B9D;
  box-shadow: 0 4px 8px rgba(69, 123, 157, 0.15);
  transform: translateY(-1px);
}

.course-select:focus {
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2), 0 4px 8px rgba(69, 123, 157, 0.15);
  transform: translateY(-1px);
}

.course-select option {
  padding: 12px 16px;
  background: white;
  color: #374151;
  font-size: 14px;
}

.course-select option:hover {
  background: #f3f4f6;
}

.course-select option[value=""] {
  color: #9ca3af;
  font-style: italic;
}

.dropdown-arrow {
  position: absolute;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  pointer-events: none;
  transition: all 0.3s ease;
}

.arrow-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
  transition: all 0.3s ease;
}

.course-select:hover + .dropdown-arrow .arrow-icon {
  color: #457B9D;
  transform: scale(1.1);
}

.course-select:focus + .dropdown-arrow .arrow-icon {
  color: #457B9D;
  transform: scale(1.1) rotate(180deg);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .course-selector {
    max-width: 100%;
  }
  
  .course-select {
    font-size: 16px; /* Prevents zoom on iOS */
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .course-select {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    color: #f9fafb;
    border-color: #374151;
  }
  
  .course-select:hover,
  .course-select:focus {
    border-color: #60a5fa;
  }
  
  .course-select option {
    background: #1f2937;
    color: #f9fafb;
  }
  
  .arrow-icon {
    color: #9ca3af;
  }
  
  .course-select:hover + .dropdown-arrow .arrow-icon,
  .course-select:focus + .dropdown-arrow .arrow-icon {
    color: #60a5fa;
  }
}
</style>
