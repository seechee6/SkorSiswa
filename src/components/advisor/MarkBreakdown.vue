<template>
  <div class="mark-breakdown">
    <div class="header-section">
      <div class="title-section">
        <h3>Advisee Mark Breakdown</h3>
        <div class="student-info">
          <div class="student-avatar">{{ getInitials(student.name) }}</div>
          <div>
            <h4 class="student-name">{{ student.name }}</h4>
            <p class="student-details">{{ student.id }} | {{ student.program }} | Year {{ student.yearOfStudy }}</p>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <button @click="printReport" class="print-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print Report
        </button>
        <router-link :to="`/advisor/advisee-list`" class="back-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to List
        </router-link>
      </div>
    </div>

    <!-- Summary Card -->
    <div class="summary-section">
      <div class="card summary-card">
        <div class="summary-item">
          <div class="summary-label">Current CGPA</div>
          <div class="summary-value" :class="getCgpaClass(student.cgpa)">{{ student.cgpa.toFixed(2) }}</div>
        </div>
        <div class="divider"></div>
        <div class="summary-item">
          <div class="summary-label">Current Semester GPA</div>
          <div class="summary-value" :class="getCgpaClass(currentSemester.gpa)">{{ currentSemester.gpa.toFixed(2) }}</div>
        </div>
        <div class="divider"></div>
        <div class="summary-item">
          <div class="summary-label">Total Credits Earned</div>
          <div class="summary-value">{{ student.creditsEarned }}</div>
        </div>
        <div class="divider"></div>
        <div class="summary-item">
          <div class="summary-label">Risk Level</div>
          <div class="risk-badge" :class="student.riskLevel">{{ student.riskLevel || 'None' }}</div>
        </div>
      </div>
    </div>

    <!-- Semester Selection -->
    <div class="semester-selector">
      <label for="semester-select">Select Semester:</label>
      <select id="semester-select" v-model="selectedSemester">
        <option v-for="(semester, index) in semesters" :key="index" :value="semester.id">
          {{ semester.name }}
        </option>
      </select>
    </div>

    <!-- Course Marks Table -->
    <div class="card">
      <div class="card-header">
        <h4>Course Marks - {{ getCurrentSemesterName() }}</h4>
        <span class="semester-info">{{ currentSemester.courses.length }} courses | {{ currentSemester.totalCredits }} credits</span>
      </div>
      <div class="table-container">
        <table class="marks-table">
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Credits</th>
              <th class="center-align">Assignment Marks</th>
              <th class="center-align">Midterms</th>
              <th class="center-align">Final Exam</th>
              <th class="center-align">Total</th>
              <th class="center-align">Grade</th>
              <th class="center-align">Progress</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(course, index) in currentSemester.courses" :key="index">
              <td class="code-cell">{{ course.code }}</td>
              <td>{{ course.name }}</td>
              <td class="center-align">{{ course.credits }}</td>
              <td class="center-align">
                {{ course.assignment.earned }}/{{ course.assignment.total }}
                <div class="weight">({{ course.assignment.weight }}%)</div>
              </td>
              <td class="center-align">
                {{ course.midterms.earned }}/{{ course.midterms.total }}
                <div class="weight">({{ course.midterms.weight }}%)</div>
              </td>
              <td class="center-align">
                {{ course.finalExam.earned }}/{{ course.finalExam.total }}
                <div class="weight">({{ course.finalExam.weight }}%)</div>
              </td>
              <td class="total-cell">
                {{ calculateTotal(course) }}%
              </td>
              <td class="grade-cell">
                <span :class="getGradeClass(course.grade)">{{ course.grade }}</span>
              </td>
              <td>
                <div class="progress-bar">
                  <div 
                    class="progress" 
                    :style="{ width: calculateTotal(course) + '%' }" 
                    :class="getProgressClass(calculateTotal(course))"
                  ></div>
                </div>
              </td>
            </tr>
            <tr class="summary-row">
              <td colspan="2">Semester Total</td>
              <td class="center-align">{{ currentSemester.totalCredits }}</td>
              <td colspan="3"></td>
              <td class="total-cell">
                {{ currentSemester.averagePercentage.toFixed(1) }}%
              </td>
              <td class="grade-cell">
                <span :class="getGradeClass(currentSemester.averageGrade)">
                  {{ currentSemester.averageGrade }}
                </span>
              </td>
              <td>
                <div class="progress-bar">
                  <div 
                    class="progress" 
                    :style="{ width: currentSemester.averagePercentage + '%' }" 
                    :class="getProgressClass(currentSemester.averagePercentage)"
                  ></div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assessment Breakdown -->
    <div v-if="selectedCourse" class="card assessment-breakdown">
      <div class="card-header">
        <h4>Assessment Breakdown - {{ selectedCourse.code }}: {{ selectedCourse.name }}</h4>
        <button class="close-btn" @click="selectedCourse = null">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="assessment-tabs">
        <button 
          v-for="(tab, index) in assessmentTabs" 
          :key="index"
          @click="selectedTab = tab.id"
          :class="{ active: selectedTab === tab.id }"
          class="tab-btn"
        >
          {{ tab.name }}
        </button>
      </div>

      <div class="tab-content">
        <!-- Assignments Tab -->
        <div v-if="selectedTab === 'assignments'" class="assessment-detail">
          <table class="assessment-table">
            <thead>
              <tr>
                <th>Assessment</th>
                <th>Due Date</th>
                <th>Weight</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in selectedCourse.assessments.assignments" :key="index">
                <td>{{ item.name }}</td>
                <td>{{ item.dueDate }}</td>
                <td>{{ item.weight }}%</td>
                <td>{{ item.earned }}/{{ item.total }}</td>
                <td>
                  <span :class="getGradeClass(item.grade)">{{ item.grade }}</span>
                </td>
                <td>
                  <span class="status-badge" :class="item.status.toLowerCase()">
                    {{ item.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Midterms Tab -->
        <div v-if="selectedTab === 'midterms'" class="assessment-detail">
          <table class="assessment-table">
            <thead>
              <tr>
                <th>Assessment</th>
                <th>Date</th>
                <th>Weight</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in selectedCourse.assessments.midterms" :key="index">
                <td>{{ item.name }}</td>
                <td>{{ item.date }}</td>
                <td>{{ item.weight }}%</td>
                <td>{{ item.earned }}/{{ item.total }}</td>
                <td>
                  <span :class="getGradeClass(item.grade)">{{ item.grade }}</span>
                </td>
                <td>
                  <span class="status-badge" :class="item.status.toLowerCase()">
                    {{ item.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Final Exam Tab -->
        <div v-if="selectedTab === 'final'" class="assessment-detail">
          <table class="assessment-table">
            <thead>
              <tr>
                <th>Assessment</th>
                <th>Date</th>
                <th>Weight</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in selectedCourse.assessments.final" :key="index">
                <td>{{ item.name }}</td>
                <td>{{ item.date }}</td>
                <td>{{ item.weight }}%</td>
                <td>{{ item.earned }}/{{ item.total }}</td>
                <td>
                  <span :class="getGradeClass(item.grade)">{{ item.grade }}</span>
                </td>
                <td>
                  <span class="status-badge" :class="item.status.toLowerCase()">
                    {{ item.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Advisor Notes Section -->
    <div class="card advisor-notes">
      <div class="card-header">
        <h4>Advisor Notes</h4>
        <button @click="addNote" class="add-note-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Note
        </button>
      </div>

      <div v-if="showNoteForm" class="note-form">
        <textarea v-model="newNote.content" placeholder="Enter your note here..." rows="4"></textarea>
        <div class="form-actions">
          <button @click="saveNote" class="save-note-btn">Save Note</button>
          <button @click="cancelNote" class="cancel-note-btn">Cancel</button>
        </div>
      </div>

      <div class="notes-list">
        <div v-for="(note, index) in advisorNotes" :key="index" class="note-item">
          <div class="note-header">
            <span class="note-date">{{ note.date }}</span>
            <div class="note-actions">
              <button class="edit-note-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
              </button>
              <button class="delete-note-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
          <p class="note-content">{{ note.content }}</p>
        </div>

        <div v-if="advisorNotes.length === 0" class="no-notes">
          No notes have been added yet.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MarkBreakdown',
  data() {
    return {
      student: {
        name: 'John Smith',
        id: 'S12345',
        program: 'Computer Science',
        yearOfStudy: 2,
        cgpa: 3.42,
        riskLevel: 'high',
        creditsEarned: 48
      },
      semesters: [
        {
          id: 'current',
          name: 'Current Semester (Spring 2025)',
          gpa: 3.38,
          totalCredits: 15,
          averagePercentage: 81.2,
          averageGrade: 'A-',
          courses: [
            {
              code: 'CS2001',
              name: 'Data Structures and Algorithms',
              credits: 3,
              assignment: { earned: 17, total: 20, weight: 20 },
              midterms: { earned: 26, total: 30, weight: 30 },
              finalExam: { earned: 42, total: 50, weight: 50 },
              grade: 'B+',
              assessments: {
                assignments: [
                  { name: 'Assignment 1', dueDate: '15 Jan 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 2', dueDate: '01 Feb 2025', weight: 5, earned: 3, total: 5, grade: 'B', status: 'Completed' },
                  { name: 'Assignment 3', dueDate: '20 Feb 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 4', dueDate: '10 Mar 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' }
                ],
                midterms: [
                  { name: 'Midterm Exam', date: '28 Feb 2025', weight: 30, earned: 26, total: 30, grade: 'A-', status: 'Completed' }
                ],
                final: [
                  { name: 'Final Exam', date: '05 May 2025', weight: 50, earned: 42, total: 50, grade: 'B+', status: 'Completed' }
                ]
              }
            },
            {
              code: 'CS2002',
              name: 'Database Systems',
              credits: 3,
              assignment: { earned: 19, total: 20, weight: 20 },
              midterms: { earned: 25, total: 30, weight: 30 },
              finalExam: { earned: 47, total: 50, weight: 50 },
              grade: 'A',
              assessments: {
                assignments: [
                  { name: 'Assignment 1', dueDate: '20 Jan 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 2', dueDate: '10 Feb 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 3', dueDate: '01 Mar 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 4', dueDate: '20 Mar 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' }
                ],
                midterms: [
                  { name: 'Midterm Exam', date: '05 Mar 2025', weight: 30, earned: 25, total: 30, grade: 'A-', status: 'Completed' }
                ],
                final: [
                  { name: 'Final Exam', date: '10 May 2025', weight: 50, earned: 47, total: 50, grade: 'A', status: 'Completed' }
                ]
              }
            },
            {
              code: 'MATH201',
              name: 'Linear Algebra',
              credits: 3,
              assignment: { earned: 15, total: 20, weight: 20 },
              midterms: { earned: 22, total: 30, weight: 30 },
              finalExam: { earned: 38, total: 50, weight: 50 },
              grade: 'B',
              assessments: {
                assignments: [
                  { name: 'Assignment 1', dueDate: '12 Jan 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 2', dueDate: '29 Jan 2025', weight: 5, earned: 3, total: 5, grade: 'B', status: 'Completed' },
                  { name: 'Assignment 3', dueDate: '15 Feb 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 4', dueDate: '05 Mar 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' }
                ],
                midterms: [
                  { name: 'Midterm Exam', date: '25 Feb 2025', weight: 30, earned: 22, total: 30, grade: 'B', status: 'Completed' }
                ],
                final: [
                  { name: 'Final Exam', date: '08 May 2025', weight: 50, earned: 38, total: 50, grade: 'B', status: 'Completed' }
                ]
              }
            },
            {
              code: 'CS2003',
              name: 'Operating Systems',
              credits: 3,
              assignment: { earned: 18, total: 20, weight: 20 },
              midterms: { earned: 28, total: 30, weight: 30 },
              finalExam: { earned: 43, total: 50, weight: 50 },
              grade: 'A-',
              assessments: {
                assignments: [
                  { name: 'Assignment 1', dueDate: '18 Jan 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 2', dueDate: '05 Feb 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 3', dueDate: '25 Feb 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 4', dueDate: '15 Mar 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' }
                ],
                midterms: [
                  { name: 'Midterm Exam', date: '02 Mar 2025', weight: 30, earned: 28, total: 30, grade: 'A', status: 'Completed' }
                ],
                final: [
                  { name: 'Final Exam', date: '12 May 2025', weight: 50, earned: 43, total: 50, grade: 'A-', status: 'Completed' }
                ]
              }
            },
            {
              code: 'ENG201',
              name: 'Technical Communication',
              credits: 3,
              assignment: { earned: 19, total: 20, weight: 20 },
              midterms: { earned: 27, total: 30, weight: 30 },
              finalExam: { earned: 46, total: 50, weight: 50 },
              grade: 'A',
              assessments: {
                assignments: [
                  { name: 'Assignment 1', dueDate: '10 Jan 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 2', dueDate: '25 Jan 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' },
                  { name: 'Assignment 3', dueDate: '12 Feb 2025', weight: 5, earned: 4, total: 5, grade: 'A', status: 'Completed' },
                  { name: 'Assignment 4', dueDate: '01 Mar 2025', weight: 5, earned: 5, total: 5, grade: 'A+', status: 'Completed' }
                ],
                midterms: [
                  { name: 'Midterm Exam', date: '20 Feb 2025', weight: 30, earned: 27, total: 30, grade: 'A', status: 'Completed' }
                ],
                final: [
                  { name: 'Final Exam', date: '02 May 2025', weight: 50, earned: 46, total: 50, grade: 'A', status: 'Completed' }
                ]
              }
            }
          ]
        },
        {
          id: 'prev1',
          name: 'Fall 2024',
          gpa: 3.25,
          totalCredits: 15,
          averagePercentage: 79.5,
          averageGrade: 'B+',
          courses: [
            // Course data would be here
          ]
        },
        {
          id: 'prev2',
          name: 'Spring 2024',
          gpa: 3.65,
          totalCredits: 18,
          averagePercentage: 85.8,
          averageGrade: 'A',
          courses: [
            // Course data would be here
          ]
        }
      ],
      selectedSemester: 'current',
      selectedCourse: null,
      selectedTab: 'assignments',
      assessmentTabs: [
        { id: 'assignments', name: 'Assignments' },
        { id: 'midterms', name: 'Midterms' },
        { id: 'final', name: 'Final Exam' }
      ],
      advisorNotes: [
        {
          date: '10 Jun 2025',
          content: 'John has shown improvement in his Database Systems class. Recommended additional practice with algorithmic problems to strengthen his understanding of Data Structures and Algorithms.'
        },
        {
          date: '01 May 2025',
          content: 'Expressed concern about Linear Algebra performance. Referred to the math tutoring center for additional help.'
        }
      ],
      showNoteForm: false,
      newNote: {
        content: ''
      }
    }
  },
  computed: {
    currentSemester() {
      return this.semesters.find(s => s.id === this.selectedSemester)
    }
  },
  methods: {
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
    },
    getCgpaClass(cgpa) {
      if (cgpa >= 3.5) return 'cgpa-excellent'
      if (cgpa >= 3.0) return 'cgpa-good'
      if (cgpa >= 2.5) return 'cgpa-average'
      return 'cgpa-concern'
    },
    calculateTotal(course) {
      const assignmentContribution = (course.assignment.earned / course.assignment.total) * course.assignment.weight
      const midtermsContribution = (course.midterms.earned / course.midterms.total) * course.midterms.weight
      const finalExamContribution = (course.finalExam.earned / course.finalExam.total) * course.finalExam.weight
      
      return (assignmentContribution + midtermsContribution + finalExamContribution).toFixed(1)
    },
    getGradeClass(grade) {
      if (grade === 'A+' || grade === 'A') return 'grade-a'
      if (grade === 'A-' || grade === 'B+') return 'grade-b-plus'
      if (grade === 'B' || grade === 'B-') return 'grade-b'
      if (grade === 'C+' || grade === 'C') return 'grade-c'
      return 'grade-concern'
    },
    getProgressClass(percentage) {
      if (percentage >= 85) return 'progress-excellent'
      if (percentage >= 75) return 'progress-good'
      if (percentage >= 60) return 'progress-average'
      return 'progress-concern'
    },
    printReport() {
      window.print()
    },
    getCurrentSemesterName() {
      return this.currentSemester ? this.currentSemester.name : ''
    },
    addNote() {
      this.showNoteForm = true
    },
    saveNote() {
      if (this.newNote.content.trim()) {
        const today = new Date()
        const formattedDate = today.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })
        
        this.advisorNotes.unshift({
          date: formattedDate,
          content: this.newNote.content
        })
        
        this.showNoteForm = false
        this.newNote.content = ''
      }
    },
    cancelNote() {
      this.showNoteForm = false
      this.newNote.content = ''
    }
  }
}
</script>

<style scoped>
.mark-breakdown {
  padding: 0 16px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.title-section h3 {
  margin: 0 0 16px;
  font-size: 24px;
  font-weight: 700;
  color: #1D3557;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 16px;
  background: white;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border: 1px solid #f1f3f4;
}

.student-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #457B9D;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 18px;
}

.student-name {
  margin: 0 0 4px;
  font-size: 18px;
  font-weight: 600;
  color: #1D3557;
}

.student-details {
  margin: 0;
  font-size: 14px;
  color: #6c757d;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.print-btn, .back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.print-btn {
  background-color: #457B9D;
  color: white;
  border: none;
}

.print-btn:hover {
  background-color: #3d6e8d;
  transform: translateY(-2px);
}

.back-btn {
  background-color: #f8f9fa;
  color: #1D3557;
  border: 1px solid #dee2e6;
  text-decoration: none;
}

.back-btn:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.summary-section {
  margin-bottom: 24px;
}

.summary-card {
  display: flex;
  justify-content: space-around;
  padding: 24px;
  align-items: center;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.summary-label {
  font-size: 14px;
  color: #6c757d;
  margin-bottom: 8px;
}

.summary-value {
  font-size: 24px;
  font-weight: 700;
  color: #1D3557;
}

.divider {
  width: 1px;
  height: 60px;
  background-color: #dee2e6;
}

.cgpa-excellent {
  color: #2e7d32;
}

.cgpa-good {
  color: #1565c0;
}

.cgpa-average {
  color: #ff8f00;
}

.cgpa-concern {
  color: #c62828;
}

.risk-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  text-transform: capitalize;
}

.risk-badge.high {
  background: #ffebee;
  color: #c62828;
}

.risk-badge.medium {
  background: #fff8e1;
  color: #ff8f00;
}

.risk-badge.low {
  background: #e8f5e9;
  color: #2e7d32;
}

.semester-selector {
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.semester-selector label {
  font-size: 16px;
  font-weight: 500;
  color: #1D3557;
}

.semester-selector select {
  padding: 10px 16px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  font-size: 14px;
  background-color: white;
  min-width: 250px;
}

.semester-selector select:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 2px rgba(69, 123, 157, 0.2);
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  overflow: hidden;
  border: 1px solid #f1f3f4;
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #f1f3f4;
}

.card-header h4 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #1D3557;
}

.semester-info {
  font-size: 14px;
  color: #6c757d;
}

.table-container {
  overflow-x: auto;
}

.marks-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.marks-table th {
  padding: 12px 16px;
  border-bottom: 1px solid #e9ecef;
  color: #495057;
  font-weight: 600;
  text-align: left;
}

.marks-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f3f4;
  color: #495057;
}

