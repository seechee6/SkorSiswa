<template>
  <div>
    <div class="header-section">
      <h3>Manage Courses</h3>
      <button @click="showAddForm = !showAddForm" class="add-btn">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        {{ showAddForm ? 'Cancel' : 'Add New Course' }}
      </button>
    </div>

    <!-- Add New Course Form (collapsible) -->
    <div class="card add-form" v-show="showAddForm">
      <h4>Add New Course</h4>
      <form @submit.prevent="createCourse" class="course-form">
        <div class="form-grid">
          <div class="form-group">
            <label for="code">Course Code</label>
            <input 
              id="code"
              v-model="newCourse.code" 
              placeholder="e.g. CS101" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="name">Course Name</label>
            <input 
              id="name"
              v-model="newCourse.name" 
              placeholder="e.g. Introduction to Computer Science" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester" v-model="newCourse.semester" required>
              <option value="">Select Semester</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
              <option value="3">Semester 3</option>
            </select>
          </div>
          <div class="form-group">
            <label for="year">Academic Year</label>
            <select id="year" v-model="newCourse.year" required>
              <option value="">Select Academic Year</option>
              <option value="2023/2024">2023/2024</option>
              <option value="2024/2025">2024/2025</option>
              <option value="2025/2026">2025/2026</option>
              <option value="2026/2027">2026/2027</option>
              <option value="2027/2028">2027/2028</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Course
          </button>
          <button type="button" @click="cancelAdd" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Courses Table -->
    <div class="card">
      <div class="table-header">
        <h4>Your Courses</h4>
        <span class="course-count">{{ courses.length }} courses</span>
      </div>
      
      <div class="table-container">
        <table class="course-table">
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th>Students</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="course in courses" :key="course.id" class="course-row">
              <td class="code-cell">
                <span class="course-code">{{ course.code }}</span>
              </td>
              <td class="name-cell">
                <div class="course-name">{{ course.name }}</div>
              </td>
              <td class="semester-cell">Semester {{ course.semester }}</td>
              <td class="year-cell">{{ course.year }}</td>
              <td class="students-cell">
                <span class="student-count">{{ course.student_count || 0 }}</span>
              </td>
              <td class="actions-cell">
                <button @click="editCourse(course)" class="edit-btn">
                  <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button @click="confirmDelete(course)" class="delete-btn">
                  <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Empty State -->
        <div v-if="courses.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <h4>No Courses Yet</h4>
          <p>Click "Add New Course" to create your first course.</p>
        </div>
      </div>
      
      <div v-if="error" class="error-message">{{ error }}</div>
      <div v-if="success" class="success-message">{{ success }}</div>
    </div>

    <!-- Edit Course Modal -->
    <div v-if="editing" class="modal" @click.self="closeEditModal">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Edit Course</h4>
          <button @click="closeEditModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <form @submit.prevent="updateCourse" class="course-form">
          <div class="form-grid">
            <div class="form-group">
              <label for="edit-code">Course Code</label>
              <input 
                id="edit-code"
                v-model="editCourseData.code" 
                placeholder="Course Code" 
                required 
              />
            </div>
            <div class="form-group">
              <label for="edit-name">Course Name</label>
              <input 
                id="edit-name"
                v-model="editCourseData.name" 
                placeholder="Course Name" 
                required 
              />
            </div>
            <div class="form-group">
              <label for="edit-semester">Semester</label>
              <select id="edit-semester" v-model="editCourseData.semester" required>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
              </select>
            </div>
            <div class="form-group">
              <label for="edit-year">Academic Year</label>
              <select id="edit-year" v-model="editCourseData.year" required>
                <option value="2023/2024">2023/2024</option>
                <option value="2024/2025">2024/2025</option>
                <option value="2025/2026">2025/2026</option>
                <option value="2026/2027">2026/2027</option>
                <option value="2027/2028">2027/2028</option>
              </select>
            </div>
          </div>
          <div class="modal-actions">
            <button type="submit" class="save-btn">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Changes
            </button>
            <button type="button" @click="closeEditModal" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="modal" @click.self="closeDeleteConfirm">
      <div class="modal-content delete-modal">
        <div class="delete-icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h4>Delete Course</h4>
        <p>Are you sure you want to delete <strong>{{ courseToDelete?.name }}</strong>?</p>
        <p class="warning-text">This action cannot be undone and will remove all associated data.</p>
        <div class="modal-actions">
          <button @click="deleteCourse" class="delete-confirm-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete Course
          </button>
          <button @click="closeDeleteConfirm" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import api from '../../api'

