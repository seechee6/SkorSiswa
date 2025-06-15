<template>
  <div>
    <h3>At-Risk Students Management</h3>
    
    <div class="card">
      <div class="header-actions">
        <div class="search-container">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search students..." 
            @input="filterStudents"
            class="search-input"
          />
          <i class="fas fa-search search-icon"></i>
        </div>
        <div class="filter-container">
          <select v-model="riskLevelFilter" @change="filterStudents">
            <option value="all">All Risk Levels</option>
            <option value="high">High Risk</option>
            <option value="medium">Medium Risk</option>
            <option value="low">Low Risk</option>
          </select>
        </div>
        <div class="filter-container">
          <select v-model="semesterFilter" @change="filterStudents">
            <option value="all">All Semesters</option>
            <option value="2024-2">2024 Semester 2</option>
            <option value="2024-1">2024 Semester 1</option>
            <option value="2023-2">2023 Semester 2</option>
          </select>
        </div>        <button class="export-btn" @click="exportData">
          <i class="fas fa-download"></i> Export List
        </button>
      </div>
    </div>

    <div class="dashboard-card risk-metrics">
      <div class="metric-card">
        <h3>{{ metrics.highRisk }}</h3>
        <p>High Risk</p>
        <div class="risk-indicator high"></div>
      </div>
      <div class="metric-card">
        <h3>{{ metrics.mediumRisk }}</h3>
        <p>Medium Risk</p>
        <div class="risk-indicator medium"></div>
      </div>
      <div class="metric-card">
        <h3>{{ metrics.lowRisk }}</h3>
        <p>Low Risk</p>
        <div class="risk-indicator low"></div>
      </div>
      <div class="metric-card">
        <h3>{{ metrics.total }}</h3>
        <p>Total</p>
        <div class="risk-indicator total"></div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th @click="sortBy('id')">
              Student ID
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('name')">
              Student Name
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('riskLevel')">
              Risk Level
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('cgpa')">
              CGPA
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('failedCourses')">
              Failed Courses
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('attendanceRate')">
              Attendance
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('lastMeeting')">
              Last Meeting
              <i class="fas fa-sort"></i>
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in paginatedStudents" :key="student.id" :class="{ 'high-risk': student.riskLevel === 'high', 'medium-risk': student.riskLevel === 'medium', 'low-risk': student.riskLevel === 'low' }">
            <td>{{ student.id }}</td>
            <td>{{ student.name }}</td>
            <td>
              <span :class="`risk-badge ${student.riskLevel}`">
                {{ capitalizeFirst(student.riskLevel) }}
              </span>
            </td>
            <td>{{ student.cgpa }}</td>
            <td>{{ student.failedCourses }}</td>
            <td>{{ student.attendanceRate }}%</td>
            <td>{{ student.lastMeeting }}</td>
            <td class="actions-cell">
              <button class="action-btn view-btn" @click="viewStudent(student)">
                <i class="fas fa-eye"></i>
              </button>
              <button class="action-btn meeting-btn" @click="scheduleMeeting(student)">
                <i class="fas fa-calendar-plus"></i>
              </button>
              <button class="action-btn note-btn" @click="addNote(student)">
                <i class="fas fa-sticky-note"></i>
              </button>
            </td>
          </tr>
          <tr v-if="paginatedStudents.length === 0">
            <td colspan="8" class="no-data">No students match the current filters</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination-controls">
      <button 
        @click="currentPage > 1 && currentPage--"
        :disabled="currentPage === 1"
        class="pagination-btn"
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <span class="pagination-info">Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        @click="currentPage < totalPages && currentPage++"
        :disabled="currentPage === totalPages"
        class="pagination-btn"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
      <select v-model="perPage" @change="currentPage = 1" class="per-page-select">
        <option :value="5">5 per page</option>
        <option :value="10">10 per page</option>
        <option :value="20">20 per page</option>
        <option :value="50">50 per page</option>
      </select>
    </div>

    <!-- Meeting Modal -->
    <div v-if="showMeetingModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Schedule Meeting with {{ selectedStudent.name }}</h3>
          <button @click="showMeetingModal = false" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Date & Time:</label>
            <input type="datetime-local" v-model="meetingForm.datetime" class="form-input" />
          </div>
          <div class="form-group">
            <label>Meeting Type:</label>
            <select v-model="meetingForm.type" class="form-input">
              <option value="academic-review">Academic Review</option>
              <option value="course-planning">Course Planning</option>
              <option value="intervention">Intervention</option>
              <option value="general-check-in">General Check-in</option>
            </select>
          </div>
          <div class="form-group">
            <label>Duration (minutes):</label>
            <select v-model="meetingForm.duration" class="form-input">
              <option value="15">15 minutes</option>
              <option value="30">30 minutes</option>
              <option value="45">45 minutes</option>
              <option value="60">60 minutes</option>
            </select>
          </div>
          <div class="form-group">
            <label>Notes:</label>
            <textarea v-model="meetingForm.notes" class="form-input textarea"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showMeetingModal = false" class="cancel-btn">Cancel</button>
          <button @click="saveMeeting" class="save-btn">Schedule Meeting</button>
        </div>
      </div>
    </div>

    <!-- Notes Modal -->
    <div v-if="showNotesModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Notes for {{ selectedStudent.name }}</h3>
          <button @click="showNotesModal = false" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <h4>Add New Note</h4>
          <div class="form-group">
            <label>Note Type:</label>
            <select v-model="noteForm.type" class="form-input">
              <option value="academic">Academic</option>
              <option value="behavioral">Behavioral</option>
              <option value="attendance">Attendance</option>
              <option value="personal">Personal</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Note:</label>
            <textarea v-model="noteForm.content" class="form-input textarea"></textarea>
          </div>
          <div class="form-group">
            <label class="checkbox-container">
              <input type="checkbox" v-model="noteForm.confidential">
              <span class="checkmark"></span>
              Mark as confidential
            </label>
          </div>
          <button @click="saveNote" class="save-btn full-width">Save Note</button>

          <div class="previous-notes" v-if="studentNotes.length > 0">
            <h4>Previous Notes</h4>
            <div v-for="(note, index) in studentNotes" :key="index" class="note-item">
              <div class="note-header">
                <span class="note-type">{{ capitalizeFirst(note.type) }}</span>
                <span class="note-date">{{ note.date }}</span>
                <span v-if="note.confidential" class="confidential-badge">Confidential</span>
              </div>
              <p class="note-content">{{ note.content }}</p>
              <p class="note-author">By: {{ note.author }}</p>
            </div>
          </div>
          <div class="no-notes" v-else>
            <p>No previous notes for this student.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showNotesModal = false" class="close-btn">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AtRiskStudents',
  data() {
    return {
      searchQuery: '',
      riskLevelFilter: 'all',
      semesterFilter: 'all',
      sortKey: 'riskLevel',
      sortOrder: 'desc',
      currentPage: 1,
      perPage: 10,
      showMeetingModal: false,
      showNotesModal: false,
      selectedStudent: {},
      meetingForm: {
        datetime: '',
        type: 'academic-review',
        duration: '30',
        notes: ''
      },
      noteForm: {
        type: 'academic',
        content: '',
        confidential: false
      },
      metrics: {
        highRisk: 12,
        mediumRisk: 23,
        lowRisk: 15,
        total: 50
      },
      // Mock data - in a real application, this would come from an API
      students: [
        {
          id: 'S12345',
          name: 'Alex Johnson',
          riskLevel: 'high',
          cgpa: 1.8,
          failedCourses: 3,
          attendanceRate: 62,
          lastMeeting: '2025-05-20',
          riskFactors: ['Low CGPA', 'Failed core courses', 'Poor attendance']
        },
        {
          id: 'S23456',
          name: 'Jamie Smith',
          riskLevel: 'high',
          cgpa: 2.0,
          failedCourses: 2,
          attendanceRate: 70,
          lastMeeting: '2025-05-15',
          riskFactors: ['Failed prerequisites', 'Declining performance']
        },
        {
          id: 'S34567',
          name: 'Taylor Williams',
          riskLevel: 'medium',
          cgpa: 2.3,
          failedCourses: 1,
          attendanceRate: 78,
          lastMeeting: '2025-04-12',
          riskFactors: ['Borderline passing grades', 'Inconsistent attendance']
        },
        {
          id: 'S45678',
          name: 'Morgan Lee',
          riskLevel: 'medium',
          cgpa: 2.5,
          failedCourses: 1,
          attendanceRate: 80,
          lastMeeting: '2025-05-02',
          riskFactors: ['Struggling with major courses']
        },
        {
          id: 'S56789',
          name: 'Jordan Brown',
          riskLevel: 'low',
          cgpa: 2.7,
          failedCourses: 0,
          attendanceRate: 85,
          lastMeeting: '2025-03-20',
          riskFactors: ['Recent grade decline']
        },
        {
          id: 'S67890',
          name: 'Casey Miller',
          riskLevel: 'low',
          cgpa: 2.8,
          failedCourses: 0,
          attendanceRate: 90,
          lastMeeting: '2025-02-10',
          riskFactors: ['Struggling with one course']
        },
        {
          id: 'S78901',
          name: 'Riley Garcia',
          riskLevel: 'high',
          cgpa: 1.5,
          failedCourses: 4,
          attendanceRate: 50,
          lastMeeting: '2025-05-22',
          riskFactors: ['Multiple failed courses', 'Very poor attendance']
        }
      ],
      filteredStudents: [],
      // Mock data for student notes
      studentNotes: [
        {
          type: 'academic',
          date: '2025-05-22',
          content: 'Student is struggling with Calculus II. Recommended tutoring services and provided additional resources.',
          author: 'Dr. Wilson',
          confidential: false
        },
        {
          type: 'behavioral',
          date: '2025-05-10',
          content: 'Student mentioned having difficulty concentrating in class due to personal issues at home.',
          author: 'Dr. Wilson',
          confidential: true
        },
        {
          type: 'attendance',
          date: '2025-04-15',
          content: 'Student has missed 3 classes in the last two weeks. Discussed importance of regular attendance.',
          author: 'Dr. Wilson',
          confidential: false
        }
      ]
    }
  },
  created() {
    this.filteredStudents = [...this.students];
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.perPage);
    },
    paginatedStudents() {
      const startIndex = (this.currentPage - 1) * this.perPage;
      const endIndex = startIndex + this.perPage;
      return this.filteredStudents.slice(startIndex, endIndex);
    }
  },
  methods: {
    filterStudents() {
      this.filteredStudents = this.students.filter(student => {
        // Filter by search query
        const matchesSearch = student.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             student.id.toLowerCase().includes(this.searchQuery.toLowerCase());
        
        // Filter by risk level
        const matchesRiskLevel = this.riskLevelFilter === 'all' || student.riskLevel === this.riskLevelFilter;
        
        // Filter by semester (in a real app, students would have semester data)
        // For now, we'll just assume all students match the semester filter
        const matchesSemester = true;
        
        return matchesSearch && matchesRiskLevel && matchesSemester;
      });
      
      // Apply sorting
      this.sortStudents();
      
      // Reset pagination to first page
      this.currentPage = 1;
    },
    sortBy(key) {
      // If clicking the same key, toggle sort order
      if (this.sortKey === key) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortKey = key;
        this.sortOrder = 'asc';
      }
      
      this.sortStudents();
    },
    sortStudents() {
      this.filteredStudents.sort((a, b) => {
        let comparison = 0;
        
        if (a[this.sortKey] > b[this.sortKey]) {
          comparison = 1;
        } else if (a[this.sortKey] < b[this.sortKey]) {
          comparison = -1;
        }
        
        return this.sortOrder === 'desc' ? comparison * -1 : comparison;
      });
    },
    viewStudent(student) {
      // Navigate to student details view
      this.$router.push(`/advisor/students/${student.id}`);
    },
    scheduleMeeting(student) {
      this.selectedStudent = student;
      this.showMeetingModal = true;
      
      // Set default date to tomorrow at 10:00 AM
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      tomorrow.setHours(10, 0, 0, 0);
      
      // Format for datetime-local input: YYYY-MM-DDThh:mm
      const year = tomorrow.getFullYear();
      const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
      const day = String(tomorrow.getDate()).padStart(2, '0');
      const hours = String(tomorrow.getHours()).padStart(2, '0');
      const minutes = String(tomorrow.getMinutes()).padStart(2, '0');
      
      this.meetingForm.datetime = `${year}-${month}-${day}T${hours}:${minutes}`;
    },
    saveMeeting() {
      // In a real app, this would send data to an API
      console.log('Meeting scheduled:', {
        studentId: this.selectedStudent.id,
        studentName: this.selectedStudent.name,
        ...this.meetingForm
      });
      
      // Success message
      alert(`Meeting scheduled with ${this.selectedStudent.name} for ${this.meetingForm.datetime}`);
      
      // Close modal and reset form
      this.showMeetingModal = false;
      this.meetingForm = {
        datetime: '',
        type: 'academic-review',
        duration: '30',
        notes: ''
      };
    },
    addNote(student) {
      this.selectedStudent = student;
      this.showNotesModal = true;
      
      // Reset form
      this.noteForm = {
        type: 'academic',
        content: '',
        confidential: false
      };
    },
    saveNote() {
      // Validate
      if (!this.noteForm.content.trim()) {
        alert('Please enter note content');
        return;
      }
      
      // In a real app, this would send data to an API
      const newNote = {
        type: this.noteForm.type,
        date: new Date().toISOString().split('T')[0],
        content: this.noteForm.content,
        author: 'Dr. Wilson', // Would come from logged-in user in real app
        confidential: this.noteForm.confidential
      };
      
      // Add to the beginning of the list
      this.studentNotes.unshift(newNote);
      
      // Success message
      alert('Note added successfully');
      
      // Reset form
      this.noteForm = {
        type: 'academic',
        content: '',
        confidential: false
      };
    },
    exportData() {
      // In a real application, this would generate a CSV or Excel file
      alert('Exporting at-risk students list...');
    },
    capitalizeFirst(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  }
}
</script>