.center-align {
  text-align: center;
}

.code-cell {
  font-family: monospace;
  font-weight: 600;
  color: #1D3557;
}

.weight {
  font-size: 12px;
  color: #6c757d;
  margin-top: 4px;
}

.total-cell {
  font-weight: 600;
  color: #1D3557;
  text-align: center;
}

.grade-cell {
  font-weight: 700;
  text-align: center;
}

.grade-a {
  color: #2e7d32;
}

.grade-b-plus {
  color: #1565c0;
}

.grade-b {
  color: #0277bd;
}

.grade-c {
  color: #ff8f00;
}

.grade-concern {
  color: #c62828;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #f1f3f4;
  border-radius: 4px;
  overflow: hidden;
}

.progress {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-excellent {
  background-color: #2e7d32;
}

.progress-good {
  background-color: #1565c0;
}

.progress-average {
  background-color: #ff8f00;
}

.progress-concern {
  background-color: #c62828;
}

.summary-row {
  background: #f8f9fa;
  font-weight: 600;
}

.assessment-breakdown {
  margin-top: 32px;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background-color: #f1f3f4;
}

.close-btn svg {
  width: 18px;
  height: 18px;
  color: #495057;
}

.assessment-tabs {
  display: flex;
  gap: 2px;
  background-color: #f8f9fa;
  padding: 0 24px;
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: none;
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}

.tab-btn.active {
  color: #1D3557;
  background-color: white;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: #457B9D;
}

.tab-content {
  padding: 24px;
}

.assessment-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.assessment-table th {
  padding: 12px 16px;
  border-bottom: 1px solid #e9ecef;
  color: #495057;
  font-weight: 600;
  text-align: left;
}

.assessment-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f3f4;
  color: #495057;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.completed {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status-badge.pending {
  background-color: #fff8e1;
  color: #ff8f00;
}