export default {
  name: 'ManageCourses',
  data() {
    return {
      courses: [],
      error: '',
      success: '',
      showAddForm: false,
      showDeleteConfirm: false,
      courseToDelete: null,
      editing: false,
      newCourse: { 
        code: '', 
        name: '', 
        semester: '', 
        year: ''
      },
      editCourseData: { 
        id: '', 
        code: '', 
        name: '', 
        semester: '', 
        year: '' 
      }
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get('/courses')
        this.courses = res.data.filter(c => c.lecturer_id === user.id)
        this.clearMessages()
      } catch (e) {
        this.showError('Failed to load courses.')
      }
    },
    
    async createCourse() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post('/courses', {
          ...this.newCourse,
          lecturer_id: user.id
        })
        
        this.resetNewCourse()
        this.showAddForm = false
        this.showSuccess('Course created successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to create course. Please check your input and try again.')
      }
    },
    
    editCourse(course) {
      this.editing = true
      this.editCourseData = { ...course }
      this.clearMessages()
    },
    
    async updateCourse() {
      try {
        await api.put(`/courses/${this.editCourseData.id}`, this.editCourseData)
        this.editing = false
        this.showSuccess('Course updated successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to update course. Please try again.')
      }
    },
    
    confirmDelete(course) {
      this.showDeleteConfirm = true
      this.courseToDelete = course
      this.clearMessages()
    },
    
    async deleteCourse() {
      try {
        await api.delete(`/courses/${this.courseToDelete.id}`)
        this.closeDeleteConfirm()
        this.showSuccess('Course deleted successfully!')
        this.fetchCourses()
      } catch (e) {
        this.showError('Failed to delete course. Please try again.')
      }
    },
    
    cancelAdd() {
      this.showAddForm = false
      this.resetNewCourse()
      this.clearMessages()
    },
    
    closeEditModal() {
      this.editing = false
      this.clearMessages()
    },
    
    closeDeleteConfirm() {
      this.showDeleteConfirm = false
      this.courseToDelete = null
    },
    
    resetNewCourse() {
      this.newCourse = { 
        code: '', 
        name: '', 
        semester: '', 
        year: ''
      }
    },
    
    showSuccess(message) {
      this.success = message
      this.error = ''
      setTimeout(() => {
        this.success = ''
      }, 5000)
    },
    
    showError(message) {
      this.error = message
      this.success = ''
      setTimeout(() => {
        this.error = ''
      }, 5000)
    },
    
    clearMessages() {
      this.error = ''
      this.success = ''
    }
  },
  
  mounted() {
    this.fetchCourses()
  }
}
</script>
<style scoped>
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-section h3 {
  margin: 0;
  color: #1D3557;
  font-size: 28px;
}

.add-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
}

.add-btn:hover {
  background: linear-gradient(135deg, #229954, #27ae60);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(46, 204, 113, 0.4);
}

.btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #f1f3f4;
}

.add-form {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.course-form {
  margin-top: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 4px;
  font-weight: 600;
  color: #1D3557;
  font-size: 14px;
}

.form-group input,
.form-group select {
  padding: 12px;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #457B9D;
  box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.submit-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #457B9D, #1D3557);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.submit-btn:hover {
  background: linear-gradient(135deg, #3a6b8a, #1a2e4a);
  transform: translateY(-1px);
}

.cancel-btn {
  background: #6c757d;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: #5a6268;
}

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
}

.course-count {
  background: #F1FAEE;
  color: #1D3557;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.table-container {
  overflow-x: auto;
}

.course-table {
  width: 100%;
  border-collapse: collapse;
}

.course-table th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  font-size: 14px;
  padding: 16px 12px;
  text-align: left;
  border-bottom: 2px solid #e9ecef;
}

.course-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f1f3f4;
  vertical-align: middle;
}

.course-row:hover {
  background: #f8f9fa;
}

.course-code {
  font-weight: 700;
  color: #1D3557;
  background: #F1FAEE;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.course-name {
  font-weight: 500;
  color: #1D3557;
  line-height: 1.4;
}

.semester-cell,
.year-cell {
  color: #6c757d;
  font-size: 14px;
}

.student-count {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.actions-cell {
  white-space: nowrap;
}

.edit-btn,
.delete-btn {
  padding: 8px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-right: 8px;
  transition: all 0.2s ease;
}

.edit-btn {
  background: #fff3cd;
  color: #856404;
}

.edit-btn:hover {
  background: #ffeaa7;
  transform: scale(1.1);
}

.delete-btn {
  background: #f8d7da;
  color: #721c24;
}

.delete-btn:hover {
  background: #f5c6cb;
  transform: scale(1.1);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #495057;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.error-message,
.success-message {
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 16px;
  font-weight: 500;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success-message {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.modal {
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
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  min-width: 500px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 18px;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
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

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.save-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.save-btn:hover {
  background: linear-gradient(135deg, #229954, #27ae60);
}

.delete-modal {
  text-align: center;
  max-width: 400px;
}

.delete-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #e74c3c;
}

.delete-modal h4 {
  margin: 0 0 12px 0;
  color: #1D3557;
}

.delete-modal p {
  margin: 8px 0;
  color: #495057;
  line-height: 1.5;
}

.warning-text {
  color: #e74c3c;
  font-size: 14px;
  font-weight: 500;
}

.delete-confirm-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.delete-confirm-btn:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    min-width: 0;
    margin: 16px;
  }
  
  .form-actions,
  .modal-actions {
    flex-direction: column;
  }
  
  .table-container {
    overflow-x: auto;
  }
}
</style>