<style scoped>
.at-risk-students-container {
  padding: 20px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-container {
  position: relative;
}

.search-input {
  padding: 8px 15px 8px 35px;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 250px;
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
}

.filter-container select {
  padding: 8px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.export-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.export-btn:hover {
  background-color: #45a049;
}

.risk-metrics {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.metric-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  flex: 1;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
}

.metric-card h3 {
  font-size: 2rem;
  margin: 0;
  margin-bottom: 5px;
}

.metric-card p {
  margin: 0;
  color: #666;
}

.risk-indicator {
  height: 4px;
  width: 100%;
  position: absolute;
  bottom: 0;
  left: 0;
}

.risk-indicator.high {
  background-color: #f44336;
}

.risk-indicator.medium {
  background-color: #ff9800;
}

.risk-indicator.low {
  background-color: #2196F3;
}

.risk-indicator.total {
  background-color: #673AB7;
}

.table-responsive {
  overflow-x: auto;
  margin-bottom: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table th {
  background-color: #f5f5f5;
  padding: 12px 15px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #ddd;
}

.data-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
}

.data-table th i {
  margin-left: 5px;
  font-size: 0.8em;
}

.data-table th:hover {
  cursor: pointer;
  background-color: #eaeaea;
}

.data-table tr:hover {
  background-color: #f9f9f9;
}

.data-table tr.high-risk {
  border-left: 4px solid #f44336;
}

.data-table tr.medium-risk {
  border-left: 4px solid #ff9800;
}

.data-table tr.low-risk {
  border-left: 4px solid #2196F3;
}

.risk-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8em;
  font-weight: 600;
}

.risk-badge.high {
  background-color: #ffebee;
  color: #d32f2f;
}

.risk-badge.medium {
  background-color: #fff3e0;
  color: #e65100;
}

.risk-badge.low {
  background-color: #e3f2fd;
  color: #1565c0;
}

.actions-cell {
  white-space: nowrap;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  border: none;
  margin-right: 5px;
  cursor: pointer;
}

.view-btn {
  background-color: #2196F3;
  color: white;
}

.view-btn:hover {
  background-color: #1976D2;
}

.meeting-btn {
  background-color: #4CAF50;
  color: white;
}

.meeting-btn:hover {
  background-color: #388E3C;
}

.note-btn {
  background-color: #FF9800;
  color: white;
}

.note-btn:hover {
  background-color: #F57C00;
}

.pagination-controls {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 15px;
}

.pagination-btn {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid #ddd;
  background-color: white;
  cursor: pointer;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  font-size: 0.9em;
}

.per-page-select {
  padding: 5px 10px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.no-data {
  text-align: center;
  padding: 30px;
  color: #666;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5em;
  cursor: pointer;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.textarea {
  min-height: 100px;
  resize: vertical;
}

.save-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.save-btn:hover {
  background-color: #45a049;
}

.cancel-btn {
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.full-width {
  width: 100%;
}

/* Checkbox styling */
.checkbox-container {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  user-select: none;
}

.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #eee;
  border-radius: 4px;
}

.checkbox-container:hover input ~ .checkmark {
  background-color: #ccc;
}

.checkbox-container input:checked ~ .checkmark {
  background-color: #4CAF50;
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
  display: block;
}

.checkbox-container .checkmark:after {
  left: 7px;
  top: 3px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Note list styles */
.previous-notes {
  margin-top: 30px;
  border-top: 1px solid #eee;
  padding-top: 20px;
}

.note-item {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
}

.note-header {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.note-type {
  background-color: #e0e0e0;
  color: #333;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.8em;
  margin-right: 10px;
}

.note-date {
  color: #666;
  font-size: 0.9em;
  margin-right: 10px;
}

.note-content {
  margin: 0 0 10px 0;
  line-height: 1.5;
}

.note-author {
  margin: 0;
  font-size: 0.9em;
  color: #666;
  text-align: right;
}

.confidential-badge {
  background-color: #f44336;
  color: white;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.7em;
  font-weight: 600;
}

.no-notes {
  text-align: center;
  padding: 20px;
  color: #666;
}
</style>