.status-badge.upcoming {
  background-color: #e1f5fe;
  color: #0277bd;
}

.advisor-notes {
  margin-top: 32px;
}

.add-note-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: #1D3557;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.add-note-btn:hover {
  background-color: #0f2942;
  transform: translateY(-1px);
}

.note-form {
  padding: 16px 24px;
  border-bottom: 1px solid #e9ecef;
}

.note-form textarea {
  width: 100%;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  font-size: 14px;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.note-form textarea:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 2px rgba(69, 123, 157, 0.2);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 12px;
}

.save-note-btn, .cancel-note-btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.save-note-btn {
  background-color: #457B9D;
  color: white;
  border: none;
}

.save-note-btn:hover {
  background-color: #3d6e8d;
}

.cancel-note-btn {
  background-color: transparent;
  color: #6c757d;
  border: 1px solid #dee2e6;
}

.cancel-note-btn:hover {
  background-color: #f8f9fa;
}

.notes-list {
  padding: 16px 24px;
}

.note-item {
  border-bottom: 1px solid #f1f3f4;
  padding: 16px 0;
}

.note-item:last-child {
  border-bottom: none;
}

.note-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.note-date {
  font-size: 14px;
  font-weight: 600;
  color: #1D3557;
}

.note-actions {
  display: flex;
  gap: 8px;
}

