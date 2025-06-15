<template>
  <div class="course-performance">
    <div class="header-section">
      <div class="title-section">
        <h3>Advisee Course Performance</h3>
        <p class="page-description">Monitor and analyze advisee performance across all courses</p>
      </div>
      <div class="header-actions">
        <select v-model="selectedStudent" class="student-selector" @change="loadStudentData">
          <option value="">Select Advisee</option>
          <option v-for="student in advisees" :key="student.id" :value="student.id">
            {{ student.name }} ({{ student.id }})
          </option>
        </select>
        <button @click="generateReport" :disabled="!selectedStudent" class="generate-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Generate Report
        </button>
        <button @click="exportPerformance" :disabled="!selectedStudent" class="export-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export
        </button>
      </div>
    </div>

    <!-- Student Info Card -->
    <div v-if="selectedStudentData" class="student-info-card">
      <div class="card">
        <div class="student-header">
          <div class="student-avatar">{{ getInitials(selectedStudentData.name) }}</div>
          <div class="student-details">
            <h4 class="student-name">{{ selectedStudentData.name }}</h4>
            <p class="student-meta">
              {{ selectedStudentData.id }} | {{ selectedStudentData.program }} | 
              Year {{ selectedStudentData.yearOfStudy }} | Semester {{ selectedStudentData.currentSemester }}
            </p>
          </div>
          <div class="overall-stats">
            <div class="stat-item">
              <div class="stat-label">Current CGPA</div>
              <div class="stat-value" :class="getCgpaClass(selectedStudentData.cgpa)">
                {{ selectedStudentData.cgpa.toFixed(2) }}
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Credits Earned</div>
              <div class="stat-value">{{ selectedStudentData.creditsEarned }}/{{ selectedStudentData.totalCredits }}</div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Risk Level</div>
              <div class="risk-badge" :class="selectedStudentData.riskLevel">
                {{ selectedStudentData.riskLevel || 'None' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Summary -->
    <div v-if="selectedStudentData" class="performance-summary">
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-header">
            <h5>Academic Progress</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
          </div>
          <div class="progress-text">{{ progressPercentage }}% Complete</div>
          <div class="progress-details">
            {{ selectedStudentData.creditsEarned }} of {{ selectedStudentData.totalCredits }} credits earned
          </div>
        </div>

        <div class="summary-card">
          <div class="card-header">
            <h5>Grade Distribution</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
            </svg>
          </div>
          <div class="grade-distribution">
            <div v-for="grade in gradeDistribution" :key="grade.grade" class="grade-item">
              <div class="grade-label">{{ grade.grade }}</div>
              <div class="grade-bar">
                <div class="grade-fill" :style="{ width: grade.percentage + '%', backgroundColor: grade.color }"></div>
              </div>
              <div class="grade-count">{{ grade.count }}</div>
            </div>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-header">
            <h5>Semester Comparison</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
          </div>
          <div class="semester-chart">
            <div class="chart-container">
              <div v-for="semester in semesterData" :key="semester.semester" class="semester-bar">
                <div class="bar-fill" :style="{ height: (semester.gpa / 4 * 100) + '%' }"></div>
                <div class="bar-label">S{{ semester.semester }}</div>
                <div class="bar-value">{{ semester.gpa.toFixed(2) }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-header">
            <h5>Performance Trends</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div class="trend-indicators">
            <div class="trend-item">
              <div class="trend-label">GPA Trend</div>
              <div class="trend-value" :class="gpaChange >= 0 ? 'positive' : 'negative'">
                <svg v-if="gpaChange >= 0" class="trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                </svg>
                <svg v-else class="trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                </svg>
                {{ Math.abs(gpaChange).toFixed(2) }}
              </div>
            </div>
            <div class="trend-item">
              <div class="trend-label">Credits/Semester</div>
              <div class="trend-value">{{ averageCreditsPerSemester }}</div>
            </div>
            <div class="trend-item">
              <div class="trend-label">Pass Rate</div>
              <div class="trend-value positive">{{ passRate }}%</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Performance Table -->
    <div v-if="selectedStudentData" class="card">
      <div class="table-header">
        <h4>Course Performance Details</h4>
        <div class="table-filters">
          <select v-model="semesterFilter" class="filter-select">
            <option value="">All Semesters</option>
            <option v-for="sem in availableSemesters" :key="sem" :value="sem">
              Semester {{ sem }}
            </option>
          </select>
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="current">Current</option>
            <option value="failed">Failed</option>
          </select>
        </div>
      </div>

      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th @click="sortBy('courseCode')" class="sortable">
                Course Code
                <svg class="sort-icon" :class="{ active: sortColumn === 'courseCode' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
              </th>
              <th @click="sortBy('courseName')" class="sortable">Course Name</th>
              <th @click="sortBy('semester')" class="sortable">Semester</th>
              <th @click="sortBy('credits')" class="sortable">Credits</th>
              <th @click="sortBy('totalMarks')" class="sortable">Total Marks</th>
              <th @click="sortBy('grade')" class="sortable">Grade</th>
              <th @click="sortBy('gpa')" class="sortable">GPA Points</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="course in filteredCourses" :key="course.id" class="table-row">
              <td class="course-code">{{ course.courseCode }}</td>
              <td>
                <div class="course-info">
                  <div class="course-name">{{ course.courseName }}</div>
                  <div class="course-lecturer">{{ course.lecturer }}</div>
                </div>
              </td>
              <td>{{ course.semester }}</td>
              <td>{{ course.credits }}</td>
              <td>
                <div class="marks-info">
                  <div class="marks-value">{{ course.totalMarks }}/100</div>
                  <div class="marks-bar">
                    <div class="marks-fill" :style="{ width: course.totalMarks + '%' }"></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="grade-badge" :class="getGradeClass(course.grade)">
                  {{ course.grade }}
                </span>
              </td>
              <td class="gpa-points">{{ course.gpaPoints.toFixed(2) }}</td>
              <td>
                <span class="status-badge" :class="course.status">{{ course.status }}</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="viewDetailedBreakdown(course)" class="action-btn view-btn" title="View Details">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="viewAssessments(course)" class="action-btn assess-btn" title="View Assessments">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!selectedStudent" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
      </div>
      <h4>Select an Advisee</h4>
      <p>Choose an advisee from the dropdown to view their course performance and academic progress.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CoursePerformance',
  data() {
    return {
      selectedStudent: '',
      selectedStudentData: null,
      semesterFilter: '',
      statusFilter: '',
      sortColumn: 'semester',
      sortDirection: 'desc',

      advisees: [
        { id: 'STU001', name: 'Alice Johnson' },
        { id: 'STU002', name: 'Bob Smith' },
        { id: 'STU003', name: 'Carol Davis' },
        { id: 'STU004', name: 'David Wilson' },
        { id: 'STU005', name: 'Emma Brown' }
      ],

      // Sample student performance data
      studentData: {
        'STU001': {
          id: 'STU001',
          name: 'Alice Johnson',
          program: 'Computer Science',
          yearOfStudy: 2,
          currentSemester: 4,
          cgpa: 3.45,
          creditsEarned: 58,
          totalCredits: 120,
          riskLevel: 'moderate',
          courses: [
            {
              id: 1,
              courseCode: 'CS201',
              courseName: 'Data Structures',
              lecturer: 'Dr. Smith',
              semester: 3,
              credits: 3,
              totalMarks: 78,
              grade: 'B+',
              gpaPoints: 3.3,
              status: 'completed'
            },
            {
              id: 2,
              courseCode: 'CS202',
              courseName: 'Algorithms',
              lecturer: 'Prof. Johnson',
              semester: 4,
              credits: 3,
              totalMarks: 85,
              grade: 'A-',
              gpaPoints: 3.7,
              status: 'current'
            },
            {
              id: 3,
              courseCode: 'MATH201',
              courseName: 'Discrete Mathematics',
              lecturer: 'Dr. Wilson',
              semester: 3,
              credits: 4,
              totalMarks: 65,
              grade: 'C+',
              gpaPoints: 2.3,
              status: 'completed'
            },
            {
              id: 4,
              courseCode: 'CS301',
              courseName: 'Database Systems',
              lecturer: 'Prof. Davis',
              semester: 4,
              credits: 3,
              totalMarks: 72,
              grade: 'B',
              gpaPoints: 3.0,
              status: 'current'
            }
          ]
        }
      }
    }
  },

  computed: {
    filteredCourses() {
      if (!this.selectedStudentData) return [];
      
      let courses = [...this.selectedStudentData.courses];

      if (this.semesterFilter) {
        courses = courses.filter(course => course.semester == this.semesterFilter);
      }

      if (this.statusFilter) {
        courses = courses.filter(course => course.status === this.statusFilter);
      }

      // Sort courses
      courses.sort((a, b) => {
        let aValue = a[this.sortColumn];
        let bValue = b[this.sortColumn];

        if (this.sortDirection === 'asc') {
          return aValue > bValue ? 1 : -1;
        } else {
          return aValue < bValue ? 1 : -1;
        }
      });

      return courses;
    },

    availableSemesters() {
      if (!this.selectedStudentData) return [];
      return [...new Set(this.selectedStudentData.courses.map(c => c.semester))].sort();
    },

    progressPercentage() {
      if (!this.selectedStudentData) return 0;
      return Math.round((this.selectedStudentData.creditsEarned / this.selectedStudentData.totalCredits) * 100);
    },

    gradeDistribution() {
      if (!this.selectedStudentData) return [];
      
      const grades = {};
      const total = this.selectedStudentData.courses.length;
      
      this.selectedStudentData.courses.forEach(course => {
        grades[course.grade] = (grades[course.grade] || 0) + 1;
      });

      const gradeColors = {
        'A+': '#10b981', 'A': '#10b981', 'A-': '#059669',
        'B+': '#3b82f6', 'B': '#2563eb', 'B-': '#1d4ed8',
        'C+': '#f59e0b', 'C': '#d97706', 'C-': '#b45309',
        'D+': '#ef4444', 'D': '#dc2626', 'F': '#991b1b'
      };

      return Object.entries(grades).map(([grade, count]) => ({
        grade,
        count,
        percentage: Math.round((count / total) * 100),
        color: gradeColors[grade] || '#6b7280'
      }));
    },

    semesterData() {
      if (!this.selectedStudentData) return [];
      
      const semesters = {};
      this.selectedStudentData.courses.forEach(course => {
        if (!semesters[course.semester]) {
          semesters[course.semester] = { totalPoints: 0, totalCredits: 0 };
        }
        semesters[course.semester].totalPoints += course.gpaPoints * course.credits;
        semesters[course.semester].totalCredits += course.credits;
      });

      return Object.entries(semesters).map(([semester, data]) => ({
        semester: parseInt(semester),
        gpa: data.totalPoints / data.totalCredits
      })).sort((a, b) => a.semester - b.semester);
    },

    gpaChange() {
      const semesters = this.semesterData;
      if (semesters.length < 2) return 0;
      return semesters[semesters.length - 1].gpa - semesters[semesters.length - 2].gpa;
    },

    averageCreditsPerSemester() {
      if (!this.selectedStudentData) return 0;
      const totalCredits = this.selectedStudentData.creditsEarned;
      const semesters = this.selectedStudentData.currentSemester;
      return Math.round(totalCredits / semesters);
    },

    passRate() {
      if (!this.selectedStudentData) return 0;
      const passed = this.selectedStudentData.courses.filter(c => c.grade !== 'F').length;
      return Math.round((passed / this.selectedStudentData.courses.length) * 100);
    }
  },

  methods: {
    loadStudentData() {
      if (this.selectedStudent && this.studentData[this.selectedStudent]) {
        this.selectedStudentData = this.studentData[this.selectedStudent];
      } else {
        this.selectedStudentData = null;
      }
    },

    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortDirection = 'asc';
      }
    },

    getInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },

    getCgpaClass(cgpa) {
      if (cgpa >= 3.5) return 'excellent';
      if (cgpa >= 3.0) return 'good';
      if (cgpa >= 2.5) return 'average';
      return 'poor';
    },

    getGradeClass(grade) {
      const excellent = ['A+', 'A', 'A-'];
      const good = ['B+', 'B', 'B-'];
      const average = ['C+', 'C', 'C-'];
      const poor = ['D+', 'D', 'F'];

      if (excellent.includes(grade)) return 'excellent';
      if (good.includes(grade)) return 'good';
      if (average.includes(grade)) return 'average';
      if (poor.includes(grade)) return 'poor';
      return '';
    },

    viewDetailedBreakdown(course) {
      this.$router.push({
        path: '/advisor/mark-breakdown',
        query: { student: this.selectedStudent, course: course.id }
      });
    },

    viewAssessments(course) {
      // Navigate to assessments view or show modal
      console.log('View assessments for', course);
    },

    generateReport() {
      this.$router.push({
        path: '/advisor/advisee-reports',
        query: { student: this.selectedStudent, type: 'performance' }
      });
    },

    exportPerformance() {
      // Mock export functionality
      console.log('Exporting performance data for', this.selectedStudent);
    }
  }
}
</script>

