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
          <div class="summary-label">Total Credits Enrolled</div>
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
      
      <!-- Show table when courses exist -->
      <div v-if="currentSemester.courses.length > 0" class="table-container">
        <table class="marks-table">
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Credits</th>
              <th class="center-align">Assignment Marks</th>
              <th class="center-align">Final Exam</th>
              <th class="center-align">Total</th>
              <th class="center-align">Grade</th>
            </tr>
          </thead>          <tbody>
            <tr v-for="(course, index) in (performanceData.courses || [])" :key="index" @click="selectCourse(course)" class="course-row">
              <td class="code-cell">{{ course.course_code }}</td>
              <td>{{ course.course_name }}</td>
              <td class="center-align">{{ course.credit_hours || 3 }}</td>              <td class="center-align">
                {{ formatNumber(course.assessment_total) || '0.00' }}/70
              </td>
              <td class="center-align">
                {{ formatNumber(course.final_exam_weighted) || '0.00' }}/30
              </td>
              <td class="total-cell">
                {{ parseFloat(course.total_marks || 0).toFixed(2) }}%
              </td>
              <td class="grade-cell">
                <span :class="getGradeClass(course.grade)">{{ course.grade }}</span>
              </td>
              <td>
              </td>
            </tr>
            <tr class="summary-row">
              <td colspan="2">Semester Total</td>
              <td class="center-align">{{ currentSemester.totalCredits }}</td>
              <td colspan="3"></td>
              
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Show empty state when no courses are enrolled -->
      <div v-else class="empty-semester-state">
        <div class="empty-state-content">
          <div class="empty-state-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
          </div>
          <h3>No Courses Enrolled Yet</h3>
          <p v-if="selectedSemester === 2">
            This is an upcoming semester. Course enrollment for {{ getCurrentSemesterName() }} has not started yet.
          </p>
          <p v-else>
            No course enrollment data available for this semester.
          </p>
          <div class="empty-state-actions" v-if="selectedSemester === 2">
            <div class="enrollment-info">
              <div class="info-item">
                <strong>Course Registration:</strong> Opens January 2025
              </div>
              <div class="info-item">
                <strong>Classes Begin:</strong> February 2025
              </div>
              <div class="info-item">
                <strong>Expected Credits:</strong> 18-21 credits
              </div>
            </div>
          </div>
        </div>
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
        <!-- All Assessments Tab -->
        <div v-if="selectedTab === 'all_assessments'" class="assessment-detail">
          <table class="assessment-table">
            <thead>
              <tr>
                <th>Assessment</th>
                <th>Type</th>
                <th>Weight (%)</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <!-- Regular Assessments -->
              <tr v-for="(item, index) in getAllAssessments()" :key="'assessment-' + index">
                <td>{{ item.name }}</td>
                <td>
                  <span class="assessment-type" :class="getAssessmentTypeClass(item.name)">
                    {{ getAssessmentType(item.name) }}
                  </span>
                </td>
                <td class="center-align">{{ item.weight }}%</td>
                <td class="center-align">{{ item.earned }}/{{ item.total }}</td>
                <td class="center-align">
                  <span :class="getGradeClass(item.grade)">{{ item.grade }}</span>
                </td>
                <td class="center-align">
                  <span class="status-badge" :class="item.status.toLowerCase()">
                    {{ item.status }}
                  </span>
                </td>
              </tr>
              <!-- Final Exam (if exists) -->
              <tr v-for="(item, index) in getFinalExam()" :key="'final-' + index" class="final-exam-row">
                <td>{{ item.name }}</td>
                <td>
                  <span class="assessment-type final-exam">Final Exam</span>
                </td>
                <td class="center-align">{{ item.weight }}%</td>
                <td class="center-align">{{ item.earned }}/{{ item.total }}</td>
                <td class="center-align">
                  <span :class="getGradeClass(item.grade)">{{ item.grade }}</span>
                </td>
                <td class="center-align">
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
        <textarea 
          v-model="newNote.content" 
          :placeholder="editingNote ? 'Edit your note here...' : 'Enter your note here...'" 
          rows="4"
        ></textarea>
        <div class="form-actions">
          <button @click="saveNote" class="save-note-btn">
            {{ editingNote ? 'Update Note' : 'Save Note' }}
          </button>
          <button @click="cancelNote" class="cancel-note-btn">Cancel</button>
        </div>
      </div>

      <div class="notes-list">
        <div v-for="(note, index) in advisorNotes" :key="index" class="note-item">
          <div class="note-header">
            <span class="note-date">{{ note.date }}</span>
            <div class="note-actions">
              <button class="edit-note-btn" @click="editNote(note)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
              </button>
              <button class="delete-note-btn" @click="deleteNote(note)">
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
import advisorService from '../../services/advisor.js';
import auth from '../../utils/auth.js';

