<template>
  <div class="student-enrollments">
    <div class="header">
      <h2>Student Enrollments</h2>
      <div class="header-info" v-if="selectedCourse">
        <span>Managing: <strong>{{ selectedCourse.code }} - {{ selectedCourse.name }}</strong></span>
        <button @click="goBackToCourses" class="btn-secondary">← Back to Courses</button>
      </div>
    </div>

    <!-- Course Selection View -->
    <div v-if="!selectedCourse" class="courses-view">      <div class="filters">
        <input v-model="courseSearch" placeholder="Search courses..." class="search-input">
        <select v-model="semesterFilter" class="semester-filter">
          <option value="">All Semesters</option>
          <option v-for="semester in semesters" :key="semester" :value="semester">
            {{ semester }}
          </option>
        </select>
        <select v-model="yearFilter" class="year-filter">
          <option value="">All Years</option>
          <option v-for="year in availableYears" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>

      <div class="courses-grid">
        <div 
          v-for="course in filteredCourses" 
          :key="course.id" 
          class="course-card"
          @click="selectCourse(course)"
        >
          <div class="course-header">
            <h3>{{ course.code }}</h3>
            <span class="enrollment-count">{{ getEnrollmentCount(course.id) }} students</span>
          </div>
          <p class="course-name">{{ course.name }}</p>
          <div class="course-details">
            <span class="semester">{{ course.semester }} {{ course.year }}</span>
            <span class="lecturer">{{ course.lecturer_name || 'No Lecturer' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Students View for Selected Course -->
    <div v-if="selectedCourse" class="students-view">
      <div class="course-actions">
        <button @click="showEnrollModal = true" class="btn-primary">
          <i class="fas fa-plus"></i> Enroll New Student
        </button>
        <div class="student-search">
          <input v-model="studentSearch" placeholder="Search students..." class="search-input">
        </div>
      </div>

      <div class="students-table">
        <table>
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Matric No</th>
              <th>Email</th>
              <th>Enrollment Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="enrollment in filteredStudentEnrollments" :key="enrollment.enrollment_id">
              <td>{{ enrollment.student_name }}</td>
              <td>{{ enrollment.matric_no }}</td>
              <td>{{ enrollment.email }}</td>
              <td>{{ formatDate(enrollment.enrollment_date) }}</td>
              <td class="actions">
                <button @click="viewStudentDetails(enrollment)" class="btn-view">
                  <i class="fas fa-eye"></i> View
                </button>
                <button @click="unenrollStudent(enrollment)" class="btn-delete">
                  <i class="fas fa-user-minus"></i> Unenroll
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div v-if="filteredStudentEnrollments.length === 0" class="empty-state">
          <p>No students enrolled in this course yet.</p>
          <button @click="showEnrollModal = true" class="btn-primary">Enroll First Student</button>
        </div>
      </div>
    </div>    <!-- Enroll Student Modal -->
    <div v-if="showEnrollModal" class="modal">
      <div class="modal-content">
        <h3>Enroll Student in {{ selectedCourse.code }}</h3>
        <p class="course-info">{{ selectedCourse.name }} ({{ selectedCourse.semester }} {{ selectedCourse.year }})</p>        <form @submit.prevent="enrollStudent">
          <div class="form-group">
            <label>Search and Select Student</label>
            <SearchableDropdown
              :items="unenrolledStudents"
              :selectedItem="selectedStudent"
              @select="onStudentSelect"
              placeholder="Type to search students..."
              itemType="student"
              displayField="full_name"
              subDisplayField="matric_no"
              :searchFields="['full_name', 'matric_no', 'email']"
              :pageSize="10"
            />
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="!selectedStudent">
              Enroll Student
            </button>
            <button type="button" @click="closeEnrollModal" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Student Details Modal -->
    <div v-if="showDetailsModal" class="modal">
      <div class="modal-content">
        <h3>Student Details</h3>
        <div v-if="selectedEnrollment" class="student-details">
          <div class="detail-section">
            <h4>Student Information</h4>
            <div class="detail-row">
              <strong>Name:</strong> {{ selectedEnrollment.student_name }}
            </div>
            <div class="detail-row">
              <strong>Matric No:</strong> {{ selectedEnrollment.matric_no }}
            </div>
            <div class="detail-row">
              <strong>Email:</strong> {{ selectedEnrollment.email }}
            </div>
          </div>
          
          <div class="detail-section">
            <h4>Course Information</h4>
            <div class="detail-row">
              <strong>Course:</strong> {{ selectedCourse.code }} - {{ selectedCourse.name }}
            </div>
            <div class="detail-row">
              <strong>Semester:</strong> {{ selectedCourse.semester }} {{ selectedCourse.year }}
            </div>
            <div class="detail-row">
              <strong>Lecturer:</strong> {{ selectedCourse.lecturer_name || 'Not Assigned' }}
            </div>
            <div class="detail-row">
              <strong>Enrollment Date:</strong> {{ formatDate(selectedEnrollment.enrollment_date) }}
            </div>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" @click="showDetailsModal = false" class="btn-secondary">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api';
import SearchableDropdown from '../SearchableDropdown.vue';

export default {
  name: 'StudentEnrollments',
  components: {
    SearchableDropdown
  },
  data() {
    return {
      enrollments: [],
      courses: [],
      unenrolledStudents: [],
      selectedCourse: null,      courseSearch: '',
      studentSearch: '',
      semesterFilter: '',
      yearFilter: '',
      showEnrollModal: false,
      showDetailsModal: false,
      selectedEnrollment: null,
      selectedStudent: null,
      enrollmentForm: {
        course_id: '',
        student_id: ''
      },      semesters: [
        '1',
        '2'
      ],
      availableYears: []
    }
  },
  computed: {    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = 
          course.code.toLowerCase().includes(this.courseSearch.toLowerCase()) ||
          course.name.toLowerCase().includes(this.courseSearch.toLowerCase()) ||
          (course.lecturer_name && course.lecturer_name.toLowerCase().includes(this.courseSearch.toLowerCase()));
        
        const matchesSemester = !this.semesterFilter || course.semester === this.semesterFilter;
        const matchesYear = !this.yearFilter || course.year === this.yearFilter;
        
        return matchesSearch && matchesSemester && matchesYear;
      });
    },
    courseEnrollments() {
      if (!this.selectedCourse) return [];
      return this.enrollments.filter(enrollment => enrollment.course_id == this.selectedCourse.id);
    },
    filteredStudentEnrollments() {
      return this.courseEnrollments.filter(enrollment => {
        if (!this.studentSearch) return true;
        return enrollment.student_name.toLowerCase().includes(this.studentSearch.toLowerCase()) ||
               enrollment.matric_no.toLowerCase().includes(this.studentSearch.toLowerCase()) ||
               enrollment.email.toLowerCase().includes(this.studentSearch.toLowerCase());
      });
    }
  },  created() {
    this.fetchEnrollments();
    this.fetchCourses();
  },
  methods: {
    async fetchEnrollments() {
      try {
        const response = await api.get('/api/admin/enrollments');
        this.enrollments = response.data;
      } catch (error) {
        console.error('Error fetching enrollments:', error);
        alert('Error loading enrollments. Please try again.');
      }
    },    async fetchCourses() {
      try {
        const response = await api.get('/api/admin/courses');
        this.courses = response.data;
        this.extractAvailableYears();
      } catch (error) {
        console.error('Error fetching courses:', error);
        alert('Error loading courses. Please try again.');
      }
    },
    extractAvailableYears() {
      // Extract unique years from courses and sort them
      const years = [...new Set(this.courses.map(course => course.year))];
      this.availableYears = years.sort();
    },
    async loadUnenrolledStudents() {
      if (!this.selectedCourse) {
        this.unenrolledStudents = [];
        return;
      }
      
      try {
        const response = await api.get(`/courses/${this.selectedCourse.id}/unenrolled-students`);
        this.unenrolledStudents = response.data;
      } catch (error) {
        console.error('Error fetching unenrolled students:', error);
        alert('Error loading available students. Please try again.');
      }
    },
    selectCourse(course) {
      this.selectedCourse = course;
      this.enrollmentForm.course_id = course.id;
      this.studentSearch = '';
      this.loadUnenrolledStudents();
    },    goBackToCourses() {
      this.selectedCourse = null;
      this.selectedStudent = null;
      this.enrollmentForm.course_id = '';
      this.enrollmentForm.student_id = '';
      this.studentSearch = '';
      this.unenrolledStudents = [];
    },getEnrollmentCount(courseId) {
      return this.enrollments.filter(enrollment => enrollment.course_id == courseId).length;
    },
    onStudentSelect(student) {
      this.selectedStudent = student;
      this.enrollmentForm.student_id = student ? student.id : '';
    },
    async enrollStudent() {
      if (!this.selectedStudent) {
        alert('Please select a student to enroll.');
        return;
      }

      try {
        await api.post('/api/admin/enrollments', this.enrollmentForm);
        alert('Student enrolled successfully!');
        this.closeEnrollModal();
        this.fetchEnrollments();
        this.loadUnenrolledStudents();
      } catch (error) {
        console.error('Error enrolling student:', error);
        if (error.response?.status === 400) {
          alert('Student is already enrolled in this course.');
        } else {
          alert('Error enrolling student. Please try again.');
        }
      }
    },
    async unenrollStudent(enrollment) {
      if (confirm(`Are you sure you want to unenroll ${enrollment.student_name} from ${this.selectedCourse.code}?`)) {
        try {
          await api.delete(`/api/admin/enrollments/${enrollment.enrollment_id}`);
          alert('Student unenrolled successfully!');
          this.fetchEnrollments();
          this.loadUnenrolledStudents();
        } catch (error) {
          console.error('Error unenrolling student:', error);
          alert('Error unenrolling student. Please try again.');
        }
      }
    },
    viewStudentDetails(enrollment) {
      this.selectedEnrollment = enrollment;
      this.showDetailsModal = true;
    },    closeEnrollModal() {
      this.showEnrollModal = false;
      this.selectedStudent = null;
      this.enrollmentForm.student_id = '';
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
}
</script>

<style scoped>
.student-enrollments {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-info span {
  color: #666;
}

/* Course Selection View */
.courses-view {
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-input, .semester-filter, .year-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.course-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.course-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  border-color: #4CAF50;
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.course-header h3 {
  margin: 0;
  color: #333;
  font-size: 1.2em;
}

.enrollment-count {
  background-color: #4CAF50;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  font-weight: bold;
}

.course-name {
  margin: 10px 0;
  color: #666;
  font-weight: 500;
}

.course-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.semester {
  background-color: #E3F2FD;
  color: #1976D2;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8em;
}

.lecturer {
  color: #666;
  font-size: 0.9em;
}

/* Students View */
.students-view {
  margin-top: 20px;
}

.course-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 15px;
}

.student-search {
  flex: 1;
  max-width: 300px;
}

.students-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f5f5f5;
  font-weight: bold;
  color: #333;
}

.actions {
  display: flex;
  gap: 8px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.empty-state p {
  margin-bottom: 15px;
  font-size: 1.1em;
}

/* Buttons */
.btn-primary {
  background-color: #4CAF50;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-primary:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.btn-view {
  background-color: #2196F3;
  color: white;
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-delete {
  background-color: #f44336;
  color: white;
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 25px;
  border-radius: 8px;
  min-width: 400px;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.course-info {
  background-color: #E8F5E9;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 15px;
  color: #2E7D32;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.no-students {
  color: #FF9800;
  font-style: italic;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

/* Student Details */
.student-details {
  margin: 20px 0;
}

.detail-section {
  margin-bottom: 20px;
}

.detail-section h4 {
  margin: 0 0 10px 0;
  color: #333;
  border-bottom: 2px solid #4CAF50;
  padding-bottom: 5px;
}

.detail-row {
  margin-bottom: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.detail-row:last-child {
  border-bottom: none;
}

/* Responsive Design */
@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
  }
  
  .course-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .student-search {
    max-width: none;
  }
  
  .actions {
    flex-direction: column;
  }
  
  .btn-view,
  .btn-delete {
    width: 100%;
    margin-bottom: 4px;
    justify-content: center;
  }
  
  .modal-content {
    margin: 10px;
    min-width: auto;
  }
  
  .header-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}

@media (max-width: 480px) {
  .student-enrollments {
    padding: 10px;
  }
  
  .course-card {
    padding: 15px;
  }
  
  .course-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  
  .course-details {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
}
</style>