<style scoped>
.course-performance {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.title-section h3 {
  color: #1f2937;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.page-description {
  color: #6b7280;
  font-size: 16px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.student-selector {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  min-width: 200px;
  outline: none;
  cursor: pointer;
}

.student-selector:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.generate-btn, .export-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.generate-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.generate-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-1px);
}

.export-btn {
  background: #10b981;
  color: white;
}

.export-btn:hover:not(:disabled) {
  background: #059669;
}

.generate-btn:disabled, .export-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Card Styles */
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

/* Student Info Card */
.student-info-card {
  margin-bottom: 24px;
}

.student-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 24px;
}

.student-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 24px;
}

.student-details {
  flex: 1;
}

.student-name {
  color: #1f2937;
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.student-meta {
  color: #6b7280;
  font-size: 16px;
  margin: 0;
}

.overall-stats {
  display: flex;
  gap: 32px;
}

.stat-item {
  text-align: center;
}

.stat-label {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-value.excellent {
  color: #10b981;
}

.stat-value.good {
  color: #3b82f6;
}

.stat-value.average {
  color: #f59e0b;
}

.stat-value.poor {
  color: #ef4444;
}

.risk-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.risk-badge.low {
  background: #d1fae5;
  color: #047857;
}

.risk-badge.moderate {
  background: #fef3c7;
  color: #b45309;
}

.risk-badge.high {
  background: #fee2e2;
  color: #dc2626;
}

/* Performance Summary */
.performance-summary {
  margin-bottom: 24px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-header h5 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.header-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
}

/* Progress Bar */
.progress-bar {
  width: 100%;
  height: 8px;
  background: #f3f4f6;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 12px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #3b82f6, #10b981);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.progress-details {
  color: #6b7280;
  font-size: 14px;
}

/* Grade Distribution */
.grade-distribution {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.grade-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.grade-label {
  font-weight: 600;
  color: #1f2937;
  width: 30px;
  text-align: center;
}

.grade-bar {
  flex: 1;
  height: 6px;
  background: #f3f4f6;
  border-radius: 3px;
  overflow: hidden;
}

.grade-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.grade-count {
  font-weight: 500;
  color: #6b7280;
  width: 20px;
  text-align: center;
}

/* Semester Chart */
.semester-chart {
  height: 180px;
}

.chart-container {
  display: flex;
  align-items: end;
  justify-content: space-between;
  height: 140px;
  padding: 0 10px;
}

.semester-bar {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  max-width: 60px;
}

.bar-fill {
  width: 30px;
  background: linear-gradient(to top, #3b82f6, #8b5cf6);
  border-radius: 4px 4px 0 0;
  min-height: 10px;
  transition: height 0.3s ease;
}

.bar-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

.bar-value {
  font-size: 14px;
  color: #1f2937;
  font-weight: 600;
}

/* Trend Indicators */
.trend-indicators {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.trend-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.trend-label {
  color: #6b7280;
  font-size: 14px;
}

.trend-value {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 600;
  font-size: 16px;
}

.trend-value.positive {
  color: #10b981;
}

.trend-value.negative {
  color: #ef4444;
}

.trend-icon {
  width: 16px;
  height: 16px;
}

/* Table Styles */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 20px 0 20px;
}

.table-header h4 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.table-filters {
  display: flex;
  gap: 12px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  outline: none;
  cursor: pointer;
}

.filter-select:focus {
  border-color: #3b82f6;
}

.table-container {
  padding: 20px;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  text-align: left;
  padding: 12px;
  color: #374151;
  font-weight: 600;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
}

.data-table th.sortable:hover {
  background: #f9fafb;
}

.sort-icon {
  width: 16px;
  height: 16px;
  margin-left: 4px;
  color: #9ca3af;
  transition: color 0.2s;
}

.sort-icon.active {
  color: #3b82f6;
}

.data-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f3f4f6;
}

.table-row:hover {
  background: #f9fafb;
}

.course-code {
  font-weight: 600;
  color: #3b82f6;
}

.course-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.course-name {
  font-weight: 500;
  color: #1f2937;
}

.course-lecturer {
  font-size: 12px;
  color: #6b7280;
}

.marks-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.marks-value {
  font-weight: 500;
  color: #1f2937;
}

.marks-bar {
  width: 100px;
  height: 4px;
  background: #f3f4f6;
  border-radius: 2px;
  overflow: hidden;
}

.marks-fill {
  height: 100%;
  background: linear-gradient(135deg, #3b82f6, #10b981);
  border-radius: 2px;
  transition: width 0.3s ease;
}

.grade-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
  min-width: 40px;
}

.grade-badge.excellent {
  background: #d1fae5;
  color: #047857;
}

.grade-badge.good {
  background: #dbeafe;
  color: #1d4ed8;
}

.grade-badge.average {
  background: #fef3c7;
  color: #b45309;
}

.grade-badge.poor {
  background: #fee2e2;
  color: #dc2626;
}

.gpa-points {
  font-weight: 600;
  color: #1f2937;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.completed {
  background: #d1fae5;
  color: #047857;
}

.status-badge.current {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-badge.failed {
  background: #fee2e2;
  color: #dc2626;
}

.action-buttons {
  display: flex;
  gap: 4px;
}

.action-btn {
  padding: 6px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.view-btn {
  background: #dbeafe;
  color: #1d4ed8;
}

.view-btn:hover {
  background: #bfdbfe;
}

.assess-btn {
  background: #f3f4f6;
  color: #374151;
}

.assess-btn:hover {
  background: #e5e7eb;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  opacity: 0.5;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h4 {
  color: #374151;
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 16px;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .course-performance {
    padding: 16px;
  }

  .header-section {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .student-header {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }

  .overall-stats {
    justify-content: center;
    gap: 20px;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .table-filters {
    flex-direction: column;
    gap: 8px;
  }

  .filter-select {
    width: 100%;
  }

  .table-container {
    overflow-x: scroll;
  }

  .data-table {
    min-width: 800px;
  }
}
</style>