.edit-note-btn, .delete-note-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-note-btn:hover, .delete-note-btn:hover {
  background-color: #f1f3f4;
}

.edit-note-btn svg, .delete-note-btn svg {
  width: 16px;
  height: 16px;
}

.edit-note-btn svg {
  color: #457B9D;
}

.delete-note-btn svg {
  color: #e63946;
}

.note-content {
  font-size: 14px;
  color: #495057;
  margin: 0;
  line-height: 1.6;
}

.no-notes {
  text-align: center;
  color: #6c757d;
  padding: 32px 0;
  font-style: italic;
}

@media (max-width: 992px) {
  .summary-card {
    flex-direction: column;
    gap: 24px;
  }

  .divider {
    width: 80%;
    height: 1px;
  }

  .header-section {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .print-btn, .back-btn {
    flex: 1;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .assessment-tabs {
    overflow-x: auto;
    padding: 0 16px;
  }

  .tab-btn {
    padding: 12px 16px;
    white-space: nowrap;
  }
}

@media print {
  .header-actions,
  .close-btn,
  .note-actions,
  .add-note-btn,
  .note-form {
    display: none;
  }
  
  .card {
    box-shadow: none;
    border: 1px solid #ddd;
    break-inside: avoid;
  }
  
  .mark-breakdown {
    padding: 0;
  }
}
</style>
