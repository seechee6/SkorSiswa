<template>
  <div class="manage-courses">
    <div class="header">
      <h2>Manage Courses</h2>
      <button @click="showAddCourseModal = true" class="btn-primary">Add Course</button>
    </div>

    <div class="filters">
      <input v-model="search" placeholder="Search courses..." class="search-input">
      <select v-model="semesterFilter" class="semester-filter">
        <option value="">All Semesters</option>
        <option v-for="semester in semesters" :key="semester" :value="semester">
          {{ semester }}
        </option>
      </select>
    </div>

    <div class="courses-table">
      <table>
        <thead>
          <tr>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Semester</th>
            <th>Credit Hours</th>
            <th>Lecturer</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in filteredCourses" :key="course.id">
            <td>{{ course.code }}</td>
            <td>{{ course.name }}</td>
            <td>{{ course.semester }}</td>
            <td>{{ course.creditHours }}</td>
            <td>{{ course.lecturer ? course.lecturer.name : 'Not Assigned' }}</td>
            <td>
              <span :class="['status', course.status]">{{ course.status }}</span>
            </td>
            <td class="actions">
              <button @click="editCourse(course)" class="btn-edit">Edit</button>
              <button @click="assignLecturer(course)" class="btn-assign">Assign Lecturer</button>
              <button @click="toggleCourseStatus(course)" class="btn-toggle">
                {{ course.status === 'active' ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Course Modal -->
    <div v-if="showAddCourseModal" class="modal">
      <div class="modal-content">
        <h3>{{ editingCourse ? 'Edit Course' : 'Add New Course' }}</h3>
        <form @submit.prevent="saveCourse">
          <div class="form-group">
            <label>Course Code</label>
            <input v-model="courseForm.code" required pattern="[A-Z]{3}[0-9]{4}" 
                   title="Course code must be 3 letters followed by 4 numbers">
          </div>
          <div class="form-group">
            <label>Course Name</label>
            <input v-model="courseForm.name" required>
          </div>
          <div class="form-group">
            <label>Semester</label>
            <select v-model="courseForm.semester" required>
              <option v-for="semester in semesters" :key="semester" :value="semester">
                {{ semester }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Credit Hours</label>
            <input type="number" v-model.number="courseForm.creditHours" min="1" max="6" required>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="courseForm.description" rows="3"></textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save</button>
            <button type="button" @click="showAddCourseModal = false" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Assign Lecturer Modal -->
    <div v-if="showAssignLecturerModal" class="modal">
      <div class="modal-content">
        <h3>Assign Lecturer to {{ selectedCourse?.name }}</h3>
        <form @submit.prevent="saveLecturerAssignment">
          <div class="form-group">
            <label>Select Lecturer</label>
            <select v-model="selectedLecturerId" required>
              <option value="">Choose a lecturer</option>
              <option v-for="lecturer in availableLecturers" 
                      :key="lecturer.id" 
                      :value="lecturer.id">
                {{ lecturer.name }}
              </option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Assign</button>
            <button type="button" @click="showAssignLecturerModal = false" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ManageCourses',
  data() {
    return {
      courses: [],
      search: '',
      semesterFilter: '',
      showAddCourseModal: false,
      showAssignLecturerModal: false,
      editingCourse: null,
      selectedCourse: null,
      selectedLecturerId: '',
      availableLecturers: [],
      courseForm: {
        code: '',
        name: '',
        semester: '',
        creditHours: 3,
        description: ''
      },
      semesters: [
        '2025/2026-1',
        '2025/2026-2',
        '2026/2027-1',
        '2026/2027-2'
      ]
    }
  },
  computed: {
    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = 
          course.code.toLowerCase().includes(this.search.toLowerCase()) ||
          course.name.toLowerCase().includes(this.search.toLowerCase());
        const matchesSemester = !this.semesterFilter || course.semester === this.semesterFilter;
        return matchesSearch && matchesSemester;
      });
    }
  },
  created() {
    this.fetchCourses();
    this.fetchLecturers();
  },
  methods: {
    async fetchCourses() {
      try {
        const response = await axios.get('/api/admin/courses');
        this.courses = response.data;
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async fetchLecturers() {
      try {
        const response = await axios.get('/api/admin/lecturers');
        this.availableLecturers = response.data;
      } catch (error) {
        console.error('Error fetching lecturers:', error);
      }
    },
    editCourse(course) {
      this.editingCourse = course;
      this.courseForm = { ...course };
      this.showAddCourseModal = true;
    },
    assignLecturer(course) {
      this.selectedCourse = course;
      this.selectedLecturerId = course.lecturer?.id || '';
      this.showAssignLecturerModal = true;
    },
    async saveCourse() {
      try {
        if (this.editingCourse) {
          await axios.put(`/api/admin/courses/${this.editingCourse.id}`, this.courseForm);
        } else {
          await axios.post('/api/admin/courses', this.courseForm);
        }
        this.showAddCourseModal = false;
        this.fetchCourses();
      } catch (error) {
        console.error('Error saving course:', error);
      }
    },
    async saveLecturerAssignment() {
      try {
        await axios.post(`/api/admin/courses/${this.selectedCourse.id}/assign-lecturer`, {
          lecturerId: this.selectedLecturerId
        });
        this.showAssignLecturerModal = false;
        this.fetchCourses();
      } catch (error) {
        console.error('Error assigning lecturer:', error);
      }
    },
    async toggleCourseStatus(course) {
      try {
        const newStatus = course.status === 'active' ? 'inactive' : 'active';
        await axios.patch(`/api/admin/courses/${course.id}/status`, { status: newStatus });
        this.fetchCourses();
      } catch (error) {
        console.error('Error toggling course status:', error);
      }
    }
  }
}
</script>

<style scoped>
.manage-courses {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.search-input, .semester-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.courses-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-edit {
  background-color: #2196F3;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-assign {
  background-color: #9C27B0;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-toggle {
  background-color: #9E9E9E;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.9em;
}

.status.active {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.status.inactive {
  background-color: #FFEBEE;
  color: #C62828;
}

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
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  min-width: 400px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .actions {
    flex-direction: column;
  }
  
  .btn-edit,
  .btn-assign,
  .btn-toggle {
    width: 100%;
    margin-bottom: 4px;
  }
}
</style>