export default {
  name: 'MarkBreakdown',
  props: {
    studentId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      loading: true,
      error: null,
      currentUser: null,
      student: {
        id: '',
        name: '',
        matric_no: '',
        program: '',
        yearOfStudy: 1,
        cgpa: 0.0,
        riskLevel: 'low',
        creditsEarned: 0
      },
      performanceData: {},
      selectedCourse: null,      selectedTab: 'all_assessments',
      assessmentTabs: [
        { id: 'all_assessments', name: 'All Assessments' }
      ],      advisorNotes: [],
      showNoteForm: false,
      editingNote: null,
      newNote: {
        content: ''
      },
      selectedSemester: 1,
      semesters: [
        { id: 1, name: 'Semester 1, 2024' },
        { id: 2, name: 'Semester 2, 2025 (Upcoming)' }
      ]
    }
  },  computed: {
    currentSemesterCourses() {
      // Return empty array for upcoming semester (Semester 2)
      if (this.selectedSemester === 2) {
        return [];
      }
      
      // Group courses by semester/year and return current semester
      if (!this.performanceData || !this.performanceData.courses) {
        return [];
      }
      return this.performanceData.courses.filter(course => 
        course.academic_year === '2024/2025' && course.semester === 'Current'
      );
    },
    currentSemester() {
      const courses = this.currentSemesterCourses;
      if (courses.length === 0) {
        return {
          gpa: 0.0,
          courses: [],
          totalCredits: 0,
          averagePercentage: 0,
          averageGrade: 'N/A'
        };
      }
      
      // Calculate semester GPA
      const totalPoints = courses.reduce((sum, course) => {
        return sum + this.convertGradeToPoints(course.grade || 'F');
      }, 0);
      const gpa = totalPoints / courses.length;
      
      // Calculate total credits
      const totalCredits = courses.reduce((sum, course) => {
        return sum + (parseInt(course.credits) || 3);
      }, 0);
      
      // Calculate average percentage
      const totalPercentage = courses.reduce((sum, course) => {
        return sum + parseFloat(this.calculateTotalPercentage(course));
      }, 0);
      const averagePercentage = totalPercentage / courses.length;
      
      // Determine average grade
      const averageGrade = this.convertPercentageToGrade(averagePercentage);
      
      return {
        gpa: gpa,
        courses: courses,
        totalCredits: totalCredits,
        averagePercentage: averagePercentage,
        averageGrade: averageGrade
      };
    },    overallGPA() {
      if (!this.performanceData || !this.performanceData.gpa) return 0.0;
      return this.performanceData.gpa.toFixed(2);
    }
  },
  async created() {
    this.currentUser = auth.getCurrentUser();
    
    if (!this.currentUser || !auth.isAdvisor()) {
      this.error = 'Access denied. Advisor authentication required.';
      return;
    }
    
    await this.fetchStudentPerformance();
    await this.fetchAdvisorNotes();
  },
  methods: {    async fetchStudentPerformance() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await advisorService.getAdviseePerformance(
          this.currentUser.id, 
          this.studentId
        );
          if (response.success) {
          this.performanceData = response.data || {};
          console.log('Performance data received:', this.performanceData);
          
          // Set basic student info from the response
          if (this.performanceData.student) {
            this.student = {
              id: this.performanceData.student.id,
              name: this.performanceData.student.name,
              studentId: this.performanceData.student.studentId,
              email: this.performanceData.student.email,
              year: this.performanceData.student.year,
              program: this.performanceData.student.program,
              cgpa: this.performanceData.gpa || 0
            };
            this.updateRiskLevel();
          }
          
          // Calculate total credits earned
          this.calculateTotalCreditsEarned();
          
          // Also fetch student basic info from advisees
          await this.fetchStudentBasicInfo();
          
        } else {
          this.error = response.message || 'Failed to fetch student performance data';
        }
      } catch (error) {
        console.error('Error fetching student performance:', error);
        this.error = 'Failed to load student performance data. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchStudentBasicInfo() {
      try {
        // Get student basic info from advisees list
        const adviseesResponse = await advisorService.getAdvisees(this.currentUser.id);
        if (adviseesResponse.success) {
          const studentInfo = adviseesResponse.data.find(s => s.id == this.studentId);
          if (studentInfo) {
            this.student.name = studentInfo.name;
            this.student.matric_no = studentInfo.studentId;
            this.student.program = studentInfo.program;
            this.student.yearOfStudy = studentInfo.year;
            this.student.cgpa = studentInfo.gpa;
            
            // Calculate total credits earned from performance data
            this.calculateTotalCreditsEarned();
            
            this.updateRiskLevel();
          }
        }
      } catch (error) {
        console.error('Error fetching student basic info:', error);
      }
    },
    
    updateRiskLevel() {
      if (this.student.cgpa >= 3.5) {
        this.student.riskLevel = 'low';
      } else if (this.student.cgpa >= 2.5) {
        this.student.riskLevel = 'medium';
      } else {
        this.student.riskLevel = 'high';
      }
    },
    
    calculateTotalCreditsEarned() {
      if (!this.performanceData || !this.performanceData.courses) {
        this.student.creditsEarned = 0;
        return;
      }
      
      // Calculate total credits from all enrolled courses (including failed ones)
      // If you want only passing grades, change this logic back to exclude 'F' grades
      const totalCredits = this.performanceData.courses.reduce((sum, course) => {
        const credits = parseInt(course.credit_hours) || 3;
        return sum + credits;
      }, 0);
      
      this.student.creditsEarned = totalCredits;
    },
      convertGradeToPoints(grade) {
      const gradePoints = {
        'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7,
        'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D': 1.0, 'F': 0.0
      };
      return gradePoints[grade] || 0.0;
    },
      convertPercentageToGrade(percentage) {
      if (percentage >= 85) return 'A';
      if (percentage >= 80) return 'A-';
      if (percentage >= 75) return 'B+';
      if (percentage >= 70) return 'B';
      if (percentage >= 65) return 'B-';
      if (percentage >= 60) return 'C+';
      if (percentage >= 55) return 'C';
      if (percentage >= 50) return 'C-';
      if (percentage >= 45) return 'D';
      return 'F';
    },
    
    getCurrentSemesterName() {
      // Return appropriate semester name based on selection
      const selectedSem = this.semesters.find(s => s.id === this.selectedSemester);
      return selectedSem ? selectedSem.name : 'Semester 1, 2024';
    },
    
    getInitials(name) {
      if (!name) return 'ST';
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase();
    },
    
    getCgpaClass(cgpa) {
      if (cgpa >= 3.5) return 'cgpa-excellent';
      if (cgpa >= 3.0) return 'cgpa-good';
      if (cgpa >= 2.5) return 'cgpa-average';
      return 'cgpa-concern';
    },
    
    calculateAssessmentTotal(course) {
      if (!course.assessments || course.assessments.length === 0) {
        return 0;
      }
      
      const totalMarks = course.assessments.reduce((sum, assessment) => {
        return sum + (parseFloat(assessment.student_mark) || 0);
      }, 0);
      
      return totalMarks.toFixed(1);
    },
      calculateAssessmentPercentage(course) {
      const assessmentMarks = course.assessment_total || 0;
      const maxAssessmentMarks = 70; // 70% for assessments
      
      if (maxAssessmentMarks === 0) {
        return 0;
      }
      
      return ((assessmentMarks / maxAssessmentMarks) * 100).toFixed(1);
    },
    
    calculateFinalExamPercentage(course) {
      const finalExamWeighted = course.final_exam_weighted || 0;
      const maxFinalMarks = 30; // 30% for final exam
      
      if (maxFinalMarks === 0) {
        return 0;
      }
      
      return ((finalExamWeighted / maxFinalMarks) * 100).toFixed(1);
    },
      calculateTotalPercentage(course) {
      // The backend already calculates the correct total mark out of 100
      // We should use the total_marks from backend
      return parseFloat(course.total_marks || 0).toFixed(1);
    },

    formatNumber(value) {
      return parseFloat(value || 0).toFixed(2);
    },

    getGradeClass(grade) {
      if (grade === 'A+' || grade === 'A') return 'grade-a';
      if (grade === 'A-' || grade === 'B+') return 'grade-b-plus';
      if (grade === 'B' || grade === 'B-') return 'grade-b';
      if (grade === 'C+' || grade === 'C') return 'grade-c';
      return 'grade-concern';
    },
      getProgressClass(percentage) {
      if (percentage >= 85) return 'progress-excellent';
      if (percentage >= 75) return 'progress-good';
      if (percentage >= 60) return 'progress-average';
      return 'progress-concern';
    },
    
    async selectCourse(course) {
      try {
        // Fetch detailed assessment data for this course
        const assessmentData = await this.fetchCourseAssessmentData(course.course_code);
        
        // Set selected course for detailed breakdown
        this.selectedCourse = {
          code: course.course_code,
          name: course.course_name,
          assessments: assessmentData
        };
      } catch (error) {
        console.error('Error fetching course assessment data:', error);
        // Fallback to basic data
        this.selectedCourse = {
          code: course.course_code,
          name: course.course_name,
          assessments: {
            assignments: [],
            midterms: [],
            final: []
          }
        };
      }
    },

    async fetchCourseAssessmentData(courseCode) {
      // Get assessments data from the performance data
      const courseAssessments = this.performanceData.assessments?.[courseCode] || [];
      
      // Process all assessments (no need to categorize into separate tabs)
      const allAssessments = [];
      
      courseAssessments.forEach(assessment => {
        const assessmentData = {
          name: assessment.assessment_name,
          weight: parseFloat(assessment.weightage || 0),
          earned: parseFloat(assessment.marks_obtained || 0),
          total: parseFloat(assessment.max_marks || 0),
          grade: this.calculateAssessmentGrade(assessment.marks_obtained, assessment.max_marks),
          status: assessment.marks_obtained !== null ? 'Completed' : 'Pending'
        };

        allAssessments.push(assessmentData);
      });

      // Get final exam data from course data
      const selectedCourseData = this.performanceData.courses?.find(c => c.course_code === courseCode);
      let finalExam = null;
      
      if (selectedCourseData && selectedCourseData.final_exam_marks !== null && selectedCourseData.final_exam_marks !== undefined) {
        finalExam = {
          name: 'Final Exam',
          weight: 30,
          earned: parseFloat(selectedCourseData.final_exam_marks || 0),
          total: 100,
          grade: this.convertPercentageToGrade(selectedCourseData.final_exam_marks || 0),
          status: selectedCourseData.final_exam_marks > 0 ? 'Completed' : 'Pending'
        };
      }

      return {
        allAssessments,
        finalExam
      };
    },

    calculateAssessmentGrade(earned, total) {
      if (!earned || !total) return 'N/A';
      const percentage = (parseFloat(earned) / parseFloat(total)) * 100;
      return this.convertPercentageToGrade(percentage);
    },

    getAllAssessments() {
      return this.selectedCourse?.assessments?.allAssessments || [];
    },

    getFinalExam() {
      const finalExam = this.selectedCourse?.assessments?.finalExam;
      return finalExam ? [finalExam] : [];
    },

    getAssessmentType(name) {
      const lowercaseName = name.toLowerCase();
      if (lowercaseName.includes('assignment')) return 'Assignment';
      if (lowercaseName.includes('project')) return 'Project';
      if (lowercaseName.includes('lab')) return 'Lab';
      if (lowercaseName.includes('midterm') || lowercaseName.includes('mid')) return 'Midterm';
      if (lowercaseName.includes('quiz')) return 'Quiz';
      if (lowercaseName.includes('test')) return 'Test';
      if (lowercaseName.includes('homework')) return 'Homework';
      return 'Assessment';
    },

    getAssessmentTypeClass(name) {
      const type = this.getAssessmentType(name).toLowerCase();
      return `type-${type}`;
    },
    
    printReport() {
      window.print();
    },
    
    async fetchAdvisorNotes() {
      try {
        const response = await advisorService.getAdviseeNotes(
          this.currentUser.id,
          this.studentId,
          1 // Default course ID
        );
        
        if (response && Array.isArray(response)) {
          this.advisorNotes = response.map(note => ({
            id: note.id,
            content: note.note,
            date: new Date(note.created_at).toLocaleDateString('en-US', { 
              day: '2-digit', 
              month: 'short', 
              year: 'numeric' 
            })
          }));
        }
      } catch (error) {
        console.error('Error fetching advisor notes:', error);
      }
    },
    
    async addNote() {
      this.editingNote = null;
      this.newNote.content = '';
      this.showNoteForm = true;
    },
    
    async editNote(note) {
      this.editingNote = note;
      this.newNote.content = note.content;
      this.showNoteForm = true;
    },
    
    async saveNote() {
      if (!this.newNote.content.trim()) return;
      
      try {
        if (this.editingNote) {
          // Update existing note
          await advisorService.updateAdviseeNote(
            this.editingNote.id,
            this.newNote.content
          );
          
          // Update local notes list
          const noteIndex = this.advisorNotes.findIndex(n => n.id === this.editingNote.id);
          if (noteIndex !== -1) {
            this.advisorNotes[noteIndex].content = this.newNote.content;
          }
        } else {
          // Add new note
          const response = await advisorService.addAdviseeNote(
            this.currentUser.id,
            this.studentId,
            this.newNote.content,
            1 // Default course ID
          );
          
          // Add to local notes list
          const today = new Date();
          const formattedDate = today.toLocaleDateString('en-US', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
          });
          
          this.advisorNotes.unshift({
            id: response.id,
            date: formattedDate,
            content: this.newNote.content
          });
        }
        
        this.showNoteForm = false;
        this.newNote.content = '';
        this.editingNote = null;
      } catch (error) {
        console.error('Error saving note:', error);
        alert('Failed to save note. Please try again.');
      }
    },
    
    async deleteNote(note) {
      if (!confirm('Are you sure you want to delete this note?')) {
        return;
      }
      
      try {
        await advisorService.deleteAdviseeNote(note.id);
        
        // Remove from local notes list
        const noteIndex = this.advisorNotes.findIndex(n => n.id === note.id);
        if (noteIndex !== -1) {
          this.advisorNotes.splice(noteIndex, 1);
        }
      } catch (error) {
        console.error('Error deleting note:', error);
        alert('Failed to delete note. Please try again.');
      }
    },
    
    cancelNote() {
      this.showNoteForm = false;
      this.newNote.content = '';
      this.editingNote = null;
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

/* Empty State Styles */
.empty-semester-state {
  padding: 60px 24px;
  text-align: center;
}

.empty-state-content {
  max-width: 500px;
  margin: 0 auto;
}

.empty-state-icon {
  margin-bottom: 24px;
  color: #9ca3af;
  display: flex;
  justify-content: center;
}

.empty-state-content h3 {
  color: #374151;
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 12px;
}

.empty-state-content p {
  color: #6b7280;
  font-size: 16px;
  line-height: 1.6;
  margin-bottom: 32px;
}

.enrollment-info {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 24px;
  text-align: left;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e2e8f0;
  font-size: 14px;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item strong {
  color: #374151;
  font-weight: 600;
}

.empty-state-actions {
  margin-top: 24px;
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
